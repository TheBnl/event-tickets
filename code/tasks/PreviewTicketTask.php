<?php
/**
 * PreviewTicketTask.php
 *
 * @author Bram de Leeuw
 * Date: 27/03/17
 */

namespace Broarm\EventTickets;

use BuildTask;
use CalendarEvent_Controller;
use Director;
use Dompdf\Dompdf;
use SSViewer;

/**
 * Class PreviewTicketTask
 * Based of the ShopEmailPreviewTask by Anselm Christophersen
 *
 * @package Broarm\EventTickets
 */
class PreviewTicketTask extends BuildTask
{
    protected $title = 'Preview Ticket email or pdf';

    protected $description = 'Preview Ticket email or pdf';

    protected $previews = array(
        'PrintableTicket',
        'ReservationMail',
        'NotificationMail'
    );

    /**
     * @param \SS_HTTPRequest $request
     */
    public function run($request)
    {
        $preview = $request->remaining();
        $params = $request->allParams();
        $url = Director::absoluteURL("dev/{$params['Action']}/{$params['TaskName']}", true);

        if ($preview && in_array($preview, $this->previews)) {
            $reservation = Reservation::get()->filter('TicketFileID:not', 0)->last();

            // Set the template and parse the data
            $data = $reservation->getViewableData();

            $template = new SSViewer($preview);
            $html = $template->process($data);

            switch ($preview) {
                // TODO: preview a real pdf
                // TODO: preview in a iframe ?
                default:
                    \Requirements::block('app.css');
                    echo $html->getValue();
            }
        }

        echo '<hr><h2>Choose Preview</h2>';
        echo '<ul>';
        foreach ($this->previews as $preview) {
            echo '<li><a href="' . $url . '/' . $preview . '">' . $preview . '</a></li>';
        }
        echo '</ul><hr>';

        exit();
    }
}
