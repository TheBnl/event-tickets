<?php

namespace Broarm\EventTickets\Forms\GridField;

use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldExportButton;

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
        $this->addComponent(new GridFieldExportButton('buttons-before-left'));
    }
}
