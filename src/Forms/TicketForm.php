<?php

namespace Broarm\EventTickets\Forms;

use Broarm\EventTickets\Extensions\TicketsField;
use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Session\ReservationSession;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataList;

/**
 * Class TicketForm
 *
 * @package Broarm\EventTickets
 */
class TicketForm extends FormStep
{
    /**
     * @var DataList
     */
    protected $tickets;

    protected $event;

    public function __construct($controller, $name, DataList $tickets = null, $event = null)
    {
        $this->event = $event;
        $fields = FieldList::create(
            TicketsField::create('Tickets', '', $this->tickets = $tickets)
        );

        $actions = FieldList::create(
            FormAction::create('handleTicketForm', _t('TicketForm.MAKE_RESERVATION', 'Make reservation'))
                ->setDisabled($event->getAvailability() === 0)
        );

        $requiredFields = RequiredFields::create(array('Tickets'));

        parent::__construct($controller, $name, $fields, $actions, $requiredFields);
    }

    /**
     * Get the attached reservation
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * Handle the ticket form registration
     *
     * @param array      $data
     * @param TicketForm $form
     *
     * @return string
     */
    public function handleTicketForm(array $data, TicketForm $form)
    {
        $reservation = ReservationSession::start($this->getEvent());

        foreach ($data['Tickets'] as $ticketID => $ticketData) {
            for ($i = 0; $i < $ticketData['Amount']; $i++) {
                $attendee = Attendee::create();
                $attendee->TicketID = $ticketID;
                $attendee->ReservationID = $reservation->ID;
                $attendee->EventID = $reservation->EventID;
                $attendee->write();
                $reservation->Attendees()->add($attendee);
            }
        }

        $reservation->calculateTotal();
        $reservation->write();

        $this->extend('beforeNextStep', $data, $form, $reservation);
        return $this->nextStep();
    }
}
