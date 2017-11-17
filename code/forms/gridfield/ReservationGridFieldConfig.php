<?php

namespace Broarm\EventTickets;

use CalendarEvent;
use GridFieldAddNewButton;
use GridFieldConfig_RecordEditor;
use GridFieldDataColumns;
use GridFieldPaginator;

/**
 * Class GuestListGridFieldConfig
 *
 * @author Bram de Leeuw
 * @package Broarm\EventTickets
 */
class ReservationGridFieldConfig extends GridFieldConfig_RecordEditor
{
    public function __construct($itemsPerPage = null)
    {
        parent::__construct($itemsPerPage);
        $this->removeComponentsByType(new GridFieldAddNewButton('buttons-before-left'));
    }
}
