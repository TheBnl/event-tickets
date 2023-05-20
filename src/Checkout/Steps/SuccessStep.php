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
        ReservationSession::end();
        return $this->owner;
    }
}
