<?php

namespace Broarm\EventTickets\Controllers;

use Broarm\EventTickets\Session\ReservationSession;
use Exception;

/**
 * Class SuccessController
 *
 * @package Broarm\EventTickets
 */
class SuccessController extends CheckoutStepController
{
    protected $step = 'success';

    /**
     * Init the success controller, check if files should be created and send
     * @throws Exception
     */
    public function init()
    {
        parent::init();
        $reservation = $this->getReservation();

        // If we get to the success controller form any state except PENDING or PAID
        // This would mean someone would be clever and change the url from summary to success bypassing the payment
        // End the session, thus removing the reservation, and redirect back
        if (!in_array($reservation->Status, array('PENDING', 'PAID'))) {
            ReservationSession::end();
            $this->redirect($this->Link('/'));
        }
    }
}
