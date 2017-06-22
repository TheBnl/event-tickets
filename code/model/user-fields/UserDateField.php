<?php

/**
 * Class UserTextField
 *
 * @author Bram de Leeuw
 * @package UserTextField
 *
 * @property string MinDate
 * @property string MaxDate
 */
class UserDateField extends Broarm\EventTickets\UserField
{
    private static $db = array(
        'MinDate' => 'Date',
        'MaxDate' => 'Date'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Validation', array(
            DateField::create('MinDate', _t('UserDateField.MinDate', 'Minimum required date'))->setConfig('showcalendar', true),
            DateField::create('MaxDate', _t('UserDateField.MaxDate', 'Maximum required date'))->setConfig('showcalendar', true)
        ));

        return $fields;
    }

    /**
     * Create a default text field
     *
     * @param string $fieldName
     * @param null   $defaultValue
     *
     * @return TextField
     */
    public function createField($fieldName, $defaultValue = null)
    {
        $dateField = DateField::create($fieldName, $this->Title, $defaultValue);

        if ($this->MinDate) {
            $dateField->setConfig('min', $this->dbObject('MinDate')->Format('Y-m-d'));
        }

        if ($this->MaxDate) {
            $dateField->setConfig('max', $this->dbObject('MaxDate')->Format('Y-m-d'));
        }

        return $dateField;
    }
}
