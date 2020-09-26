<?php

namespace Broarm\EventTickets\Extensions;

use Broarm\EventTickets\Controllers\SummaryController;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Core\Extension;

/**
 * Class SummaryStep
 * 
 * @property TicketControllerExtension|TicketExtension|ContentController $owner
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
        return new SummaryController($this->owner->data());
    }
}
