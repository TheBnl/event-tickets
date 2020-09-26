<?php

namespace Broarm\EventTickets\Extensions;

use Broarm\EventTickets\Controllers\ReservationController;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Core\Extension;

/**
 * Class RegisterStep
 * 
 * @property TicketControllerExtension|TicketExtension|ContentController $owner
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
        return new ReservationController($this->owner->data());
    }
}
