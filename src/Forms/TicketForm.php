<?php

namespace Broarm\EventTickets\Forms;

use Broarm\EventTickets\Extensions\TicketExtension;
use Broarm\EventTickets\Fields\TicketsField;
use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Session\ReservationSession;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ValidationException;

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

    public function __construct(RequestHandler $controller, $name, DataList $tickets = null)
    {
        $fields = FieldList::create(
            TicketsField::create('Tickets', '', $this->tickets = $tickets)
        );

        $actions = FieldList::create(
            FormAction::create('handleTicketForm', _t(__CLASS__ . '.MakeReservation', 'Make reservation'))
                ->setDisabled($controller->getAvailability() === 0)
        );

        $requiredFields = RequiredFields::create(['Tickets']);

        parent::__construct($controller, $name, $fields, $actions, $requiredFields);
    }

    /**
     * Handle the ticket form registration
     *
     * @param array $data
     * @param TicketForm $form
     * @return HTTPResponse
     * @throws ValidationException
     */
    public function handleTicketForm(array $data, TicketForm $form)
    {
        $reservation = ReservationSession::start($this->getController()->data());
        foreach ($data['Tickets'] as $ticketID => $ticketData) {
            for ($i = 0; $i < $ticketData['Amount']; $i++) {
                $attendee = Attendee::create();
                $attendee->TicketID = $ticketID;
                $attendee->ReservationID = $reservation->ID;
                $attendee->TicketPageID = $reservation->TicketPageID;
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
