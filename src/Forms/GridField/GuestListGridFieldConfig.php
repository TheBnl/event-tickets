<?php

namespace Broarm\EventTickets\Forms\GridField;

use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;

/**
 * Class GuestListGridFieldConfig
 *
 * @author Bram de Leeuw
 * @package Broarm\EventTickets
 */
class GuestListGridFieldConfig extends GridFieldConfig_RecordEditor
{
    /**
     * GuestListGridFieldConfig constructor.
     * @param null $itemsPerPage
     */
    public function __construct($itemsPerPage = null)
    {
        parent::__construct($itemsPerPage);
        $this->addComponent(new GuestListExportButton('buttons-before-left'));
    }
}
