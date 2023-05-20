<?php

namespace Broarm\EventTickets\Forms;

use Broarm\EventTickets\Extensions\TicketExtension;
use Broarm\EventTickets\Fields\TicketsField;
use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Model\Buyable;
use Broarm\EventTickets\Model\OrderItem;
use Broarm\EventTickets\Session\ReservationSession;
use Huygens\EventTickets\Ticket;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;

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
            $action = FormAction::create('checkout', _t(__CLASS__ . '.MakeReservation', 'Make reservation'))
                // ->setDisabled($controller->getAvailability() === 0)
        );

        if (ReservationSession::config()->get('cart_mode')) {
            $action->setTitle(_t(__CLASS__ . '.DirectCheckout', 'Direct checkout'));
            $actions->add(FormAction::create(
                'addToCart', 
                _t(__CLASS__ . '.AddToCart', 'Add to cart')
            ));
        } 

        $requiredFields = RequiredFields::create(['Tickets']);

        parent::__construct($controller, $name, $fields, $actions, $requiredFields);
        $this->extend('updateForm');
    }

    public function addToCart(array $data, TicketForm $form)
    {
        $reservation = $this->handleTicketForm($data, $form);
        $this->extend('afterAddToCart', $data, $form, $reservation);
        $form->sessionMessage(_t(__CLASS__ . '.AddedToCart', 'Added to cart'), ValidationResult::TYPE_GOOD);
        return $this->getController()->redirectBack();
    }

    public function checkout(array $data, TicketForm $form)
    {
        $reservation = $this->handleTicketForm($data, $form);
        $this->extend('beforeNextStep', $data, $form, $reservation);
        return $this->nextStep();
    }

    protected function handleTicketForm(array $data, TicketForm $form)
    {
        $reservation = ReservationSession::start();
        foreach ($data['Tickets'] as $buyableID => $buyableData) {
            $buyable = Buyable::get_by_id($buyableID);
            $amount = $buyableData['Amount'] ?? 0;
            
            $item = $reservation->OrderItems()->find('BuyableID', $buyableID);
            if ($item && $item->exists()) {
                $item->Amount = $amount;
                $item->write();

                if ($amount == 0) {
                    $item->delete();
                }
            } elseif ($amount > 0) {
                $item = OrderItem::create([
                    'BuyableID' => $buyableID,
                    'ReservationID' => $reservation->ID,
                    'Price' => $buyable->Price,
                    'Amount' => $amount
                ]);
                $reservation->OrderItems()->add($item);
            }

            // Clear the old attendees and add the new amount
            $reservation->Attendees()->filter(['TicketID' => $buyable->ID])->removeAll();
            $attendees = $buyable->createAttendees($amount);
            $reservation->Attendees()->addMany($attendees);
        }

        $reservation->calculateTotal();
        $reservation->write();
        return $reservation;
    }
}
