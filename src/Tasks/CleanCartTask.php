<?php

namespace Broarm\EventTickets\Tasks;

use Broarm\EventTickets\Model\Reservation;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;

/**
 * Class CleanCartTask
 * Cleanup discarded tasks
 *
 * @package Broarm\EventTickets
 */
class CleanCartTask extends BuildTask
{
    protected $title = 'Cleanup cart task';

    protected $description = 'Cleanup discarded ticket shop carts';

    /**
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        if (!Director::is_cli()) echo '<pre>';
        echo "Start cleaning\n\n";

        /** @var Reservation $reservation */
        foreach (Reservation::get()->filter(['Status' => Reservation::STATUS_CART]) as $reservation) {
            if ($reservation->isDiscarded()) {
                echo "Delete reservation {$reservation->ID}\n";
                $reservation->delete();
            }
        }

        echo "\n\nDone cleaning";
        if (!Director::is_cli()) echo '</pre>';
    }
}
