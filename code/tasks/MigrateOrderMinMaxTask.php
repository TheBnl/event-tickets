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
use Guzzle\Common\Event;

/**
 * Class MigrateOrderMinMaxTask
 * Migrate the min and max field data from the Event to the ticket
 *
 * @package Broarm\EventTickets
 */
class MigrateOrderMinMaxTask extends BuildTask
{
    protected $title = 'Migrate the order min and max';

    protected $description = 'Migrate the order min and max field data from the Event to the ticket';

    protected $enabled = true;

    /**
     * @param \SS_HTTPRequest $request
     */
    public function run($request)
    {
        if (!Director::is_cli()) echo '<pre>';
        echo "Start migrating the order min and max\n\n";

        /** @var \CalendarEvent|TicketExtension $event */
        foreach (\CalendarEvent::get() as $event) {
            if (($tickets = $event->Tickets()) && $tickets->exists()) {
                $max = $event->OrderMax;
                $min = $event->OrderMin;
                /** @var Ticket $ticket */
                foreach ($tickets as $ticket) {
                    if (empty($ticket->OrderMax)) $ticket->OrderMax = $max;
                    if (empty($ticket->OrderMin)) $ticket->OrderMin = $min;
                    $ticket->write();
                    echo "[$event->Title] Set order min on ticket {$ticket->Title} to [$min] and max to [$max] \n";
                }
            }
        }

        echo "\n\nDone migration";
        if (!Director::is_cli()) echo '</pre>';
    }
}
