<?php
/**
 * TicketExtension.php
 *
 * @author Bram de Leeuw
 * Date: 09/03/17
 */

namespace Broarm\EventTickets;

use CalendarEvent_Controller;
use DataExtension;
use FieldList;
use GridField;
use GridFieldAddNewButton;
use GridFieldConfig_RecordEditor;
use HtmlEditorField;
use LiteralField;
use NumericField;
use SiteConfig;

/**
 * Class TicketExtension
 *
 * @package Broarm\EventTickets
 *
 * @property TicketExtension|\CalendarEvent $owner
 * @property int                            Capacity
 * @property string                         SuccessMessage
 * @property string                         SuccessMessageMail
 *
 * @method \HasManyList Tickets
 * @method \HasManyList Reservations
 * @method \HasManyList Attendees
 */
class TicketExtension extends DataExtension
{
    /**
     * @var CalendarEvent_Controller
     */
    protected $controller;

    private static $db = array(
        'Capacity' => 'Int',
        'SuccessMessage' => 'HTMLText',
        'SuccessMessageMail' => 'HTMLText'
    );

    private static $has_many = array(
        'Tickets' => 'Broarm\EventTickets\Ticket.Event',
        'Reservations' => 'Broarm\EventTickets\Reservation.Event',
        'Attendees' => 'Broarm\EventTickets\Attendee.Event'
    );

    public function updateCMSFields(FieldList $fields)
    {
        $gridFieldConfig = GridFieldConfig_RecordEditor::create();

        // If the event dates are in the past remove ability to create new tickets, reservations and attendees.
        // This is done here instead of in the canCreate method because of the lack of context there.
        if (!$this->canCreateTickets()) {
            $gridFieldConfig->removeComponentsByType(new GridFieldAddNewButton());
        }

        $ticketLabel = _t('TicketExtension.Tickets', 'Tickets');
        $fields->addFieldsToTab(
            "Root.$ticketLabel", array(
            GridField::create('Tickets', $ticketLabel, $this->owner->Tickets(), $gridFieldConfig),
            NumericField::create('Capacity', _t('TicketExtension.Capacity', 'Capacity')),
            HtmlEditorField::create('SuccessMessage', 'Success message')->setRows(4),
            HtmlEditorField::create('SuccessMessageMail', 'Mail message')->setRows(4)
        ));

        if ($this->owner->Reservations()->exists()) {
            $reservationLabel = _t('TicketExtension.Reservations', 'Reservations');
            $fields->addFieldToTab(
                "Root.$reservationLabel",
                GridField::create('Reservations', $reservationLabel, $this->owner->Reservations(), $gridFieldConfig)
            );
        }

        if ($this->owner->Attendees()->exists()) {
            $guestListLabel = _t('TicketExtension.GuestList', 'GuestList');
            $fields->addFieldToTab(
                "Root.$guestListLabel",
                GridField::create('Attendees', $guestListLabel, $this->owner->Attendees(), $gridFieldConfig)
            );
        }

        return $fields;
    }

    /**
     * Extend the page actions with an start check in action
     *
     * @param FieldList $actions
     */
    public function updateCMSActions(FieldList $actions)
    {
        $checkInButton = new LiteralField('StartCheckIn',
            "<a class='action ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only'
                id='Edit_StartCheckIn'
                role='button'
                href='{$this->owner->Link('checkin')}'
                target='_blank'>
                Start check in
            </a>"
        );

        if ($this->owner->Attendees()->exists()) {
            $actions->push($checkInButton);
        }
    }

    /**
     * Get the leftover capacity
     *
     * @return int
     */
    public function getAvailability()
    {
        return $this->owner->Capacity - $this->owner->Attendees()->count();
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

    /**
     * Get the Ticket logo
     *
     * @return \Image
     */
    public function getMailLogo()
    {
        return SiteConfig::current_site_config()->TicketLogo();
    }

    /**
     * Check if the current event can have tickets
     *
     * @return bool
     */
    public function canCreateTickets()
    {
        $currentDate = $this->owner->getController()->CurrentDate();
        if ($currentDate && $currentDate->exists()) {
            return $currentDate->dbObject('StartDate')->InFuture();
        }

        return false;
    }

    /**
     * Get the calendar controller
     *
     * @return CalendarEvent_Controller
     */
    public function getController()
    {
        return $this->controller
            ? $this->controller
            : $this->controller = CalendarEvent_Controller::create($this->owner);
    }
}
