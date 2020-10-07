<?php

namespace Broarm\EventTickets\Model\UserFields;

use SilverStripe\Forms\DateField;

/**
 * Class UserTextField
 *
 * @author Bram de Leeuw
 * @package UserTextField
 *
 * @property string MinDate
 * @property string MaxDate
 */
class UserDateField extends UserField
{
    private static $table_name = 'EventTickets_UserDateField';

    /**
     * @var DateField
     */
    protected $fieldType = DateField::class;

    public function getType()
    {
        return _t(__CLASS__ . '.Type', 'Date Field');
    }

    private static $db = array(
        'MinDate' => 'Date',
        'MaxDate' => 'Date'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Validation', array(
            DateField::create('MinDate', _t(__CLASS__ . '.MinDate', 'Minimum required date')),
            DateField::create('MaxDate', _t(__CLASS__ . '.MaxDate', 'Maximum required date'))
        ));

        return $fields;
    }

    /**
     * Create a default text field
     *
     * @param string  $fieldName
     * @param null    $defaultValue
     * @param boolean $main check if the field is for the main attendee
     *
     * @return DateField
     */
    public function createField($fieldName, $defaultValue = null, $main = false)
    {
        /** @var DateField $dateField */
        $dateField = parent::createField($fieldName, $defaultValue, $main);

        // Todo set min max
//        if ($this->MinDate) {
//            $dateField->setConfig('min', $this->dbObject('MinDate')->Format('Y-m-d'));
//        }
//
//        if ($this->MaxDate) {
//            $dateField->setConfig('max', $this->dbObject('MaxDate')->Format('Y-m-d'));
//        }

        return $dateField;
    }
}
