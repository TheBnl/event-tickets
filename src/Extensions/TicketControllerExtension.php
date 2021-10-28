<?php

namespace Broarm\EventTickets\Extensions;

use Broarm\EventTickets\Controllers\CheckInController;
use Broarm\EventTickets\Checkout\Steps\CheckoutSteps;
use Broarm\EventTickets\Forms\TicketForm;
use Broarm\EventTickets\Forms\WaitingListRegistrationForm;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Core\Extension;

/**
 * Class TicketControllerExtension
 *
 * @package Broarm\EventTickets
 *
 * @property TicketControllerExtension|TicketExtension|ContentController $owner
 */
class TicketControllerExtension extends Extension
{
    private static $allowed_actions = array(
        'TicketForm',
        'WaitingListRegistrationForm',
        'checkin'
    );

    /**
     * Get the ticket form with available tickets
     *
     * @return TicketForm
     */
    public function TicketForm()
    {
        if ($this->owner->Tickets()->count() && $this->owner->getTicketsAvailable()) {
            $ticketForm = new TicketForm($this->owner, 'TicketForm', $this->owner->Tickets(), $this->owner->data());
            $ticketForm->setNextStep(CheckoutSteps::start());
            return $ticketForm;
        } else {
            return null;
        }
    }

    /**
     * show the waiting list form when the event is sold out
     *
     * @return WaitingListRegistrationForm
     */
    public function WaitingListRegistrationForm()
    {
        if (
            $this->owner->Tickets()->count()
            && $this->owner->getTicketsSoldOut()
            && !$this->owner->getEventExpired()
        ) {
            return new WaitingListRegistrationForm($this->owner, 'WaitingListRegistrationForm');
        } else {
            return null;
        }
    }

    /**
     * Go to the check in controller
     *
     * @return CheckInController
     */
    public function checkIn()
    {
        return new CheckInController($this->owner->data());
    }

    /**
     * Checks the waiting list var
     *
     * @return mixed
     */
    public function getWaitingListSuccess()
    {
        return $this->owner->getRequest()->getVar('waitinglist');
    }


}
