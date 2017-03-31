<?php
/**
 * EventReservation.php
 *
 * @author Bram de Leeuw
 * Date: 09/03/17
 */

namespace Broarm\EventTickets;

use CalendarEvent;
use CalendarEvent_Controller;
use DataObject;
use Director;
use Dompdf\Dompdf;
use FieldList;
use File;
use Folder;
use HasManyList;
use ManyManyList;
use Member;
use SSViewer;
use Tab;
use TabSet;
use ViewableData;

/**
 * Class EventReservation
 *
 * @package Broarm\EventTickets
 *
 * @property string Status
 * @property string Title
 * @property float  Total
 * @property string FirstName
 * @property string Surname
 * @property string Email
 * @property string Comments
 * @property string ReservationCode
 *
 * @property int    EventID
 * @property int    TicketFileID
 *
 * @method File TicketFile
 * @method CalendarEvent|TicketExtension Event
 * @method HasManyList Attendees
 * @method ManyManyList PriceModifiers
 */
class Reservation extends DataObject
{
    private static $db = array(
        'Status' => 'Enum("CART,PENDING,PAID,CANCELED","CART")', // State 'CANCELED' is more for administrative purposes
        'Title' => 'Varchar(255)',
        'Total' => 'Currency',
        'FirstName' => 'Varchar(255)',
        'Surname' => 'Varchar(255)',
        'Email' => 'Varchar(255)',
        'Comments' => 'Text',
        'ReservationCode' => 'Varchar(255)'
    );

    private static $default_sort = 'PlacedDate DESC';

    private static $has_one = array(
        'Event' => 'CalendarEvent',
        'TicketFile' => 'File'
    );

    private static $has_many = array(
        'Attendees' => 'Broarm\EventTickets\Attendee.Reservation'
    );

    private static $many_many = array(
        'PriceModifiers' => 'Broarm\EventTickets\PriceModifier'
    );

    private static $summary_fields = array(
        'Title' => 'Customer',
        'Total.Nice' => 'Total',
        'State' => 'Status'
    );

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root', $mainTab = new Tab('Main')));

        $fields->addFieldsToTab('Root.Main', array());
        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    public function onBeforeWrite()
    {
        // Set the title to the name of the reservation holder
        $this->Title = $this->getName();

        // Create a validation code to be used for confirmation and in the barcode
        if ($this->exists() && empty($this->ReservationCode)) {
            $this->ReservationCode = $this->createReservationCode();
        }

        parent::onBeforeWrite();
    }

    public function onBeforeDelete()
    {
        // If a reservation is deleted remove the names from the guest list
        foreach ($this->Attendees() as $attendee) {
            /** @var Attendee $attendee */
            if ($attendee->exists()) {
                $attendee->delete();
            }
        }

        // Make sure the ticket file is not downloadable
        if ($this->TicketFile()->exists()) {
            $this->TicketFile()->delete();
        }
        // Remove the folder
        if ($this->fileFolder()->exists()) {
            $this->fileFolder()->delete();
        }

        parent::onBeforeDelete();
    }

    public function singular_name()
    {
        $name = explode('\\', parent::singular_name());
        return trim(end($name));
    }

    /**
     * Get the full name
     *
     * @return string
     */
    public function getName()
    {
        /** @var Attendee $attendee */
        if ($this->Attendees()->exists() && $attendee = $this->Attendees()->first()) {
            $attendee = $this->Attendees()->first();
            return $attendee->getName();
        } else {
            return 'new reservation';
        }
    }

    /**
     * Return the translated state
     * @return string
     */
    public function getState()
    {
        return _t("Reservation.{$this->Status}", $this->Status);
    }

    /**
     * Get the total by querying the sum of attendee ticket prices
     *
     * @return float
     */
    public function calculateTotal()
    {
        $total = $this->Attendees()->leftJoin(
            'Broarm\EventTickets\Ticket',
            '`Broarm\EventTickets\Attendee`.`TicketID` = `Broarm\EventTickets\Ticket`.`ID`'
        )->sum('Price');

        // Calculate any price modifications if added
        if ($this->PriceModifiers()->exists()) {
            foreach ($this->PriceModifiers() as $priceModifier) {
                $priceModifier->updateTotal($total);
            }
        }

        return $this->Total = $total;
    }

    /**
     * Safely change to a state
     *
     * @param $state
     */
    public function changeState($state)
    {
        $availableStates = $this->dbObject('Status')->enumValues();
        if (in_array($state, $availableStates)) {
            $this->Status = $state;
        } else {
            user_error(_t('Reservation.STATE_CHANGE_ERROR', 'Selected state is not available'));
        }
    }

    /**
     * Create a reservation code
     *
     * @return string
     */
    public function createReservationCode()
    {
        return uniqid($this->ID);
    }

    /**
     * Create the folder for the qr code and ticket file
     *
     * @return Folder|null
     */
    public function fileFolder()
    {
        return Folder::find_or_make("/event-tickets/{$this->ReservationCode}/");
    }

    /**
     * Generate the qr codes and downloadable pdf
     */
    public function createFiles()
    {
        $folder = $folder = $this->fileFolder();
        /** @var Attendee $attendee */
        foreach ($this->Attendees() as $attendee) {
            $attendee->createQRCode($folder);
        }

        // Create a pdf with the newly create QR codes
        $this->createTicketFile($folder);
    }

    /**
     * Create a Printable ticket file
     *
     * @param Folder $folder
     *
     * @return File
     */
    private function createTicketFile(Folder $folder)
    {
        // Find or make a folder
        $relativeFilePath = "/{$folder->Filename}{$this->ReservationCode}.pdf";
        $absoluteFilePath = Director::baseFolder() . $relativeFilePath;

        if (!$file = File::get()->find('Filename', $relativeFilePath)) {
            $file = File::create();
            $file->ParentID = $folder->ID;
            $file->OwnerID = (Member::currentUser()) ? Member::currentUser()->ID : 0;
            $file->Title = $this->ReservationCode;
            $file->setFilename($relativeFilePath);
            $file->write();

            // Attach the ticket file to the Attendee
            $this->TicketFileID = $file->ID;
            $this->write();
        }

        // Set the template and parse the data
        $template = new SSViewer('PrintableTicket');
        $html = $template->process($this->getViewableData());

        // Create a DomPDF instance
        $domPDF = new Dompdf();
        $domPDF->loadHtml($html);
        $domPDF->setPaper('A4');
        $domPDF->getOptions()->setDpi(250);
        $domPDF->render();

        // Save the pdf stream as a file
        file_put_contents($absoluteFilePath, $domPDF->output());

        return $file;
    }

    /**
     * Get a viewable data object for this reservation
     * For use in the Email and print template
     *
     * @return ViewableData
     */
    public function getViewableData()
    {
        $data = $this->Me();
        $calendarController = new CalendarEvent_Controller($this->Event());
        $data->CurrentDate = $calendarController->CurrentDate();
        $this->extend('updateViewableData', $data);
        return $data;
    }

    public function canView($member = null)
    {
        return $this->Event()->canView($member);
    }

    public function canEdit($member = null)
    {
        return $this->Event()->canEdit($member);
    }

    public function canDelete($member = null)
    {
        return $this->Event()->canDelete($member);
    }

    public function canCreate($member = null)
    {
        return $this->Event()->canCreate($member);
    }
}
