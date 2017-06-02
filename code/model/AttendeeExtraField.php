<?php
/**
 * AttendeeExtraField.php
 *
 * @author Bram de Leeuw
 * Date: 24/05/17
 */

namespace Broarm\EventTickets;

use CheckboxField;
use Convert;
use DataObject;
use DropdownField;
use FieldList;
use FormField;
use GridField;
use LiteralField;
use OptionsetField;
use ReadonlyField;
use Tab;
use TabSet;
use TextField;

/**
 * Class AttendeeExtraField
 *
 * @property string    Title
 * @property string    FieldName
 * @property FormField FieldType
 * @property string    DefaultValue
 * @property string    ExtraClass
 * @property boolean   Required
 * @property boolean   Editable
 *
 * @method \CalendarEvent Event()
 * @method \HasManyList Options()
 */
class AttendeeExtraField extends DataObject
{
    /**
     * Field name to be used in the AttendeeField (Composite field)
     * @var string
     */
    protected $fieldName;

    private static $db = array(
        'Title' => 'Varchar(255)',
        'DefaultValue' => 'Varchar(255)',
        'ExtraClass' => 'Varchar(255)',
        'FieldName' => 'Varchar(255)',
        'Required' => 'Boolean',
        'Editable' => 'Boolean',
        'FieldType' => 'Enum("TextField,EmailField,CheckboxField,OptionsetField","TextField")',
        'Sort' => 'Int'
    );

    private static $defaults = array(
        'Editable' => 1
    );

    private static $default_sort = 'Sort ASC';

    private static $has_one = array(
        'Event' => 'CalendarEvent'
    );

    private static $has_many = array(
        'Options' => 'Broarm\EventTickets\AttendeeExtraFieldOption'
    );

    private static $belongs_many_many = array(
        'Attendees' => 'Broarm\EventTickets\Attendee'
    );

    private static $summary_fields = array(
        'Title' => 'Title',
        'FieldType' => 'FieldType',
        'Required.Nice' => 'Required'
    );

    private static $translate = array(
        'Title'
    );

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root', $mainTab = new Tab('Main')));

        $fields->addFieldsToTab('Root.Main', array(
            $fieldType = DropdownField::create(
                'FieldType',
                _t('AttendeeExtraField.FieldType', 'Type of field'),
                $this->dbObject('FieldType')->enumValues()
            ),
            TextField::create('Title', _t('AttendeeExtraField.Title', 'Field label or question')),
            TextField::create('ExtraClass', _t('AttendeeExtraField.ExtraClass', 'Add an extra class to the field')),
            $required = CheckboxField::create('Required', _t('AttendeeExtraField.Required', 'This field is required'))
        ));

        if ($this->exists()) {
            if ($this->FieldType !== 'OptionsetField') {
                $fields->addFieldToTab('Root.Main', TextField::create(
                    'DefaultValue',
                    _t('AttendeeExtraField.DefaultValue', 'Set a default value'))
                );
            } else {
                $fields->addFieldToTab('Root.Main', GridField::create(
                    'Options',
                    _t('AttendeeExtraField.Options', 'Add field options'),
                    $this->Options(),
                    GridFieldConfig_Fields::create()
                ));
            }
        }

        if (!$this->Editable) {
            $fieldType->setDisabled(true);
            $required->setDisabled(true);
        }

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    /**
     * Returns the singular name without the namespaces
     *
     * @return string
     */
    public function singular_name()
    {
        $name = explode('\\', parent::singular_name());
        return trim(end($name));
    }

    /**
     * Set the Field name based on the title and ID
     */
    public function onBeforeWrite()
    {
        if (empty($this->FieldName)) {
            $this->FieldName = Convert::raw2url($this->Title);
        }

        parent::onBeforeWrite();
    }

    /**
     * Get the value casted based on chosen field type
     *
     * @return mixed|string
     */
    public function getValue()
    {
        switch ($this->FieldType) {
            case 'OptionsetField':
                return $this->Options()->byID($this->getField('Value'))->Title;
            case 'CheckboxField':
                return (bool)$this->getField('Value') ? _t('Boolean.YESANSWER', 'Yes') : _t('Boolean.NOANSWER', 'No');
            default:
                return $this->getField('Value');
        }
    }

    /**
     * Create a field from given configuration
     *
     * @param $fieldName
     * @param $fieldConfig
     *
     * @return AttendeeExtraField|DataObject
     */
    public static function createFromConfig($fieldName, $fieldConfig) {
        $field = AttendeeExtraField::create();
        $field->Title = _t("AttendeeField.$fieldName", $fieldName);
        $field->FieldName = $fieldName;
        $field->Required = true;
        $field->Editable = false;

        if (is_array($fieldConfig)) {
            foreach ($fieldConfig as $property => $value) {
                $field->setField($property, $value);
            }
        } else {
            $field->FieldType = $fieldConfig;
        }

        return $field;
    }

    /**
     * Create the configured field
     *
     * @param $fieldName
     * @param $defaultValue
     *
     * @return \FormField
     */
    public function createField($fieldName, $defaultValue = null)
    {
        $fieldType = $this->FieldType;
        $field = $fieldType::create($this->fieldName = $fieldName, $this->Title);

        // Set a default value if set
        $field->setValue($defaultValue ? $defaultValue : $this->DefaultValue);

        // Add any extra classes
        $field->addExtraClass($this->ExtraClass);

        // Check if the field is an option set
        if ($field instanceof OptionsetField) {
            /** @var OptionsetField $field */
            $options = $this->Options()->map('ID', 'Title')->toArray();
            $field->setSource($options);

            // If a field is selected as default set that value
            if ($defaultValue = $this->Options()->find('Default', 1)) {
                $field->setValue($defaultValue->ID);
            }
        }

        $this->extend('updateField', $field);
        return $field;
    }

    public function canView($member = null)
    {
        return $this->Event()->canView($member);
    }

    public function canEdit($member = null)
    {
        return $this->Event()->canEdit($member);
    }

    public function canDelete($member = null)
    {
        return $this->Editable;
    }

    public function canCreate($member = null)
    {
        return $this->Event()->canCreate($member);
    }
}
