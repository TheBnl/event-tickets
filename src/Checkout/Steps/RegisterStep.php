<?php

namespace Broarm\EventTickets\Checkout\Steps;

use Broarm\EventTickets\Forms\ReservationForm;
use Broarm\EventTickets\Session\ReservationSession;
use SilverStripe\Forms\Form;

class RegisterStep extends CheckoutStep
{
    public $step = 'register';

    private static $allowed_actions = array(
        'register',
        'ReservationForm'
    );

    public function register()
    {
        if (!$reservation = $this->getReservation()) {
            return $this->owner->redirect($this->owner->Link());
        }

        return [
            'Form' => $this->ReservationForm()
        ];
    }

    public function ReservationForm()
    {
        $reservationForm = new ReservationForm($this->owner, 'ReservationForm', ReservationSession::get());
        $reservationForm->setNextStep(CheckoutSteps::nextStep($this->step));
        return $reservationForm;
    }
}
