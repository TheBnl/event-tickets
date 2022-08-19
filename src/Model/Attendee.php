<?php

namespace Broarm\EventTickets\Model;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Broarm\EventTickets\Extensions\TicketExtension;
use Broarm\EventTickets\Forms\CheckInValidator;
use Broarm\EventTickets\Model\UserFields\UserEmailField;
use Broarm\EventTickets\Model\UserFields\UserField;
use Broarm\EventTickets\Model\UserFields\UserTextField;
use Dompdf\Dompdf;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

/**
 * Class Attendee
 * @package Broarm\EventTickets
 *
 * @property string Title
 * @property string  TicketCode
 * @property boolean TicketReceiver
 * @property boolean CheckedIn
 * @property FieldList SavableFields    Field to be set in AttendeesField
 *
 * @property int TicketID
 * @property int TicketQRCodeID
 * @property int TicketFileID
 * @property int ReservationID
 * @property int TicketPageID
 * @property int MemberID
 *
 * @method Reservation Reservation()
 * @method Ticket Ticket()
 * @method Member Member()
 * @method TicketExtension|SiteTree TicketPage()
 * @method ManyManyList Fields()
 */
class Attendee extends DataObject
{
    private static $table_name = 'EventTickets_Attendee';

    /**
     * Set this to true when you want to have a QR code that opens the check in page and validates the code.
     * The validation is only done with proper authorisation so guest cannot check themselves in by mistake.
     * By default only the ticket number is translated to an QR code. (for use with USB QR scanners)
     *
     * @var bool
     */
    private static $qr_as_link = false;

    private static $default_fields = array(
        'FirstName' => array(
            'Title' => 'First name',
            'FieldType' => UserTextField::class,
            'Required' => true,
            'Editable' => false
        ),
        'Surname' => array(
            'Title' => 'Surname',
            'FieldType' => UserTextField::class,
            'Required' => true,
            'Editable' => false
        ),
        'Email' => array(
            'Title' => 'Email',
            'FieldType' => UserEmailField::class,
            'Required' => true,
            'Editable' => false
        )
    );

    private static $table_fields = array(
        'Title',
        'Email'
    );

    private static $db = array(
        'Title' => 'Varchar',
        'TicketReceiver' => 'Boolean',
        'TicketCode' => 'Varchar',
        'CheckedIn' => 'Boolean'
    );

    private static $default_sort = 'Created DESC';

    private static $indexes = array(
        'TicketCode' => [
            'type' => 'unique',
            'columns' => ['TicketCode']
        ]
    );

    private static $has_one = array(
        'TicketPage' => SiteTree::class,
        'Reservation' => Reservation::class,
        'Ticket' => Ticket::class,
        'Member' => Member::class
    );

    private static $many_many = array(
        'Fields' => UserField::class
    );

    private static $many_many_extraFields = array(
        'Fields' => array(
            'Value' => 'Varchar'
        )
    );

    private static $summary_fields = array(
        'Title' => 'Name',
        'Ticket.Title' => 'Ticket',
        'TicketCode' => 'Ticket #',
        'CheckedIn.Nice' => 'Checked in',
    );

    protected static $cachedFields = array();

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('TicketCode', _t(__CLASS__ . '.Ticket', 'Ticket')),
            ReadonlyField::create('MyCheckedIn', _t(__CLASS__ . '.CheckedIn', 'Checked in'), $this->dbObject('CheckedIn')->Nice())
        ));

        foreach ($this->Fields() as $field) {
            $fieldType = $field->getFieldType();
            $fields->addFieldToTab(
                'Root.Main',
                $fieldType::create("{$field->Name}[$field->ID]", $field->Title, $field->getValue())
            );
        }

        return $fields;
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

        if ($fields = $this->Fields()) {
            foreach ($fields as $field) {
                if ($value = $this->{"$field->Name[$field->ID]"}) {
                    $fields->add($field->ID, array('Value' => $value));
                }
            }
        }

        parent::onBeforeWrite();
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if (($ticketPage = $this->TicketPage()) && $ticketPage->exists() && !$this->Fields()->exists()) {
            $this->Fields()->addMany($ticketPage->Fields()->column());
        }
    }

    /**
     * Delete any stray files before deleting the object
     */
    public function onBeforeDelete()
    {
        if ($this->Fields()->exists()) {
            $this->Fields()->removeAll();
        }

        parent::onBeforeDelete();
    }

    /**
     * Check if the attendee has all required fields set
     *
     * @return bool
     */
    public function isValid()
    {
        $fields = self::config()->get('default_fields');
        $requiredFields = array_filter($fields, function ($field) {
            return $field['Required'];
        });
        
        $valid = true;
        foreach ($requiredFields as $requiredField => $requiredFieldConfig) {
            if ($valid) {
                $valid = !empty($this->getUserField($requiredField));
            }
        }

        return $valid;
    }

    /**
     * Create the folder for the qr code and ticket file
     *
     * @return Folder|DataObject|null
     * @deprecated dont store files, generate files when needed
     */
    public function fileFolder()
    {
        return Folder::find_or_make("/event-tickets/{$this->TicketPage()->URLSegment}/{$this->TicketCode}/");
    }

    /**
     * Utility method for fetching the default field, FirstName, value
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getUserField('FirstName');
    }

    /**
     * Utility method for fetching the default field, Surname, value
     *
     * @return string|null
     */
    public function getSurname()
    {
        return $this->getUserField('Surname');
    }

    /**
     * Utility method for fetching the default field, Email, value
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getUserField('Email');
    }

    /**
     * Get the combined first and last nave for display on the ticket and attendee list
     *
     * @return string|null
     */
    public function getName()
    {
        $name = null;
        $mainContact = $this->Reservation()->MainContact();
        if ($this->getSurname()) {
            $name = trim("{$this->getFirstName()} {$this->getSurname()}");
        } elseif ($mainContact->exists() && $mainContact->getSurname()) {
            $name = _t(__CLASS__ . '.GuestOf', 'Guest of {name}', null, array('name' => $mainContact->getName()));
        }

        $this->extend('updateName', $name);
        return $name;
    }

    /**
     * Get the user field and store it in a static cache
     * todo: add a cache that saves the field value on save and retrieves the values here, dumb, so empty fields don't trigger queries
     *
     * @param $field
     * @return mixed|null|string
     */
    public function getUserField($field)
    {
        if (isset(self::$cachedFields[$this->ID][$field])) {
            return self::$cachedFields[$this->ID][$field];
        } elseif ($userField = $this->Fields()->find('Name', $field)) {
            return self::$cachedFields[$this->ID][$field] = (string)$userField->getField('Value');
        }

        return null;
    }

    public function getIsMainContact()
    {
        return $this->ID === $this->Reservation()->MainContactID;
    }

    protected function getFieldCacheKey($field)
    {
        return md5(serialize(array($this->ID, $field)));
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
            $data = new ArrayData([
                'Header' => _t(__CLASS__ . ".$field", $field),
                'Value' => $this->{$field}
            ]);

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
     * Get a base64 encoded QR png code
     *
     * @return string
     */
    public function getQRCode()
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd()
        );

        $writer = new Writer($renderer);
        return base64_encode($writer->writeString($this->TicketCode));
    }

    /**
     * Get the checkin link
     *
     * @return string
     */
    public function getCheckInLink()
    {
        return Director::absoluteURL("checkin/ticket/{$this->TicketCode}");
    }

    /**
     * Check the attendee out
     * @throws ValidationException
     */
    public function checkIn()
    {
        $this->CheckedIn = true;
        $this->write();
    }

    public function canCheckOut()
    {
        return CheckInValidator::config()->get('allow_checkout');
    }

    /**
     * Check the attendee in
     */
    public function checkOut()
    {
        if ($this->canCheckOut()) {
            $this->CheckedIn = false;
            $this->write();
        }
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

    public function canCreate($member = null, $context = [])
    {
        return $this->Reservation()->canCreate($member, $context);
    }
}
