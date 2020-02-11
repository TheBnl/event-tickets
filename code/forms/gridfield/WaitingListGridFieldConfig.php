<?php

namespace Broarm\EventTickets;

use CalendarEvent;
use ExcelGridFieldExportButton;
use GridFieldAddNewButton;
use GridFieldConfig_RecordEditor;
use GridFieldDataColumns;
use GridFieldPaginator;

/**
 * Class WaitingListGridFieldConfig
 *
 * @author Bram de Leeuw
 * @package Broarm\EventTickets
 */
class WaitingListGridFieldConfig extends GridFieldConfig_RecordEditor
{
    public function __construct($itemsPerPage = null)
    {
        parent::__construct($itemsPerPage);
        $this->addComponent(new ExcelGridFieldExportButton('buttons-before-left'));
    }
}
