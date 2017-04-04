<?php
/**
 * ReservationForm.php
 *
 * @author Bram de Leeuw
 * Date: 13/03/17
 */

namespace Broarm\EventTickets;

use FieldGroup;
use FieldList;
use FormAction;
use RequiredFields;
use TextField;

/**
 * Class ReservationForm
 *
 * @package Broarm\EventTickets
 */
class ReservationForm extends FormStep
{
    /**
     * @var Reservation
     */
    protected $reservation;

    public function __construct($controller, $name, Reservation $reservation = null)
    {
        $requiredFields = array();
        $fields = FieldList::create();
        $this->reservation = $reservation;

        // Ask details about created attendees
        foreach ($reservation->Attendees() as $attendee) {
            $fields->add($field = AttendeeField::create($attendee));
            $requiredFields = array_merge($requiredFields, $field->getRequiredFields());
        }

        $actions = FieldList::create(
            FormAction::create('goToNextStep', _t('ReservationForm.PAYMENT', 'Continue to payment'))
        );

        $required = new RequiredFields($requiredFields);

        parent::__construct($controller, $name, $fields, $actions, $required);
    }



    /**
     * Get the attached reservation
     *
     * @return Reservation
     */
    public function getReservation() {
        return $this->reservation;
    }

    /**
     * Finish the registration and continue to checkout
     *
     * @param array           $data
     * @param ReservationForm $form
     *
     * @return \SS_HTTPResponse
     */
    public function goToNextStep(array $data, ReservationForm $form)
    {
        foreach ($data['Attendee'] as $attendeeID => $attendeeData) {
            $attendee = Attendee::get()->byID($attendeeID);
            foreach ($attendeeData as $field => $value) {
                $attendee->setField($field, $value);
            }
            $attendee->write();
        }

        $this->extend('beforeNextStep', $data, $form);
        return $this->nextStep();
    }
}