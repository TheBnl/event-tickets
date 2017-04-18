<?php
/**
 * PreviewTicketTask.php
 *
 * @author Bram de Leeuw
 * Date: 27/03/17
 */

namespace Broarm\EventTickets;

use BuildTask;
use Director;

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
     * @param \SS_HTTPRequest $request
     */
    public function run($request)
    {
        if (!Director::is_cli()) echo '<pre>';
        echo "Start cleaning\n\n";

        /** @var Reservation $reservation */
        foreach (Reservation::get() as $reservation) {
            if ($reservation->isDiscarded()) {
                echo "Delete reservation {$reservation->ID}\n";
                $reservation->delete();
            }
        }

        echo "\n\nDone cleaning";
        if (!Director::is_cli()) echo '</pre>';
    }
}
