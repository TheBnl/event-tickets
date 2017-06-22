<?php

namespace Broarm\EventTickets;

use CalendarEvent;
use GridFieldConfig_RecordEditor;

/**
 * Class GuestListGridFieldConfig
 *
 * @author Bram de Leeuw
 * @package Broarm\EventTickets
 */
class GuestListGridFieldConfig extends GridFieldConfig_RecordEditor
{
    public function __construct(CalendarEvent $event, $itemsPerPage = null)
    {
        parent::__construct($itemsPerPage);
        $this->addComponent(new GuestListExportButton($event, 'Footer'));
        $this->extend('updateGuestListConfig', $event);
    }
}
