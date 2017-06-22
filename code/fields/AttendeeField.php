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

        $children = FieldList::create();
        $savableFields = $attendee->Event()->Fields();

        /** @var UserField $field */
        foreach ($savableFields as $field) {
            // Generate a unique field name
            $fieldName = "{$this->name}[{$attendee->ID}][$field->ID]";
            
            // Check if the field is required
            if ($field->Required && $required) {
                $this->addRequiredField($fieldName);
            }

            // Create the field an fill with attendee data
            $hasField = $attendee->Fields()->find('ID', $field->ID);
            $value = $hasField ? $hasField->getField('Value') : null;
            $formField = $field->createField($fieldName, $value);

            // Pre fill the field if a member is logged in
            if ($main && empty($formField->value) && $member = Member::currentUser()) {
                $formField->setValue($member->getField($field->Name));
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
