<?php

namespace Broarm\EventTickets\Session;

use Broarm\EventTickets\Extensions\TicketExtension;
use Broarm\EventTickets\Model\CartPage;
use Broarm\EventTickets\Model\CheckoutPage;
use Broarm\EventTickets\Model\Reservation;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;
use SilverStripe\View\TemplateGlobalProvider;

/**
 * Class ReservationSession
 *
 * @package Broarm\EventTickets
 */
class ReservationSession
{
    use Configurable;
    
    const KEY = 'EventTickets';

    private static $cart_mode = false;

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
        if ($id = $session->get(self::KEY)) {
            return Reservation::get_by_id($id);
        }

        return null;
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
     * @param $ticketPage
     * @return Reservation
     * @throws ValidationException
     */
    public static function start()//SiteTree $ticketPage)
    {
        // Check if we can start a reservation session on the given page
        // if (!$ticketPage->hasExtension(TicketExtension::class)) {
        //     return null;
        // }

        // If in cart mode, check if there is a reservation in the session
        $cartMode = self::config()->get('cart_mode');
        if ($cartMode && $reservation = self::get()) {
            return $reservation;
        }

        $reservation = Reservation::create();
        // $reservation->TicketPageID = $ticketPage->ID;
        $reservation->write();
        self::set($reservation);
        return $reservation;
    }

    /**
     * End the Ticket session
     */
    public static function end()
    {
        // If the session is ended while in cart state, remove the reservation.
        // The session is only ended in these states when iffy business is going on.
        if (self::get() && in_array(self::get()->Status, array('CART'))) {
            self::get()->delete();
        }

        /** @var HTTPRequest $request */
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $session->clear(self::KEY);
        $session->save($request);
    }
}
