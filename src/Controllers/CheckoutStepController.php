<?php

namespace Broarm\EventTickets\Controllers;

use Broarm\EventTickets\Model\Reservation;
use Broarm\EventTickets\Session\ReservationSession;
use Exception;
use PageController;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\SSViewer;

/**
 * Class CheckoutStepController
 *
 * @package Broarm\EventTickets
 */
abstract class CheckoutStepController extends PageController
{
    protected $step = null;

    /**
     * @var Reservation|null
     */
    protected $reservation = null;

    /**
     * Init the controller and check if the current step is allowed
     * @throws Exception
     */
    public function init()
    {
        // If the step is not a registered step exit
        if (!in_array($this->step, CheckoutSteps::getSteps())) {
            $this->redirect($this->Link('/'));
        }

        // If no ReservationSession exists redirect back to the base event controller
        elseif (empty(ReservationSession::get())) {
            $this->redirect($this->Link('/'));
        }

        // If the reservation has been processed end the session and redirect
        elseif (ReservationSession::get()->Status === 'PAID' && $this->step != 'success') {
            ReservationSession::end();
            $this->redirect($this->Link('/'));
        } else {
            $this->reservation = ReservationSession::get();
            parent::init();
        }
    }

    /**
     * Force the controller action
     *
     * @param string $action
     *
     * @return SSViewer
     */
    public function getViewer($action)
    {
        if ($action === 'index') {
            $action = $this->step;
        }

        return parent::getViewer($action);
    }

    /**
     * Get a relative link to the current controller
     *
     * @param null $action
     *
     * @return string
     */
    public function Link($action = null)
    {
        if (!$action) {
            $action = $this->step;
        }

        return $this->dataRecord->RelativeLink($action);
    }

    /**
     * Get the current reservation
     *
     * @return Reservation
     */
    public function getReservation() {
        return $this->reservation;
    }

    /**
     * Get a link to the next step
     *
     * @return string
     */
    public function getNextStepLink()
    {
        return $this->Link(CheckoutSteps::nextStep($this->step));
    }

    /**
     * Get the checkout steps
     *
     * @return ArrayList
     */
    public function CheckoutSteps()
    {
        return CheckoutSteps::get($this);
    }
}
