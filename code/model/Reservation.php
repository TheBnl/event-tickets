<?php
/**
 * Reservation.php
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
use GridField;
use GridFieldConfig_RecordViewer;
use HasManyList;
use ManyManyList;
use Member;
use ReadonlyField;
use SiteConfig;
use SSViewer;
use Tab;
use TabSet;
use ViewableData;

/**
 * Class Reservation
 *
 * @package Broarm\EventTickets
 *
 * @property string Status
 * @property string Title
 * @property float  Subtotal
 * @property float  Total
 * @property string Email todo determine obsolete value
 * @property string Comments
 * @property string ReservationCode
 * @property string Gateway
 *
 * @property int    EventID
 * @property int    MainContactID
 *
 * @method CalendarEvent|TicketExtension Event()
 * @method Attendee MainContact()
 * @method HasManyList Payments()
 * @method HasManyList Attendees()
 * @method ManyManyList PriceModifiers()
 */
class Reservation extends DataObject
{
    /**
     * Time to wait before deleting the discarded cart
     * Give a string that is parsable by strtotime
     *
     * @var string
     */
    private static $delete_after = '+1 week';

    private static $db = array(
        'Status' => 'Enum("CART,PENDING,PAID,CANCELED","CART")',
        'Title' => 'Varchar(255)',
        'Subtotal' => 'Currency',
        'Total' => 'Currency',
        'Email' => 'Varchar(255)',
        'Gateway' => 'Varchar(255)',
        'Comments' => 'Text',
        'ReservationCode' => 'Varchar(255)'
    );

    private static $has_one = array(
        'Event' => 'CalendarEvent',
        //'TicketFile' => 'File',
        'MainContact' => 'Broarm\EventTickets\Attendee'
    );

    private static $has_many = array(
        'Payments' => 'Payment',
        'Attendees' => 'Broarm\EventTickets\Attendee.Reservation'
    );

    private static $belongs_many_many = array(
        'PriceModifiers' => 'Broarm\EventTickets\PriceModifier'
    );

    private static $indexes = array(
        'ReservationCode' => 'unique("ReservationCode")'
    );

    private static $summary_fields = array(
        'Title' => 'Customer',
        'Total.Nice' => 'Total',
        'State' => 'Status'
    );

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root', $mainTab = new Tab('Main')));
        $gridFieldConfig = GridFieldConfig_RecordViewer::create();
        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('ReservationCode', _t('Reservation.Code', 'Code')),
            ReadonlyField::create('Title', _t('Reservation.MainContact', 'Main contact')),
            ReadonlyField::create('Gateway', _t('Reservation.Gateway', 'Gateway')),
            ReadonlyField::create('Total', _t('Reservation.Total', 'Total')),
            ReadonlyField::create('Comments', _t('Reservation.Comments', 'Comments')),
            $reservationFileField = ReadonlyField::create(
                'ReservationFile',
                _t('Attendee.Reservation', 'Reservation'),
                "<a class='readonly' href='{$this->TicketFile()->Link()}' target='_blank'>Download reservation PDF</a>"
            ),
            GridField::create('Attendees', 'Attendees', $this->Attendees(), $gridFieldConfig),
            GridField::create('Payments', 'Payments', $this->Payments(), $gridFieldConfig)
        ));
        $reservationFileField->dontEscape = true;
        $fields->addFieldsToTab('Root.Main', array());
        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    /**
     * @deprecated
     * @return mixed
     */
    public function TicketFile()
    {
        return $this->Attendees()->first()->TicketFile();
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
        //if ($this->TicketFile()->exists()) {
        //    $this->TicketFile()->delete();
        //}

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
     * Check if the cart is still in cart state and the delete_after time period has been exceeded
     *
     * @return bool
     */
    public function isDiscarded()
    {
        $deleteAfter = strtotime(self::config()->get('delete_after'), strtotime($this->Created));
        return ($this->Status === 'CART') && (time() > $deleteAfter);
    }

    /**
     * Get the full name
     *
     * @return string
     */
    public function getName()
    {
        /** @var Attendee $attendee */
        if ($this->MainContact()->exists()) {
            return $this->MainContact()->getName();
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
        $total = $this->Subtotal = $this->Attendees()->leftJoin(
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
     * Set the main contact id
     *
     * @param $id
     */
    public function setMainContact($id) {
        $this->MainContactID = $id;
        $this->write();
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
     * @return Folder|DataObject|null
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
            $attendee->createTicketFile($folder);
        }
    }

    /**
     * Create a Printable ticket file
     *
     * @param Folder $folder
     *
     * @return File
     * /
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

        // Attach the ticket file to the Attendee
        $this->TicketFileID = $file->ID;
        $this->write();

        return $file;
    }*/

    /**
     * Get a viewable data object for this reservation
     * For use in the Email and print template
     * @deprecated
     * @return ViewableData
     * /
    public function getViewableData()
    {
        $config = SiteConfig::current_site_config();
        $data = $this->Me();
        $data->CurrentDate = $this->Event()->getController()->CurrentDate();
        $data->Logo = $config->TicketLogo();
        $this->extend('updateViewableData', $data);
        return $data;
    }*/

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
