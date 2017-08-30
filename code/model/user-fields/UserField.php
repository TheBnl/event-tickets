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
use FieldList;
use FormField;
use ReadonlyField;
use Tab;
use TabSet;
use TextField;

/**
 * Class AttendeeExtraField
 * todo write migration from AttendeeExtraField -> UserField
 *
 * @property string  Name
 * @property string  Title
 * @property string  Default
 * @property string  ExtraClass
 * @property boolean Required
 * @property boolean Editable
 *
 * @method \CalendarEvent Event()
 */
class UserField extends DataObject
{
    /**
     * @var FormField
     */
    protected $fieldType = 'FormField';

    /**
     * Field name to be used in the AttendeeField (Composite field)
     *
     * @see AttendeeField::__construct()
     *
     * @var string
     */
    protected $fieldName;

    private static $db = array(
        'Name' => 'Varchar(255)', // mostly here for default fields lookup
        'Title' => 'Varchar(255)',
        'Default' => 'Varchar(255)',
        'ExtraClass' => 'Varchar(255)',
        'Required' => 'Boolean',
        'Editable' => 'Boolean',
        'Sort' => 'Int'
    );

    private static $defaults = array(
        'Editable' => 1
    );

    private static $default_sort = 'Sort ASC';

    private static $has_one = array(
        'Event' => 'CalendarEvent'
    );

    private static $belongs_many_many = array(
        'Attendees' => 'Broarm\EventTickets\Attendee'
    );

    private static $summary_fields = array(
        'singular_name' => 'Type of field',
        'Title' => 'Title',
        'RequiredNice' => 'Required field'
    );

    private static $translate = array(
        'Title',
        'Default'
    );

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root', $mainTab = new Tab('Main')));
        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('PreviewFieldType', 'Field type', $this->ClassName),
            ReadonlyField::create('Name', _t('AttendeeExtraField.Name', 'Name for this field')),
            TextField::create('Title', _t('AttendeeExtraField.Title', 'Field label or question')),
            TextField::create('ExtraClass', _t('AttendeeExtraField.ExtraClass', 'Add an extra class to the field')),
            TextField::create('Default', _t('AttendeeExtraField.Default', 'Set a default value'))
        ));

        $fields->addFieldsToTab('Root.Validation', array(
            CheckboxField::create('Required', _t(
                'AttendeeExtraField.Required',
                'This field is required'
            ))->setDisabled(!(bool)$this->Editable)
        ));

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    public function onBeforeWrite()
    {
        // Set the title to be the name when empty
        if (empty($this->Name)) {
            $str = preg_replace('/[^a-z0-9]+/i', ' ', $this->Title);
            $str = trim($str);
            $str = ucwords($str);
            $this->Name = str_replace(" ", "", $str);
        }

        parent::onBeforeWrite();
    }

    /**
     * Show if the field is required in a nice format
     *
     * BUGFIX Using the shorthand Required.Nice in the summary_fields
     * made the fields, that were required (true), be set to (false)
     *
     * @return mixed
     */
    public function getRequiredNice()
    {
        return $this->dbObject('Required')->Nice();
    }

    /**
     * Get the value
     *
     * @return mixed|string
     */
    public function getValue()
    {
        return $this->getField('Value');
    }

    /**
     * Create a field from given configuration
     * These fields are created based on the set default fields @see Attendee::$default_fields
     *
     * @param $fieldName
     * @param $fieldConfig
     *
     * @return UserField|DataObject
     */
    public static function createDefaultField($fieldName, $fieldConfig)
    {
        /** @var UserField $fieldType */
        $fieldType = $fieldConfig['FieldType'];

        $field = $fieldType::create();
        $field->Name = $fieldName;
        if (is_array($fieldConfig)) {
            foreach ($fieldConfig as $property => $value) {
                $field->setField($property, $value);
            }
        }

        return $field;
    }

    /**
     * Create the actual field
     * Overwrite this on the field subclass
     *
     * @param $fieldName string Created by the AttendeeField
     * @param $defaultValue string Set a default value
     *
     * @return FormField
     */
    public function createField($fieldName, $defaultValue = null)
    {
        $fieldType = $this->fieldType;
        $field = $fieldType::create($fieldName, $this->Title, $defaultValue);
        $field->addExtraClass($this->ExtraClass);
        $this->extend('updateCreateField', $field);
        return $field;
    }

    /**
     * Returns the singular name without the namespaces
     *
     * @return string
     */
    public function singular_name()
    {
        $name = explode('\\', parent::singular_name());
        return trim(str_replace('User', '', end($name)));
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
