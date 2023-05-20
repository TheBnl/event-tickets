<?php

namespace Broarm\EventTickets\Forms;

use Broarm\EventTickets\Fields\CartTicketsField;
use Broarm\EventTickets\Session\ReservationSession;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataList;

class CartForm extends FormStep
{
    private static $default_classes = [
        'cart-form'
    ];
    
    public function __construct(RequestHandler $controller, $name, DataList $tickets = null)
    {
        $fields = FieldList::create();
        $requiredFields = RequiredFields::create();
        $actions = FieldList::create([
            $checkoutAction = FormAction::create('handleCart', _t(__CLASS__ . '.HandleCart', 'Update cart'))
        ]);

        if (($reservation = ReservationSession::get()) && !$reservation->isEmpty()) {
            $fields->add(CartTicketsField::create('OrderItems', '', $reservation->OrderItems()));
            $requiredFields->addRequiredField('OrderItems');
        } else {
            $fields->add(
                LiteralField::create('CartEmpty', _t(__CLASS__ . '.CartEmpty', '<p>Your cart is empty</p>'))
            );
            $checkoutAction->setDisabled(true);
        }

        parent::__construct($controller, $name, $fields, $actions, $requiredFields);
        $this->extend('updateForm');
    }

    public function handleCart(array $data, Form $form)
    {
        $reservation = ReservationSession::start();
        foreach ($data['OrderItems'] as $orderItemID => $buyableData) {
            $amount = $buyableData['Amount'];
            
            $orderItem = $reservation->OrderItems()->byID($orderItemID);
            if ($amount > 0) {
                $orderItem->Amount = $amount;
                $orderItem->write();
            } else {
                $orderItem->delete();
            }
            
            // Clear the old attendees and add the new amount
            $reservation->Attendees()->filter(['TicketID' => $orderItem->BuyableID])->removeAll();
            $attendees = $orderItem->Buyable()->createAttendees($amount);
            $reservation->Attendees()->addMany($attendees);            
        }

        $reservation->calculateTotal();
        $reservation->write();

        return $this->getController()->redirectBack();
    }
}
