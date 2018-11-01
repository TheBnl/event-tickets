<?php

namespace Broarm\EventTickets;

use CalendarEvent;
use GridFieldAddNewButton;
use GridFieldConfig_RecordEditor;

/**
 * Class GuestListGridFieldConfig
 *
 * @author Bram de Leeuw
 * @package Broarm\EventTickets
 */
class GuestListGridFieldConfig extends ReservationGridFieldConfig
{
    /**
     * GuestListGridFieldConfig constructor.
     * @param CalendarEvent|TicketExtension $event
     * @param null $itemsPerPage
     */
    public function __construct(CalendarEvent $event, $itemsPerPage = null)
    {
        parent::__construct($itemsPerPage);
        $this->addComponent(new GridFieldAddNewButton('buttons-before-left'));
        $this->addComponent(new GuestListExportButton('buttons-before-left', $event->Fields()->map()->toArray()));
        $this->extend('updateGuestListConfig', $event);
    }
}
