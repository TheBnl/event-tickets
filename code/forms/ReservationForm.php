<?php
/**
 * ReservationForm.php
 *
 * @author Bram de Leeuw
 * Date: 13/03/17
 */

namespace Broarm\EventTickets;

use FieldList;
use FormAction;
use RequiredFields;

/**
 * Class ReservationForm
 *
 * @package Broarm\EventTickets
 */
class ReservationForm extends FormStep
{
    /**
     * Set this to true if you want to require credentials for all attendees
     *
     * @config
     * @var bool
     */
    private static $require_all_attendees = false;

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
        foreach ($reservation->Attendees() as $index => $attendee) {
            // The main reservation information is the first field
            $main = $index === 0;
            // Set required to true for all attendees or for the first only
            $required = self::config()->get('require_all_attendees')
                ? self::config()->get('require_all_attendees')
                : $main;

            $fields->add($field = AttendeeField::create($attendee, $main, $required));
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
    public function getReservation()
    {
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
        $reservation = $form->getReservation();
        foreach ($data['Attendee'] as $attendeeID => $attendeeData) {
            $attendee = Attendee::get()->byID($attendeeID);

            // populate the attendees
            foreach ($attendeeData as $field => $value) {
                $attendee->setField($field, $value);
            }
            $attendee->write();

            // Set the main contact
            if (isset($attendeeData['Main']) && (bool)$attendeeData['Main']) {
                $reservation->setMainContact($attendeeID);
            }
        }

        // add the tax modifier
        $reservation->PriceModifiers()->add(TaxModifier::findOrMake($reservation));
        $reservation->calculateTotal();
        $reservation->write();

        $this->extend('beforeNextStep', $data, $form);
        return $this->nextStep();
    }
}