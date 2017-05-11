<?php
/**
 * AttendeesField.php
 *
 * @author Bram de Leeuw
 * Date: 10/03/17
 */

namespace Broarm\EventTickets;

use ArrayList;
use CompositeField;
use DBField;
use FieldGroup;
use FieldList;
use FormField;
use HiddenField;
use LiteralField;
use Member;

/**
 * Class AttendeeField
 *
 * @package Broarm\EventTickets
 */
class AttendeeField extends CompositeField
{
    private static $required_fields = array(
        'FirstName',
        'Surname',
        'Email'
    );

    protected $name = 'Attendee';

    protected $requiredFields = array();

    public function __construct(Attendee $attendee, $main = false, $required = true)
    {
        parent::__construct();
        $this->setTag('fieldset');
        $this->setLegend(_t('AttendeeField.VALUED', '{title} valued {price}', null, array(
            'title' => $attendee->Ticket()->Title,
            'price' => $attendee->Ticket()->dbObject('Price')->NiceDecimalPoint())
        ));
        if ($required) $this->addExtraClass('required');

        $children = FieldList::create();
        $savableFields = Attendee::config()->get('savable_fields');
        foreach ($savableFields as $field => $fieldClass) {
            // Generate a unique field name
            $fieldName = "{$this->name}[{$attendee->ID}][$field]";

            // Check if the field is required
            if (in_array($field, self::config()->get('required_fields')) && $required) {
                $this->addRequiredField($fieldName);
            }

            // Create the field
            /** @var FormField $formField */
            $formField = $fieldClass::create(
                $fieldName,
                _t("AttendeeField.$field", $field)
            )->setValue($attendee->getField($field));

            // Pre fill the form if a member is logged in
            if ($main && empty($formField->value) && $member = Member::currentUser()) {
                $formField->setValue($member->getField($field));
            }

            // Add the form
            $children->add($formField);
        }

        // Set the main field
        $children->add(HiddenField::create("{$this->name}[{$attendee->ID}][Main]", 'Main', (int)$main));

        // set the children
        $this->setChildren($children);

        // Add a hook to modify the added fields if needed
        $this->extend('updateAttendeeField');
    }

    /**
     * Update the required fields array
     *
     * @param $fieldName
     */
    private function addRequiredField($fieldName) {
        array_push($this->requiredFields, $fieldName);
    }

    /**
     * Get the required fields
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return $this->requiredFields;
    }
}
