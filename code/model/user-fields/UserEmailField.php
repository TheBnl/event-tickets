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
     * @var FormField
     */
    protected $fieldType = 'EmailField';

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
        $field = parent::createField($fieldName, $defaultValue, $main);

        // If we have the main booker account, we create a
        if ($main) {
            $field = CompositeField::create([
                EmailField::create("{$fieldName}[_Email]", $this->Title, $defaultValue),
                EmailField::create(
                    "{$fieldName}[_ConfirmedEmail]",
                    _t(__CLASS__ . '.ConfirmedEmail', 'Confirm {title}', null, ['title' => $this->Title]),
                    $defaultValue
                ),
            ]);
            $field->addExtraClass($this->ExtraClass);
        }

        return $field;
    }
}
