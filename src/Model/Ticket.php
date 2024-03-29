<?php

namespace Broarm\EventTickets\Model;

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
        $available = parent::getAvailability();
        $placesAvailable = $this->TicketPage()->getAvailability();
        if ($placesAvailable < $available) {
            return $placesAvailable;
        }

        return $available;
    }

    public function createsAttendees()
    {
        return true;
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
