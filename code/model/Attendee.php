<?php
/**
 * Attendee.php
 *
 * @author Bram de Leeuw
 * Date: 09/03/17
 */

namespace Broarm\EventTickets;

use ArrayList;
use CalendarEvent;
use CalendarEvent_Controller;
use DataObject;
use Director;
use Dompdf\Dompdf;
use FieldList;
use File;
use Folder;
use Image;
use LiteralField;
use Member;
use ReadonlyField;
use SSViewer;
use Tab;
use TabSet;
use BaconQrCode;
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
 * @property int       ReservationID
 * @property int       EventID
 * @property int       MemberID
 *
 * @method Reservation Reservation
 * @method Ticket Ticket
 * @method Image TicketQRCode
 * @method Member Member
 * @method CalendarEvent|TicketExtension Event
 */
class Attendee extends DataObject
{
    private static $savable_fields = array(
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
        'FirstName' => 'Varchar(255)',
        'Surname' => 'Varchar(255)',
        'Email' => 'Varchar(255)',
        'TicketReceiver' => 'Boolean',
        'TicketCode' => 'Varchar(255)',
        'CheckedIn' => 'Boolean'
    );

    private static $default_sort = 'FirstName ASC, Surname ASC';

    private static $indexes = array(
        'TicketCode' => 'unique("TicketCode")'
    );

    private static $has_one = array(
        'Reservation' => 'Broarm\EventTickets\Reservation',
        'Ticket' => 'Broarm\EventTickets\Ticket',
        'Event' => 'CalendarEvent',
        'Member' => 'Member',
        'TicketQRCode' => 'Image'
    );

    private static $summary_fields = array(
        'Title' => 'Name',
        'Email' => 'Email',
        'TicketCode' => 'Ticket',
        'CheckedInSummary' => 'Checked in',
    );

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root', $mainTab = new Tab('Main')));
        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('Title', 'Name'),
            ReadonlyField::create('Email', 'Email'),
            ReadonlyField::create('TicketCode', 'Ticket'),
            LiteralField::create('CheckedIn', "
                <div class='field readonly'>
                    <label class='left'>Checked in</label>
                    <div class='middleColumn'><span class='readonly'>{$this->dbObject('CheckedIn')->Nice()}</span></div>
                </div>
            "),
            LiteralField::create('ReservationFile', "
                <div class='field readonly'>
                    <label class='left'>Reservation</label>
                    <div class='middleColumn'>
                        <span class='readonly'><a class='readonly' href='{$this->Reservation()->TicketFile()->Link()}' target='_blank'>Download reservation PDF</a></span>
                    </div>
                </div>
            ")
        ));
        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

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

    public function onBeforeDelete()
    {
        // If an attendee is deleted from the guest list remove it's qr code
        // after deleting the code it's not validatable anymore, simply here for cleanup
        if ($this->TicketQRCode()->exists()) {
            $this->TicketQRCode()->delete();
        }

        parent::onBeforeDelete();
    }

    public function getTableFields()
    {
        $fields = new ArrayList();
        foreach (self::config()->get('table_fields') as $field) {
            $data = new ViewableData();
            $data->Header = _t("Attendee.$field", $field);
            $data->Value = $this->getField($field);
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
     * Get the combined first and last nave for dispay on the ticket and attendee list
     *
     * @return string
     */
    public function getName()
    {
        return trim("$this->FirstName $this->Surname");
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
            $writer->writeFile($this->TicketCode, $absoluteFilePath);

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
