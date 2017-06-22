<?php

/**
 * Class UserCheckboxField
 *
 * @author Bram de Leeuw
 * @package UserTextField
 */
class UserCheckboxField extends Broarm\EventTickets\UserField
{
    /**
     * Create a default text field
     *
     * @param string $fieldName
     * @param null   $defaultValue
     *
     * @return CheckboxField
     */
    public function createField($fieldName, $defaultValue = null)
    {
        return CheckboxField::create($fieldName, $this->Title, $defaultValue);
    }

    /**
     * Get the value in a readable manner
     *
     * @return string
     */
    public function getValue()
    {
        return (bool)$this->getField('Value')
            ? _t('Boolean.YESANSWER', 'Yes')
            : _t('Boolean.NOANSWER', 'No');
    }
}
