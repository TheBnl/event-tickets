<?php

namespace Broarm\EventTickets\Session;

use Broarm\EventTickets\Model\Reservation;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;

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
        /** @var HTTPRequest $request */
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        return Reservation::get_by_id($session->get(self::KEY));
    }

    /**
     * Set the session variable
     *
     * @param Reservation $reservation
     */
    public static function set(Reservation $reservation)
    {
        /** @var HTTPRequest $request */
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $session->set(self::KEY, $reservation->ID);
        $session->save($request);
    }

    /**
     * Start the ticket session
     *
     * @return Reservation
     * @throws ValidationException
     */
    public static function start()
    {
        $reservation = Reservation::create();
        // todo bind reservation to event? How to abstract this
        //$reservation->EventID = $event->ID;
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

        /** @var HTTPRequest $request */
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $session->clear(self::KEY);
        $session->save($request);
    }
}
