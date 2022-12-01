<?php

namespace Broarm\EventTickets\Extensions;

use Broarm\EventTickets\Controllers\CheckInController;
use Broarm\EventTickets\Controllers\GuestListImportController;
use Broarm\EventTickets\Forms\GridField\GuestListGridFieldConfig;
use Broarm\EventTickets\Forms\GridField\ReservationGridFieldConfig;
use Broarm\EventTickets\Forms\GridField\TicketsGridFieldConfig;
use Broarm\EventTickets\Forms\GridField\UserFieldsGridFieldConfig;
use Broarm\EventTickets\Forms\GridField\WaitingListGridFieldConfig;
use Broarm\EventTickets\Model\Attendee;
use Broarm\EventTickets\Model\Reservation;
use Broarm\EventTickets\Model\Ticket;
use Broarm\EventTickets\Model\Buyable;
use Broarm\EventTickets\Model\UserFields\UserField;
use Broarm\EventTickets\Model\WaitingListRegistration;
use Exception;
use LeKoala\CmsActions\CustomAction;
use LeKoala\CmsActions\CustomLink;
use LeKoala\CmsActions\SilverStripeIcons;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class TicketExtension
 *
 * @package Broarm\EventTickets
 *
 * @property TicketExtension|SiteTree $owner
 * @property string SuccessMessage
 * @property string SuccessMessageMail
 * @property string PrintedTicketContent
 *
 * @method HasManyList Tickets()
 * @method HasManyList Reservations()
 * @method HasManyList Attendees()
 * @method HasManyList WaitingList()
 * @method ManyManyList Fields()
 */
class TicketExtension extends DataExtension
{
    protected $controller;

    private static $db = [
        'MaxCapacity' => 'Int',
        'SuccessMessage' => 'HTMLText',
        'SuccessMessageMail' => 'HTMLText',
        'PrintedTicketContent' => 'HTMLText',
    ];

    private static $has_many = [
        'Tickets' => Buyable::class . '.TicketPage', // rename to buyables ?
        'Reservations' => Reservation::class . '.TicketPage',
        'Attendees' => Attendee::class . '.TicketPage',
        'WaitingList' => WaitingListRegistration::class . '.TicketPage'
    ];

    private static $many_many = [
        'Fields' => UserField::class
    ];

    private static $many_many_extraFields = [
        'Fields' => [
            'Sort' => 'Int'
        ]
    ];

    private static $summary_fields = [
        'Title',
        'GuestListStatus',
        'StartDate'
    ];

    protected $cachedGuestList;

    public function updateCMSFields(FieldList $fields)
    {
        $guestListStatusDescription = _t(__CLASS__ . '.GuestListStatusDescription', 'Tickets sold: {guestListStatus}', null, [
            'guestListStatus' => $this->owner->getGuestListStatus()
        ]);

        $fields->addFieldsToTab('Root.Main', [
            LiteralField::create('GuestListStatus', "<p class='message notice'>{$guestListStatusDescription}</p>")
        ], 'Title');

        $ticketLabel = _t(__CLASS__ . '.Tickets', 'Tickets');
        $fields->addFieldsToTab(
            "Root.$ticketLabel", array(
            GridField::create('Tickets', $ticketLabel, $this->owner->Tickets(), TicketsGridFieldConfig::create()),
            NumericField::create('MaxCapacity', _t(__CLASS__ . '.MaxCapacity', 'Maximaal beschikbare plaatsen'))
                ->setDescription(_t(
                    __CLASS__ . 'MaxCapacityDescription', 
                    'Wanneer dit veld leeg is wordt de som van de beschikbare ticket capaciteit gebruikt: {sum}',
                    null,
                    ['sum' => $this->owner->getTicketCapacity()]
                )),
            HtmlEditorField::create('SuccessMessage', _t(__CLASS__ . '.SuccessMessage', 'Success message'))->addExtraClass('stacked')->setRows(4),
            HtmlEditorField::create('SuccessMessageMail', _t(__CLASS__ . '.MailMessage', 'Mail message'))->addExtraClass('stacked')->setRows(4),
            HtmlEditorField::create('PrintedTicketContent', _t(__CLASS__ . '.PrintedTicketContent', 'Ticket description'))->addExtraClass('stacked')->setRows(4)
        ));

        // Create Reservations tab
        $reservationLabel = _t(__CLASS__ . '.Reservations', 'Reservations');
        
        // hide carts in CMS
        $reservations = $this->owner->Reservations()->exclude([
            'Status' => Reservation::STATUS_CART
        ]);

        $fields->addFieldToTab(
            "Root.$reservationLabel",
            GridField::create('Reservations', $reservationLabel, $reservations, ReservationGridFieldConfig::create())
        );
        
        // Create Attendees tab
        $guestListConfig = GuestListGridFieldConfig::create();
        $guestListConfig->addComponent(
            $importButton = new GridFieldImportButton('buttons-before-left'),
        );
        $importButton->setModalTitle(_t(__CLASS__ . '.ImportGuestList', 'Import guestlist'));
        $importButton->setImportForm(
            GuestListImportController::singleton()->GuestListUploadForm($this->owner->ID)
        );

        $guestListLabel = _t(__CLASS__ . '.GuestList', 'GuestList');
        
        // hide carts in CMS
        $guestList = $this->owner->Attendees()->exclude([
            'Reservation.Status' => Reservation::STATUS_CART
        ]);

        $fields->addFieldToTab(
            "Root.$guestListLabel",
            GridField::create('Attendees', $guestListLabel, $guestList, $guestListConfig)
        );

        // Create WaitingList tab
        $waitingList = $this->owner->WaitingList();
        if ($this->owner->exists() && $waitingList->exists()) {
            $waitingListLabel = _t(__CLASS__ . '.WaitingList', 'WaitingList');
            $fields->addFieldToTab(
                "Root.$waitingListLabel",
                GridField::create('WaitingList', $waitingListLabel, $waitingList, WaitingListGridFieldConfig::create())
            );
        }

        // Create Fields tab
        $extraFieldsLabel = _t(__CLASS__ . '.ExtraFields', 'Attendee fields');
        $fields->addFieldToTab(
            "Root.$extraFieldsLabel",
            GridField::create('ExtraFields', $extraFieldsLabel, $this->owner->Fields(), UserFieldsGridFieldConfig::create([
                'ClassName' => function($record, $column, $grid) {
                    return new DropdownField($column, $column, UserField::availableFields());
                },
                'Title' => function($record, $column, $grid) {
                    return new TextField($column);
                },
                'RequiredNice' => 'Required field'
            ]))
        );

        $this->owner->extend('updateTicketExtensionFields', $fields);
    }

    /**
     * Trigger actions after write
     */
    public function onAfterWrite()
    {
        $this->createDefaultFields();
        parent::onAfterWrite();
    }

    /**
     * Creates and sets up the default fields
     */
    public function createDefaultFields()
    {
        $fields = Attendee::config()->get('default_fields');
        
        if (!$this->owner->Fields()->exists()) {
            $sort = 0;
            foreach ($fields as $fieldName => $config) {
                $field = UserField::createDefaultField($fieldName, $config);
                $this->owner->Fields()->add($field, ['Sort' => $sort]);
                $sort++;
            }
        }
    }

    /**
     * Extend the page actions with an start check in action
     *
     * @param FieldList $actions
     */
    public function updateCMSActions(FieldList $actions)
    {
        if ($this->owner->Attendees()->exists()) {
            $link = CheckInController::singleton()->Link("event/{$this->owner->ID}");
            $downloadTicket = new CustomLink('startCheckIn', _t(__CLASS__ . '.StartCheckIn', 'Start check in'));
            $downloadTicket->setLink($link);
            $downloadTicket->setButtonType('outline-secondary');
            $downloadTicket->setButtonIcon(SilverStripeIcons::ICON_CHECKLIST);
            $downloadTicket->setNewWindow(true);
            $actions->insertAfter('MajorActions', $downloadTicket);
        }
    }

    public function startCheckIn()
    {
        $link = CheckInController::singleton()->Link("event/{$this->owner->ID}");
        return Controller::curr()->redirect($link);
    }

    /**
     * Get the sum of ticket capacity
     */
    public function getCapacity()
    {
        if ($this->owner->MaxCapacity) {
            return $this->owner->MaxCapacity;
        }

        return $this->owner->getTicketCapacity();
    }

    public function getTicketCapacity()
    {
        $tickets = $this->owner->Tickets();
        return $tickets->exists() ? $tickets->sum('Capacity') : 0;
    }

    /**
     * Get the guest list status used in the summary fields
     */
    public function getGuestListStatus()
    {
        $guests = $this->owner->getGuestList()->count();
        $capacity = $this->owner->getCapacity();
        return "$guests/$capacity";
    }

    /**
     * Get the leftover capacity
     *
     * @return int
     */
    public function getAvailability()
    {
        return $this->owner->getCapacity() - $this->owner->getGuestList()->count();
    }

    /**
     * Check if the tickets are still available
     *
     * @return bool
     */
    public function getTicketsAvailable()
    {
        return $this->owner->getAvailability() > 0;
    }

    /**
     * Check if the tickets are sold out
     * @return bool
     */
    public function getTicketsSoldOut()
    {
        return $this->owner->getAvailability() <= 0;
    }

    /**
     * get The sale start date
     *
     * @return DBDate|DBField|null
     * @throws Exception
     */
    public function getTicketSaleStartDate()
    {
        $date = null;
        $saleStart = null;
        if (($tickets = $this->owner->Tickets())) {
            /** @var Ticket $ticket */
            foreach ($tickets as $ticket) {
                $date = $ticket->getAvailableFrom();
                if ($saleStart === null || $date && strtotime($date) < strtotime($saleStart)) {
                    $saleStart = $date;
                }
            }
        }

        return $saleStart;
    }

    /**
     * Check if the event is expired, either by unavailable tickets or because the date has passed
     *
     * @return bool
     * @throws Exception
     */
    public function getEventExpired()
    {
        $expired = false;
        if (($tickets = $this->owner->Tickets()) && $expired = $tickets->exists()) {
            /** @var Ticket $ticket */
            foreach ($tickets as $ticket) {
                $expired = (!$ticket->validateDate() && $expired);
            }
        }

        return $expired;
    }

    /**
     * Check if the ticket sale is still pending
     *
     * @return bool
     */
    public function getTicketSalePending()
    {
        return time() < strtotime($this->owner->getTicketSaleStartDate());
    }

    /**
     * Get only the attendees who are certain to attend
     * Also includes attendees without any reservation, these are manually added
     *
     * @return DataList
     */
    public function getGuestList()
    {
        $reservation = Reservation::singleton()->baseTable();
        $attendee = Attendee::singleton()->baseTable();
        return Attendee::get()
            ->leftJoin($reservation, "`$attendee`.`ReservationID` = `$reservation`.`ID`")
            ->filter(array(
                'TicketStatus' => Attendee::STATUS_ACTIVE,
                'TicketPageID' => $this->owner->ID
            ))
            ->filterAny(array(
                'ReservationID' => 0,
                'Status' => Reservation::STATUS_PAID
            ));
    }

    /**
     * Get the checked in count for display in templates
     *
     * @return string
     */
    public function getCheckedInCount()
    {
        $attendees = $this->getGuestList();
        $checkedIn = $attendees->filter('CheckedIn', true)->count();
        return "($checkedIn/{$attendees->count()})";
    }

    /**
     * Get the success message
     *
     * @return mixed|string
     */
    public function getSuccessContent()
    {
        if (!empty($this->owner->SuccessMessage)) {
            return $this->owner->dbObject('SuccessMessage');
        } else {
            return SiteConfig::current_site_config()->dbObject('SuccessMessage');
        }
    }

    /**
     * Get the mail message
     *
     * @return mixed|string
     */
    public function getMailContent()
    {
        if (!empty($this->owner->SuccessMessageMail)) {
            return $this->owner->dbObject('SuccessMessageMail');
        } else {
            return SiteConfig::current_site_config()->dbObject('SuccessMessageMail');
        }
    }

    public function getTicketContent()
    {
        if (!empty($this->owner->PrintedTicketContent)) {
            return $this->owner->dbObject('PrintedTicketContent');
        } else {
            return SiteConfig::current_site_config()->dbObject('PrintedTicketContent');
        }
    }

    public function updateFieldLabels(&$labels)
    {
        $labels['GuestListStatus'] = _t(__CLASS__ . '.GuestListStatus', 'Gastenlijst');
        $labels['StartDate'] = _t(__CLASS__ . '.StartDate', 'Datum');
    }

    /**
     * Check if the method 'getTicketEventTitle' has been set and retrieve the event name.
     * This is used in ticket emails
     *
     * @return string
     * @throws Exception
     */
    public function getEventTitle()
    {
        throw new Exception("You should create a method 'getEventTitle' on {$this->owner->ClassName}");
    }

    /**
     * @return DBDatetime
     */
    public function getEventStartDate()
    {
        throw new Exception("You should create a method 'getEventStartDate' on {$this->owner->ClassName}");
    }

    public function getEventAddress()
    {
        throw new Exception("You should create a method 'getEventAddress' on {$this->owner->ClassName}");
    }
}
