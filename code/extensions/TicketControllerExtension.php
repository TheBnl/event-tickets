<?php
/**
 * TicketControllerExtension.php
 *
 * @author Bram de Leeuw
 * Date: 09/03/17
 */

namespace Broarm\EventTickets;

use CalendarEvent_Controller;
use Extension;

/**
 * Class TicketControllerExtension
 *
 * @package Broarm\EventTickets
 *
 * @property TicketControllerExtension|TicketExtension|CalendarEvent_Controller $owner
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
        if ($this->owner->Tickets()->count() && $this->owner->getAvailability() > 0) {
            $ticketForm = new TicketForm($this->owner, 'TicketForm', $this->owner->Tickets(), $this->owner->dataRecord);
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
        if ($this->owner->Tickets()->count()) {
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
        return new CheckInController($this->owner->dataRecord);
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
