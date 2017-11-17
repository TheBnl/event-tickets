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
    public function __construct(CalendarEvent $event, $itemsPerPage = null)
    {
        parent::__construct($itemsPerPage);
        $this->addComponent(new GridFieldAddNewButton('buttons-before-left'));
        $this->addComponent(new GuestListExportButton($event, 'buttons-before-left'));
        $this->extend('updateGuestListConfig', $event);
    }
}
