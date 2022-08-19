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
    private function validateAvailability()
    {
        return $this->TicketPage()->getAvailability() > 0;
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
