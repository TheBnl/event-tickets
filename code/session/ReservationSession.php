<?php
/**
 * Session.php
 *
 * @author Bram de Leeuw
 * Date: 14/03/17
 */

namespace Broarm\EventTickets;

use CalendarEvent;
use DataObject;
use Session;

/**
 * Class ReservationSession
 *
 * @package Broarm\EventTickets
 */
class ReservationSession
{
    const KEY = 'EventTickets';

    /**
     * Get the session variable
     *
     * @return Reservation|DataObject
     */
    public static function get()
    {
        return Reservation::get()->byID(
            Session::get(self::KEY)
        );
    }

    /**
     * Set the session variable
     *
     * @param Reservation $reservation
     */
    public static function set(Reservation $reservation)
    {
        Session::set(self::KEY, $reservation->ID);
    }

    /**
     * Start the ticket session
     *
     * @param CalendarEvent $event
     *
     * @return Reservation
     */
    public static function start(CalendarEvent $event)
    {
        $reservation = Reservation::create();
        $reservation->EventID = $event->ID;
        $reservation->write();
        self::set($reservation);
        return $reservation;
    }

    /**
     * End the Ticket session
     */
    public static function end()
    {
        // If the session is ended while in cart or pending state, remove the reservation.
        // The session is only ended in these states when iffy business is going on.
        if (in_array(self::get()->Status, array('CART', 'PENDING'))) {
            self::get()->delete();
        }

        Session::set(self::KEY, null);
        Session::clear(self::KEY);
    }
}