<?php

namespace Broarm\EventTickets\Forms;

use Broarm\EventTickets\Extensions\TicketExtension;
use Broarm\EventTickets\Fields\TicketsField;
use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Model\Buyable;
use Broarm\EventTickets\Model\OrderItem;
use Broarm\EventTickets\Session\ReservationSession;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ValidationException;

/**
 * Class TicketForm
 *
 * @package Broarm\EventTickets
 */
class TicketForm extends FormStep
{
    private static $default_classes = [
        'ticket-form'
    ];
    
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
        $this->extend('updateForm');
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
        // $reservation->OrderItems()->removeAll();
        foreach ($data['Tickets'] as $buyableID => $buyableData) {
            $buyable = Buyable::get_by_id($buyableID);
            $amount = $buyableData['Amount'];

            if ($amount) {
                // add order item to the order
                $item = OrderItem::create([
                    'BuyableID' => $buyableID,
                    'ReservationID' => $reservation->ID,
                    'Price' => $buyable->Price,
                    'Amount' => $amount
                ]);
                $reservation->OrderItems()->add($item);

                // create an attendee
                $attendees = $buyable->createAttendees($buyableData['Amount']);
                $reservation->Attendees()->addMany($attendees);
            }
        
            

            // for ($i = 0; $i < $ticketData['Amount']; $i++) {
            //     $attendee = Attendee::create();
            //     $attendee->TicketID = $ticketID;
            //     $attendee->ReservationID = $reservation->ID;
            //     $attendee->TicketPageID = $reservation->TicketPageID;
            //     $attendee->write();
            //     $reservation->Attendees()->add($attendee);
            // }
        }

        $reservation->calculateTotal();
        $reservation->write();

        $this->extend('beforeNextStep', $data, $form, $reservation);
        return $this->nextStep();
    }
}
