<?php

namespace Broarm\EventTickets\Checkout\Steps;

use Broarm\EventTickets\Model\Reservation;
use Broarm\EventTickets\Session\ReservationSession;

class SuccessStep extends CheckoutStep
{
    public $step = 'success';

    private static $allowed_actions = array(
        'success'
    );

    /**
     * End at the success step
     */
    public function success()
    {
        // If we get to the success controller form any state except PENDING or PAID
        // This would mean someone would be clever and change the url from summary to success bypassing the payment
        // End the session, thus removing the reservation, and redirect back
        $reservation = $this->getReservation();
        if (!$reservation || !in_array($reservation->Status, [Reservation::STATUS_PENDING, Reservation::STATUS_PAID])) {
            ReservationSession::end();
            return $this->owner->redirect($this->owner->Link());
        }

        return $this->owner;
    }
}
