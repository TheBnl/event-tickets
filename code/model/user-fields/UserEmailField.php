<?php

/**
 * Class UserEmailField
 *
 * @author Bram de Leeuw
 * @package UserTextField
 */
class UserEmailField extends Broarm\EventTickets\UserField
{
    /**
     * Create a default text field
     *
     * @param string $fieldName
     * @param null   $defaultValue
     *
     * @return EmailField
     */
    public function createField($fieldName, $defaultValue = null)
    {
        return EmailField::create($fieldName, $this->Title, $defaultValue);
    }
}
