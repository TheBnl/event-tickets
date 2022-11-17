<?php

namespace Broarm\EventTickets\Extensions;

use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Model\Reservation;
use Mpdf\Output\Destination;
use SilverStripe\Assets\FileNameFilter;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ValidationResult;

/**
 * Class GridFieldDetailFormItemRequestExtension
 * @package Broarm\EventTickets\Extensions
 *
 * @property GridFieldDetailForm_ItemRequest $owner
 */
class GridFieldDetailFormItemRequestExtension extends \SilverStripe\Core\Extension
{
    private static $allowed_actions = [
        'sendReservation',
        'sendTicket',
        'downloadTickets'
    ];

    public function updateItemEditForm(Form $form)
    {
        if (($record = $this->owner->getRecord()) && ($record instanceof Reservation || $record instanceof Attendee)) {
            $connectionActions = CompositeField::create()->setName('ConnectionActions');
            $connectionActions->setFieldHolderTemplate(CompositeField::class . '_holder_buttongroup');

            $sendType = $record instanceof Reservation ? 'sendReservation' : 'sendTicket';
            $sendAction = FormAction::create($sendType, _t(__CLASS__ . '.SendTicketEmail', 'Resend ticket mail'))
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
            $form->Actions()->insertBefore('MajorActions', $connectionActions);
        }
    }
    
    public function sendTicket($data, Form $form)
    {
        /** @var Attendee $record */
        if (($record = $this->owner->getRecord()) && $record instanceof Attendee) {
            if ($record->sendTicket()) {
                $form->sessionMessage(_t(__CLASS__ . '.SentTickets', 'De tickets zijn verstuurd naar {email}', null, [
                    'email' => $record->getEmail()
                ]), ValidationResult::TYPE_GOOD);
            } else {
                $form->sessionMessage(_t(__CLASS__ . '.SentTicketsError', 'De tickets konden niet verstuurd worden naar {email}', null, [
                    'email' => $record->getEmail()
                ]), ValidationResult::TYPE_ERROR);
            }
        }

        $controller = $this->owner->getController();
        $url = $this->owner->getEditLink($record->ID);

        $controller->getRequest()->addHeader('X-Pjax', 'Content');
        return $controller->redirect($url . "?sent=" . uniqid() , 302);
    }

    public function sendReservation()
    {
        /** @var Reservation $record */
        if (($record = $this->owner->getRecord()) && $record instanceof Reservation) {
            $record->sendReservation();
        }
    }

    public function downloadTickets()
    {
        /** @var Reservation $record */
        if (($record = $this->owner->getRecord()) && ($record instanceof Reservation || $record instanceof Attendee)) {
            $eventName = $record->TicketPage()->getTitle();
            $pdf = $record->createTicketFile();
            $fileName = FileNameFilter::create()->filter("Tickets {$eventName}.pdf");
            
            // header("Content-type:application/pdf");
            // header("Content-Disposition:attachment;filename='{$fileName}'");
            return $pdf->Output($fileName, Destination::INLINE);
        }
    }
}
