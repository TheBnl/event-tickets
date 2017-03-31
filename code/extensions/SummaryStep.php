<?php
/**
 * SummaryStep.php
 *
 * @author Bram de Leeuw
 * Date: 29/03/17
 */


namespace Broarm\EventTickets;

use CalendarEvent_Controller;
use Extension;

/**
 * Class SummaryStep
 * 
 * @property TicketControllerExtension|TicketExtension|CalendarEvent_Controller $owner
 */
class SummaryStep extends Extension
{
    private static $allowed_actions = array(
        'summary'
    );

    /**
     * Continue to the summary step
     *
     * @return SummaryController
     */
    public function summary()
    {
        return new SummaryController($this->owner->dataRecord);
    }
}
