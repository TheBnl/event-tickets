<?php

namespace Broarm\EventTickets\Controllers;

use Broarm\EventTickets\Checkout\Steps\CheckoutSteps;
use Broarm\EventTickets\Model\CartPage;
use PageController;

class CheckoutPageController extends PageController
{   
    private static $allowed_actions = [];

    public function init()
    {
        parent::init();

        $action = $this->getAction();
        $reservation = $this->getReservation();
        if ($action !== 'success' && (!$reservation || $reservation->isEmpty())) {
            $cartPage = CartPage::inst();
            return $this->redirect($cartPage->Link());
        }
    }

    public function index()
    {
        return $this->redirect($this->Link(CheckoutSteps::start()));
    }
}
