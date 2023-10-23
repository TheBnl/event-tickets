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
        $controller = $this->getController();
        $reservation = ReservationSession::start($controller->data());

        foreach ($data['Tickets'] as $buyableID => $buyableData) {
            $buyable = Buyable::get_by_id($buyableID);
            $data['Tickets'][$buyableID]['Buyable'] = $buyable;
        }
        
        $amountSum = array_sum(array_map(function ($ticket) {
            if ($ticket['Buyable']->createsAttendees()) {
                return $ticket['Amount'];
            } else {
                return 0;
            }
        }, $data['Tickets']));
        
        // is sum is bigger than available throw error
        $amountAvailable = $controller->getAvailability();
        if ($amountSum > $amountAvailable) {
            $form->sessionError(_t(
                __CLASS__ . '.OnlyAmountAvailable', 
                'There are only {amount} places available',
                null,
                [
                    'amount' => $amountAvailable,
                ]
            ));
            return $form->getController()->redirectBack();
        }
        
        foreach ($data['Tickets'] as $buyableID => $buyableData) {
            $buyable = $buyableData['Buyable'];
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
        }

        $reservation->calculateTotal();
        $reservation->write();

        $this->extend('beforeNextStep', $data, $form, $reservation);
        return $this->nextStep();
    }
}
