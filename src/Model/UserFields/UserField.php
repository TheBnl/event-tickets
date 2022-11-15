<?php

namespace Broarm\EventTickets;

namespace Broarm\EventTickets\Model\UserFields;

use Broarm\EventTickets\Model\Attendee;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

/**
 * Class AttendeeExtraField
 *
 * @property string  Name
 * @property string  Title
 * @property string  Default
 * @property string  ExtraClass
 * @property boolean Required
 * @property boolean Editable
 */
class UserField extends DataObject
{
    private static $table_name = 'EventTickets_UserField';

    /**
     * @var FormField
     */
    protected $fieldType = FormField::class;

    /**
     * Field name to be used in the AttendeeField (Composite field)
     *
     * @see AttendeeField::__construct()
     *
     * @var string
     */
    protected $fieldName;

    public function getType()
    {
        return _t(__CLASS__ . '.Type', 'Field');
    }

    private static $db = array(
        'Name' => 'Varchar', // mostly here for default fields lookup
        'Title' => 'Varchar',
        'Default' => 'Varchar',
        'ExtraClass' => 'Varchar',
        'Required' => 'Boolean',
        'Editable' => 'Boolean',
        //'Sort' => 'Int'
    );

    private static $defaults = array(
        'Editable' => 1
    );

    //private static $default_sort = 'Sort ASC';

    private static $belongs_many_many = array(
        'Attendees' => Attendee::class,
    );

    private static $summary_fields = array(
        'ClassName' => 'Type of field',
        'Title' => 'Title',
        'RequiredNice' => 'Required field'
    );

    private static $translate = array(
        'Title',
        'Default'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Editable']);
        $fields->addFieldsToTab('Root.Main', array(
            DropdownField::create('ClassName', 'Field type', self::availableFields()),
            ReadonlyField::create('Name', _t(__CLASS__ . '.Name', 'Name for this field')),
            TextField::create('Title', _t(__CLASS__ . '.Title', 'Field label or question')),
            TextField::create('ExtraClass', _t(__CLASS__ . '.ExtraClass', 'Add an extra class to the field')),
            TextField::create('Default', _t(__CLASS__ . '.Default', 'Set a default value'))
        ));

        $fields->addFieldsToTab('Root.Validation', array(
            CheckboxField::create('Required', _t(
                __CLASS__ . '.Required',
                'This field is required'
            ))->setDisabled(!(bool)$this->Editable)
        ));

        if (!$this->Editable) {
            $fields->fieldByName('Root.Main.ClassName')->setDisabled(1);
            $fields->fieldByName('Root.Main.Title')->setDisabled(1);
            $fields->fieldByName('Root.Main.Default')->setDisabled(1);
        }

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
     * Get the filed type
     *
     * @return FormField
     */
    public function getFieldType()
    {
        return $this->fieldType;
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

        if (!$field = self::get()->find('Name', $fieldName)) {
            $field = $fieldType::create();
            $field->Name = $fieldName;
        }

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
     * @param $main boolean check if the field is for the main attendee
     *
     * @return FormField
     */
    public function createField($fieldName, $defaultValue = null, $main = false)
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

    public function canDelete($member = null)
    {
        return $this->Editable;
    }

    public static function availableFields()
    {
        $availableClasses = ClassInfo::subclassesFor(UserField::class);
        array_shift($availableClasses);
        return array_map(function ($class) {
            /** @var UserField $class */
            return $class::singleton()->getType();
        }, array_combine($availableClasses, $availableClasses));
    }
}
