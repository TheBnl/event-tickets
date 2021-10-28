<?php

namespace Broarm\EventTickets\Model\UserFields;

use Broarm\EventTickets\Forms\GridField\UserFieldsGridFieldConfig;
use Broarm\EventTickets\Forms\GridField\UserOptionSetFieldGridFieldConfig;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\HasManyList;

/**
 * Class UserOptionSetField
 *
 * @author Bram de Leeuw
 * @package UserTextField
 *
 * @method HasManyList Options()
 */
class UserOptionSetField extends UserField
{
    private static $table_name = 'EventTickets_UserOptionSetField';

    /**
     * @var OptionsetField
     */
    protected $fieldType = OptionsetField::class;

    private static $has_many = array(
        'Options' => UserFieldOption::class
    );

    public function getType()
    {
        return _t(__CLASS__ . '.Type', 'Option Set Field');
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        if ($this->exists()) {
            $fields->addFieldToTab('Root.Main', GridField::create(
                'Options',
                _t(__CLASS__ . '.Options', 'Add field options'),
                $this->Options(),
                UserFieldsGridFieldConfig::create()
            ));
        }

        return $fields;
    }

    /**
     * @param string  $fieldName
     * @param null    $defaultValue
     * @param boolean $main check if the field is for the main attendee
     *
     * @return OptionsetField
     */
    public function createField($fieldName, $defaultValue = null, $main = false)
    {
        /** @var OptionsetField $field */
        $field = parent::createField($fieldName, $defaultValue ?: [], $main);
        $field->setSource($this->Options()->map()->toArray());
        $field->setValue($defaultValue);
        return $field;
    }

    /**
     * Get the value by set option
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($option = $this->Options()->byID($this->getField('Value'))) {
            return $option->Title;
        }

        return null;
    }
}
