<?php

namespace Broarm\EventTickets\Tasks;

use Broarm\EventTickets\Model\Attendee;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;

/**
 * Class PreviewTicketTask
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
        'NotificationMail',
        'AttendeeMail',
        'MainContactMail'
    );

    /**
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        $preview = $request->remaining();
        $params = $request->allParams();
        $url = Director::absoluteURL("dev/{$params['Action']}/{$params['TaskName']}", true);

        if ($preview && in_array($preview, $this->previews)) {
            //$reservation = Reservation::get()->filter('ReservationCode:not', 'NULL')->last();
            /** @var Attendee $attendee */
            $attendee = Attendee::get()->filter('TicketFileID:not', 0)->last();

            switch ($preview) {
                case 'AttendeeMail':
                case 'PrintableTicket':
                    $data = $attendee;
                    $template = new SSViewer($preview);
                    $html = $template->process($data);
                    echo $html->getValue();
                    break;
                // TODO: preview a real pdf
                // TODO: preview in a iframe ?
                default:
                    $template = new SSViewer($preview);
                    $html = $template->process($attendee->Reservation());
                    Requirements::block('app.css');
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
