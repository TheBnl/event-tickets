<?php

namespace Broarm\EventTickets\Forms;

use Broarm\EventTickets\Fields\AttendeeField;
use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Model\Reservation;
use Broarm\EventTickets\Model\TaxModifier;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\ValidationException;

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

    public function __construct($controller, $name, Reservation $reservation = null)
    {
        $requiredFields = array();
        $fields = FieldList::create();
        if ($this->reservation = $reservation) {
            // Ask details about created attendees
            foreach ($reservation->Attendees() as $index => $attendee) {
                // The main reservation information is the first field
                $main = $index === 0;
                // Set required to true for all attendees or for the first only
                $required = self::config()->get('require_all_attendees')
                    ? self::config()->get('require_all_attendees')
                    : $main;

                if ($required) {
                    $field = AttendeeField::create($attendee, $main, $required);
                    $fields->add($field);
                    $requiredFields = array_merge($requiredFields, $field->getRequiredFields());
                }
            }
        }

        $actions = FieldList::create(
            FormAction::create('goToNextStep', _t(__CLASS__ . '.ContinueToPayment', 'Continue to payment'))
        );
        
        $required = new RequiredFields($requiredFields);
        parent::__construct($controller, $name, $fields, $actions, $required);
        $this->extend('updateForm');
    }

    /**
     * Finish the registration and continue to checkout
     *
     * @param array $data
     * @param ReservationForm $form
     * @return HTTPResponse
     * @throws ValidationException
     */
    public function goToNextStep(array $data, ReservationForm $form)
    {
        $reservation = $form->getReservation();
        foreach ($data['Attendee'] as $attendeeID => $attendeeData) {
            /** @var Attendee $attendee */
            $attendee = Attendee::get()->byID($attendeeID);

            // populate the attendees
            foreach ($attendeeData as $field => $value) {
                // Array value means we have a composite field.
                // This is used by the email field, main attendee to check if we have a correct mail address set.
                // todo, should be moved somewhere in the field classes.
                if (is_array($value)) {
                    if (count(array_unique($value)) !== 1) {
                        $form->sessionFieldError(_t(__CLASS__ . '.EmailError', 'Make sure your email address is spelled correctly'), $field);
                        return $form->getController()->redirectBack();
                    } else {
                        $value = array_shift($value);
                    }
                }

                if (is_int($field)) {
                    $attendee->Fields()->add($field, array('Value' => $value));
                } else {
                    $attendee->setField($field, $value);
                }
            }

            $attendee->write();

            // Set the main contact
            if (isset($attendeeData['Main']) && (bool)$attendeeData['Main']) {
                $reservation->setMainContact($attendeeID);
            }
        }

        // save all attendees with empty titles to set the guest titles
        foreach ($reservation->Attendees()->filter(['Title' => null]) as $attendee) {
            $attendee->write();
        }

        // add the tax modifier
        $reservation->PriceModifiers()->add(TaxModifier::findOrMake($reservation));
        $reservation->calculateTotal();
        $reservation->write();

        $this->extend('beforeNextStep', $data, $form);
        return $this->nextStep();
    }
}
