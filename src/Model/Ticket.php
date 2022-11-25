<?php

namespace Broarm\EventTickets\Model;

use Broarm\EventTickets\Extensions\TicketExtension;
use Exception;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\CurrencyField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBField;

/**
 * Class Ticket
 *
 * @package Broarm\EventTickets
 */
class Ticket extends Buyable
{
    private static $table_name = 'EventTickets_Ticket';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        return $fields;
    }

    /**
     * Validate the available capacity
     *
     * @return bool
     */
    protected function validateAvailability()
    {
        $available = parent::validateAvailability();
        if ($available) {
            // Tickets also count towards total availability
            return $this->TicketPage()->getAvailability() > 0;
        }

        return $available;
    }

    /**
     * Get the ticket availability for this type
     * A ticket always checks if places are available
     */
    public function getAvailability()
    {
        $placesAvailable = $this->TicketPage()->getAvailability();
        if ($placesAvailable > 0 && $this->Capacity !== 0) {
            $sold = OrderItem::get()->filter(['BuyableID' => $this->ID])->sum('Amount');
            $available = $this->Capacity - $sold;
            return $available < 0 ? 0 : $available;
        }

        return $placesAvailable;
    }

    public function createAttendees($amount)
    {
        $attendees = [];
        for ($i = 0; $i < $amount; $i++) {
            $attendee = Attendee::create();
            $attendee->TicketID = $this->ID;
            // $attendee->ReservationID = $reservation->ID;
            $attendee->TicketPageID = $this->TicketPageID;
            $attendee->write();
            $attendees[] = $attendee;
            // $reservation->Attendees()->add($attendee);
        }

        return $attendees;
    }
}
