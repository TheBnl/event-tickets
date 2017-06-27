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
     * @var CheckboxField
     */
    protected $fieldType = 'CheckboxField';

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
