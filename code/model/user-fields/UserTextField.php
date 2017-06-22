<?php

/**
 * Class UserTextField
 *
 * @author Bram de Leeuw
 * @package UserTextField
 */
class UserTextField extends Broarm\EventTickets\UserField
{
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
        return TextField::create($fieldName, $this->Title, $defaultValue);
    }
}
