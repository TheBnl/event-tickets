<?php

namespace Broarm\EventTickets\Model\UserFields;

use SilverStripe\Forms\FormField;
use SilverStripe\Forms\NumericField;

/**
 * Class UserNumericField
 *
 * @property int Min
 * @property int Max
 *
 * @author Bram de Leeuw
 * @package UserTextField
 */
class UserNumericField extends UserField
{
    private static $table_name = 'EventTickets_UserNumericField';

    /**
     * @var NumericField
     */
    protected $fieldType = NumericField::class;

    private static $db = array(
        'Min' => 'Int',
        'Max' => 'Int'
    );

    public function getType()
    {
        return _t(__CLASS__ . '.Type', 'Numeric Field');
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Validation', array(
            NumericField::create('Min', _t(__CLASS__ . '.Min', 'Minimum required number')),
            NumericField::create('Max', _t(__CLASS__ . '.Max', 'Maximum required number'))
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
     * @return NumericField|FormField
     */
    public function createField($fieldName, $defaultValue = null, $main = false)
    {
        $numericField = parent::createField($fieldName, $defaultValue, $main);

        if (!empty($this->Min)) {
            $numericField->setAttribute('min', $this->Min);
        }

        if (!empty($this->Max)) {
            $numericField->setAttribute('max', $this->Max);
        }

        return $numericField;
    }
}
