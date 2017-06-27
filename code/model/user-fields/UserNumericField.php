<?php

/**
 * Class UserNumericField
 *
 * @property int Min
 * @property int Max
 *
 * @author Bram de Leeuw
 * @package UserTextField
 */
class UserNumericField extends Broarm\EventTickets\UserField
{
    /**
     * @var NumericField
     */
    protected $fieldType = 'NumericField';

    private static $db = array(
        'Min' => 'Int',
        'Max' => 'Int'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Validation', array(
            NumericField::create('Min', _t('UserNumericField.Min', 'Minimum required number')),
            NumericField::create('Max', _t('UserNumericField.Max', 'Maximum required number'))
        ));

        return $fields;
    }

    /**
     * Create a default text field
     *
     * @param string $fieldName
     * @param null   $defaultValue
     *
     * @return NumericField|FormField
     */
    public function createField($fieldName, $defaultValue = null)
    {
        $numericField = parent::createField($fieldName, $defaultValue);

        if (!empty($this->Min)) {
            $numericField->setAttribute('min', $this->Min);
        }

        if (!empty($this->Max)) {
            $numericField->setAttribute('max', $this->Max);
        }

        return $numericField;
    }
}
