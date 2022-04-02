<?php

namespace Broarm\EventTickets\Extensions;

use Broarm\EventTickets\Controllers\CheckInController;
use Broarm\EventTickets\Model\Reservation;
use Mpdf\Output\Destination;
use SilverStripe\Assets\FileNameFilter;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\LiteralField;

/**
 * Class GridFieldDetailFormItemRequestExtension
 * @package Broarm\EventTickets\Extensions
 *
 * @property GridFieldDetailForm_ItemRequest $owner
 */
class GridFieldDetailFormItemRequestExtension extends \SilverStripe\Core\Extension
{
    private static $allowed_actions = [
        'sendTicketEmail',
        'downloadTickets',
        'checkin'
    ];

    public function updateItemEditForm(Form $form)
    {
        $connectionActions = CompositeField::create()->setName('ConnectionActions');
        $connectionActions->setFieldHolderTemplate(CompositeField::class . '_holder_buttongroup');

        if (($record = $this->owner->getRecord()) && $record instanceof Reservation) {

            $sendAction = FormAction::create('sendTicketEmail', _t(__CLASS__ . '.SendTicketEmail', 'Resend ticket mail'))
                ->addExtraClass('grid-print-button btn btn-outline-secondary font-icon-p-mail')
                ->setAttribute('data-icon', 'p-mail')
                ->setUseButtonTag(true);

            $connectionActions->push($sendAction);

            $printLabel = _t(__CLASS__ . '.DownloadTickets', 'Download tickets');
            $printLink = $this->owner->Link('downloadTickets');
            $printWindow = <<<JS
                window.open('$printLink', 'print_order', 'toolbar=0,scrollbars=1,location=1,statusbar=0,menubar=0,resizable=1,width=800,height=600,left = 50,top = 50');
                return false;
JS;

            $downloadAction = LiteralField::create(
                    'downloadTickets', 
                    "<button class=\"grid-print-button btn btn-outline-secondary font-icon-p-download\" onclick=\"javascript:{$printWindow}\">{$printLabel}</button>"
            );

            $connectionActions->push($downloadAction);
        }

        if (($record = $this->owner->getRecord()) && $record->hasExtension(TicketExtension::class)) {
            $eventLink = Controller::join_links(['checkin', 'event', $record->ID]);
            $checkinLink = $this->owner->Link($eventLink);
            $checkinLabel = _t(__CLASS__ . '.StartCheckIn', 'Start check in');
            $checkinAction = LiteralField::create(
                'StartCheckIn',
                "<a href='$checkinLink' target='_blank' class='no-ajax btn btn-outline-secondary font-icon-checklist'>$checkinLabel</a>"
            );
            $connectionActions->push($checkinAction);
        }

        if ($connectionActions->FieldList()->exists()) {
            $form->Actions()->insertBefore('MajorActions', $connectionActions);
        }
    }

    public function sendTicketEmail()
    {
        /** @var Reservation $record */
        if (($record = $this->owner->getRecord()) && $record instanceof Reservation) {
            $record->send();
        }
    }

    public function downloadTickets()
    {
        /** @var Reservation $record */
        if (($record = $this->owner->getRecord()) && $record instanceof Reservation) {
            $eventName = $record->TicketPage()->getTitle();
            $pdf = $record->createTicketFile();
            $fileName = FileNameFilter::create()->filter("Tickets {$eventName}.pdf");
            
            // header("Content-type:application/pdf");
            // header("Content-Disposition:attachment;filename='{$fileName}'");
            return $pdf->Output($fileName, Destination::INLINE);
        }
    }

    public function checkin()
    {
        return new CheckInController($this->owner->getRecord());
    }
}
