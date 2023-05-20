<?php

namespace Broarm\EventTickets\Fields;

use Broarm\EventTickets\Model\Buyable;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\ArrayList;

/**
 * Class TicketsField
 *
 * @package Broarm\EventTickets
 */
class CartTicketsField extends TicketsField
{
    protected $orderItems;
    
    public function __construct($name, $title, $orderItems)
    {
        $this->orderItems = $orderItems;
        $buyables = $orderItems->column('BuyableID');
        if (!empty($buyables)) {
            $tickets = Buyable::get()->filter('ID', $buyables);
        } else {
            $tickets = Buyable::get()->filter('ID', 0);
        }

        parent::__construct($name, $title, $tickets);
    }

    /**
     * Get a list of editable tickets
     * These have an numeric input field
     *
     * @return ArrayList
     */
    protected function getEditableTickets()
    {
        $items = ArrayList::create();
        foreach ($this->orderItems as $orderItem) {
            $ticket = $orderItem->Buyable();
            /** @var OrderItem $orderItem */
            $fieldName = $this->name . "[{$orderItem->ID}][Amount]";
            $range = range(0, $ticket->OrderMax);

            $orderItem->AmountField = DropdownField::create($fieldName, 'Amount', array_combine($range, $range), $orderItem->Amount);

            $availability = $ticket->getAvailability();
            if ($availability < $ticket->OrderMax) {
                $disabled = range($availability + 1, $ticket->OrderMax);
                $orderItem->AmountField->setDisabledItems(array_combine($disabled, $disabled));
            }

            if (!$ticket->getAvailable()) {
                $orderItem->AmountField->setDisabled(true);
            }

            $this->extend('updateOrderItem', $orderItem);
            $items->push($orderItem);
        }

        return $items;
    }

    public function validate($validator)
    {
        if (empty($this->value)) {
            return true;
        }

        // get the sum of selected tickets
        $ticketCount = array_sum(array_map(function ($item) {
            return $item['Amount'];
        }, $this->value));

        // If the sum of tickets is 0 trow the same error as empty
        if ($ticketCount === 0) {
            return true;
        }

        // Check if the ticket is still available
        foreach ($this->value as $id => $amountArray) {
            if (!isset($amountArray['Amount']) || !($amountArray['Amount']) > 0) {
                continue;
            }

            $amount = $amountArray['Amount'];
            $buyable = Buyable::get_by_id($id);
            $available = $buyable->getAvailability();
            if ($available < $amount) {
                $validator->validationError($this->name, _t(
                    'TicketsField.VALIDATION_TO_MUCH',
                    'There are {ticketCount} tickets left',
                    null,
                    array(
                        'ticketCount' => $available
                    )
                ), 'validation');

                return false;
            }
        }

        return false;
    }
}
