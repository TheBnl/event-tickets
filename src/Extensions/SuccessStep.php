<?php

namespace Broarm\EventTickets\Extensions;

use Broarm\EventTickets\Controllers\SuccessController;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Core\Extension;

/**
 * Class SuccessStep
 * 
 * @property TicketControllerExtension|TicketExtension|ContentController $owner
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
        return new SuccessController($this->owner->data());
    }
}
