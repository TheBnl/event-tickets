<?php

namespace Broarm\EventTickets\Controllers;

use Broarm\EventTickets\Forms\ReservationForm;
use Broarm\EventTickets\Session\ReservationSession;

/**
 * Class ReservationController
 *
 * @package Broarm\EventTickets
 */
class ReservationController extends CheckoutStepController
{
    private static $allowed_actions = array(
        'ReservationForm'
    );

    protected $step = 'register';

    /**
     * Get the reservation form
     *
     * @return ReservationForm
     */
    public function ReservationForm()
    {
        $reservationForm = new ReservationForm($this, 'ReservationForm', ReservationSession::get());
        $reservationForm->setNextStep(CheckoutSteps::nextStep($this->step));
        return $reservationForm;
    }
}
