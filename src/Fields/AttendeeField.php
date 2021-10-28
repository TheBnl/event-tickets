<?php

namespace Broarm\EventTickets\Fields;


use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Model\UserFields\UserField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Security\Security;

/**
 * Class AttendeeField
 *
 * @package Broarm\EventTickets
 */
class AttendeeField extends CompositeField
{
    protected $requiredFields = array();

    public function __construct(Attendee $attendee, $main = false, $required = true)
    {
        parent::__construct();
        $this->setTag('fieldset');
        $this->setLegend(_t(__CLASS__ . '.VALUED', '{title} valued {price}', null, array(
            'title' => $attendee->Ticket()->Title,
            'price' => $attendee->Ticket()->dbObject('Price')->NiceDecimalPoint())
        ));

        $children = FieldList::create();
        $savableFields = $attendee->TicketPage()->Fields();

        /** @var UserField $field */
        foreach ($savableFields as $field) {
            // Generate a unique field name
            $fieldName = "Attendee[{$attendee->ID}][$field->ID]";

            // Create the field an fill with attendee data
            $hasField = $attendee->Fields()->find('ID', $field->ID);
            $value = $hasField ? $hasField->getField('Value') : null;
            $formField = $field->createField($fieldName, $value, $main);

            // Check if the field is required
            if ($field->Required && $required) {
                if ($formField instanceof CompositeField) {
                    foreach ($formField->getChildren()->column('Name') as $requiredField) {
                        $this->addRequiredField($requiredField);
                    }
                } else {
                    $this->addRequiredField($fieldName);
                }
            }

            // Pre fill the field if a member is logged in
            if ($main && empty($formField->value) && $member = Security::getCurrentUser()) {
                $formField->setValue($member->getField($field->Name));
            }

            // Add the form
            $children->add($formField);
        }

        // Set the main field
        $children->add(HiddenField::create("Attendee[{$attendee->ID}][Main]", 'Main', (int)$main));

        // set the children
        $this->setChildren($children);

        // Add a hook to modify the added fields if needed
        $this->extend('updateAttendeeField', $attendee, $main, $required);
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
