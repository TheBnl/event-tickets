<?php
/**
 * Attendee.php
 *
 * @author Bram de Leeuw
 * Date: 09/03/17
 */

namespace Broarm\EventTickets;

use ArrayList;
use BaconQrCode;
use CalendarEvent;
use DataObject;
use Director;
use Dompdf\Dompdf;
use FieldList;
use File;
use Folder;
use Image;
use LiteralField;
use ManyManyList;
use Member;
use ReadonlyField;
use SSViewer;
use Tab;
use TabSet;
use TextField;
use ViewableData;

/**
 * Class Attendee
 *
 * @package Broarm\EventTickets
 *
 * @property string    Title
 * @property string    FirstName
 * @property string    Surname
 * @property string    Email
 * @property string    TicketCode
 * @property boolean   TicketReceiver
 * @property boolean   CheckedIn
 * @property FieldList SavableFields    Field to be set in AttendeesField
 *
 * @property int       TicketID
 * @property int       TicketQRCodeID
 * @property int       TicketFileID
 * @property int       ReservationID
 * @property int       EventID
 * @property int       MemberID
 *
 * @method Reservation Reservation()
 * @method Ticket Ticket()
 * @method Image TicketQRCode()
 * @method File TicketFile()
 * @method Member Member()
 * @method CalendarEvent|TicketExtension Event()
 * @method ManyManyList Fields()
 */
class Attendee extends DataObject
{
    /**
     * Set this to true when you want to have a QR code that opens the check in page and validates the code.
     * The validation is only done with proper authorisation so guest cannot check themselves in by mistake.
     * By default only the ticket number is translated to an QR code. (for use with USB QR scanners)
     *
     * @var bool
     */
    private static $qr_as_link = false;

    private static $default_fields = array(
        'FirstName' => 'TextField',
        'Surname' => 'TextField',
        'Email' => 'EmailField',
    );

    private static $table_fields = array(
        'Title',
        'Email'
    );

    private static $db = array(
        'Title' => 'Varchar(255)',
        'TicketReceiver' => 'Boolean',
        'TicketCode' => 'Varchar(255)',
        'CheckedIn' => 'Boolean'
    );

    private static $indexes = array(
        'TicketCode' => 'unique("TicketCode")'
    );

    private static $has_one = array(
        'Reservation' => 'Broarm\EventTickets\Reservation',
        'Ticket' => 'Broarm\EventTickets\Ticket',
        'Event' => 'CalendarEvent',
        'Member' => 'Member',
        'TicketQRCode' => 'Image',
        'TicketFile' => 'File'
    );

    private static $many_many = array(
        'Fields' => 'Broarm\EventTickets\AttendeeExtraField'
    );

    private static $many_many_extraFields = array(
        'Fields' => array(
            'Value' => 'Varchar(255)'
        )
    );

    private static $summary_fields = array(
        'Title' => 'Name',
        'Email' => 'Email',
        'Ticket.Title' => 'Ticket',
        'TicketCode' => 'Ticket #',
        'CheckedInSummary' => 'Checked in',
    );

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root', $mainTab = new Tab('Main')));

        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('TicketCode', _t('Attendee.Ticket', 'Ticket')),
            ReadonlyField::create('MyCheckedIn', _t('Attendee.CheckedIn', 'Checked in'), $this->dbObject('CheckedIn')->Nice())
        ));

        foreach ($this->Fields() as $field) {
            $fields->addFieldToTab(
                'Root.Main',
                ReadonlyField::create("{$field->FieldName}_Preview", $field->Title, $field->Value)
            );
        }

        if ($this->TicketFile()->exists()) {
            $fields->addFieldToTab('Root.Main', $reservationFileField = ReadonlyField::create(
                'ReservationFile',
                _t('Attendee.Reservation', 'Reservation'),
                "<a class='readonly' href='{$this->TicketFile()->Link()}' target='_blank'>Download reservation PDF</a>"
            ));
            $reservationFileField->dontEscape = true;
        }

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    /**
     * Utility method for fetching the default field, FirstName, value
     *
     * @return string
     */
    public function getFirstName()
    {
        if ($firstName = $this->Fields()->find('FieldName', 'FirstName')) {
            return (string)$firstName->getField('Value');
        }

        return null;
    }

    /**
     * Utility method for fetching the default field, Surname, value
     *
     * @return string
     */
    public function getSurname()
    {
        if ($surname = $this->Fields()->find('FieldName', 'Surname')) {
            return (string)$surname->getField('Value');
        }

        return null;
    }

    /**
     * Get the combined first and last nave for dispay on the ticket and attendee list
     *
     * @return string
     */
    public function getName()
    {
        if (!empty($this->getFirstName())) {
            return trim("{$this->getFirstName()} {$this->getSurname()}");
        } elseif ($this->Reservation()->MainContact()->exists() && $mainContact = $this->Reservation()->MainContact()) {
            return _t('Attendee.GUEST_OF', 'Guest of {name}', null, array('name' => $mainContact->getName()));
        } else {
            return null;
        }
    }

    /**
     * Utility method for fetching the default field, Email, value
     *
     * @return string
     */
    public function getEmail()
    {
        if ($email = $this->Fields()->find('FieldName', 'Email')) {
            return (string)$email->getField('Value');
        }

        return null;
    }

    /**
     * Set the title and ticket code before writing
     */
    public function onBeforeWrite()
    {
        // Set the title of the attendee
        $this->Title = $this->getName();

        // Generate the ticket code
        if ($this->exists() && empty($this->TicketCode)) {
            $this->TicketCode = $this->generateTicketCode();
        }

        parent::onBeforeWrite();
    }

    /**
     * Delete any stray files before deleting the object
     */
    public function onBeforeDelete()
    {
        // If an attendee is deleted from the guest list remove it's qr code
        // after deleting the code it's not validatable anymore, simply here for cleanup
        if ($this->TicketQRCode()->exists()) {
            $this->TicketQRCode()->delete();
        }

        // cleanup the ticket file
        if ($this->TicketFile()->exists()) {
            $this->TicketFile()->delete();
        }

        parent::onBeforeDelete();
    }

    /**
     * Get the table fields for this attendee
     *
     * @return ArrayList
     */
    public function getTableFields()
    {
        $fields = new ArrayList();
        foreach (self::config()->get('table_fields') as $field) {
            $data = new ViewableData();
            $data->Header = _t("Attendee.$field", $field);
            $data->Value = $this->$field;
            $fields->add($data);
        }
        return $fields;
    }

    /**
     * Get the unnamespaced singular name for display in the CMS
     *
     * @return string
     */
    public function singular_name()
    {
        $name = explode('\\', parent::singular_name());
        return trim(end($name));
    }

    /**
     * Return the checked in state for use in grid fields
     *
     * @return LiteralField
     */
    public function getCheckedInSummary()
    {
        $checkedInNice = $this->dbObject('CheckedIn')->Nice();
        $checkedIn = $this->CheckedIn
            ? "<span style='color: #3adb76;'>$checkedInNice</span>"
            : "<span style='color: #cc4b37;'>$checkedInNice</span>";

        return new LiteralField('CheckedIn', $checkedIn);
    }

    /**
     * Generate a unique ticket id
     * Serves as the base for the QR code and ticket file
     *
     * @return string
     */
    public function generateTicketCode()
    {
        return uniqid($this->ID);
    }

    /**
     * Create a QRCode for the attendee based on the Ticket code
     *
     * @param Folder $folder
     *
     * @return Image
     */
    public function createQRCode(Folder $folder)
    {
        $relativeFilePath = "/{$folder->Filename}{$this->TicketCode}.png";
        $absoluteFilePath = Director::baseFolder() . $relativeFilePath;

        if (!$image = Image::get()->find('Filename', $relativeFilePath)) {
            // Generate the QR code
            $renderer = new BaconQrCode\Renderer\Image\Png();
            $renderer->setHeight(256);
            $renderer->setWidth(256);
            $writer = new BaconQrCode\Writer($renderer);
            if (self::config()->get('qr_as_link')) {
                $writer->writeFile($this->Event()->AbsoluteLink("checkin/$this->TicketCode"), $absoluteFilePath);
            } else {
                $writer->writeFile($this->TicketCode, $absoluteFilePath);
            }

            // Store the image in an image object
            $image = Image::create();
            $image->ParentID = $folder->ID;
            $image->OwnerID = (Member::currentUser()) ? Member::currentUser()->ID : 0;
            $image->Title = $this->TicketCode;
            $image->setFilename($relativeFilePath);
            $image->write();

            // Attach the QR code to the Attendee
            $this->TicketQRCodeID = $image->ID;
            $this->write();
        }

        return $image;
    }

    /**
     * Creates a printable ticket for the attendee
     *
     * @param Folder $folder
     *
     * @return File
     */
    public function createTicketFile(Folder $folder)
    {
        // Find or make a folder
        $relativeFilePath = "/{$folder->Filename}{$this->TicketCode}.pdf";
        $absoluteFilePath = Director::baseFolder() . $relativeFilePath;

        if (!$file = File::get()->find('Filename', $relativeFilePath)) {
            $file = File::create();
            $file->ParentID = $folder->ID;
            $file->OwnerID = (Member::currentUser()) ? Member::currentUser()->ID : 0;
            $file->Title = $this->TicketCode;
            $file->setFilename($relativeFilePath);
            $file->write();
        }

        // Set the template and parse the data
        $template = new SSViewer('PrintableTicket');
        $html = $template->process($this->data());// getViewableData());

        // Create a DomPDF instance
        $domPDF = new Dompdf();
        $domPDF->loadHtml($html);
        $domPDF->setPaper('A4');
        $domPDF->getOptions()->setDpi(150);
        $domPDF->render();

        // Save the pdf stream as a file
        file_put_contents($absoluteFilePath, $domPDF->output());

        // Attach the ticket file to the Attendee
        $this->TicketFileID = $file->ID;
        $this->write();

        return $file;
    }

    public function canView($member = null)
    {
        return $this->Reservation()->canView($member);
    }

    public function canEdit($member = null)
    {
        return $this->Reservation()->canEdit($member);
    }

    public function canDelete($member = null)
    {
        return $this->Reservation()->canDelete($member);
    }

    public function canCreate($member = null)
    {
        return $this->Reservation()->canCreate($member);
    }
}
