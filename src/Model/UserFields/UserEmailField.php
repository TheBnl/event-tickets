<?php

namespace Broarm\EventTickets\Model\UserFields;

use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\NumericField;

/**
 * Class UserEmailField
 *
 * @author Bram de Leeuw
 * @package UserTextField
 */
class UserEmailField extends UserField
{
    private static $table_name = 'EventTickets_UserEmailField';

    /**
     * @var FormField
     */
    protected $fieldType = EmailField::class;

    public function getType()
    {
        return _t(__CLASS__ . '.Type', 'Email Field');
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
