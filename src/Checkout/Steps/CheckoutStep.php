<?php

namespace Broarm\EventTickets\Checkout\Steps;

use Broarm\EventTickets\Controllers\CheckoutPageController;
use Broarm\EventTickets\Extensions\TicketControllerExtension;
use Broarm\EventTickets\Model\CheckoutPage;
use Broarm\EventTickets\Model\Reservation;
use Broarm\EventTickets\Session\ReservationSession;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\ArrayList;

/**
 * Class CheckoutStep
 * @package Broarm\EventTickets\Checkout\Steps
 * @property RequestHandler|TicketControllerExtension $owner
 */
abstract class CheckoutStep extends Extension
{
    protected $step = null;

    /**
     * @var Reservation|null
     */
    protected $reservation = null;

    public function onBeforeInit()
    {
        $this->reservation = ReservationSession::get();
    }

    public function getReservation()
    {
        return $this->reservation;
    }

    public function getCurrentStep()
    {
        return $this->step;
    }

    /**
     * Get a link to the next step
     *
     * @return string
     */
    public function getNextStepLink()
    {
        $controller = $this->owner;
        if (ReservationSession::config()->get('cart_mode') && !($controller instanceof CheckoutPageController)) {
            $checkout = CheckoutPage::inst();
            $controller = CheckoutPageController::create($checkout);
        }

        return $controller->Link(CheckoutSteps::nextStep($this->step));
    }

    /**
     * Get the checkout steps
     *
     * @return ArrayList
     */
    public function CheckoutSteps()
    {
        return CheckoutSteps::get($this->owner);
    }
}
