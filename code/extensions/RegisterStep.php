<?php
/**
 * RegisterStep.php
 *
 * @author Bram de Leeuw
 * Date: 29/03/17
 */


namespace Broarm\EventTickets;

use CalendarEvent_Controller;
use Extension;

/**
 * Class RegisterStep
 * 
 * @property TicketControllerExtension|TicketExtension|CalendarEvent_Controller $owner
 */
class RegisterStep extends Extension
{
    private static $allowed_actions = array(
        'register'
    );

    /**
     * Continue to the registration step
     *
     * @return ReservationController
     */
    public function register()
    {
        return new ReservationController($this->owner->dataRecord);
    }
}
