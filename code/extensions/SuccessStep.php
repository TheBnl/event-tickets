<?php
/**
 * SuccessStep.php
 *
 * @author Bram de Leeuw
 * Date: 29/03/17
 */


namespace Broarm\EventTickets;

use CalendarEvent_Controller;
use Extension;

/**
 * Class SuccessStep
 * 
 * @property TicketControllerExtension|TicketExtension|CalendarEvent_Controller $owner
 */
class SuccessStep extends Extension
{
    private static $allowed_actions = array(
        'success'
    );

    /**
     * End at the success step
     *
     * @return SuccessController
     */
    public function success()
    {
        return new SuccessController($this->owner->dataRecord);
    }
}
