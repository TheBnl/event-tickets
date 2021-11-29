<?php

namespace Broarm\EventTickets\Forms\GridField;

use SilverStripe\Forms\GridField\GridFieldAddNewButton;

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
     * @param null $itemsPerPage
     */
    public function __construct($itemsPerPage = null)
    {
        parent::__construct($itemsPerPage);
        $this->addComponent(new GridFieldAddNewButton('buttons-before-left'));
        $this->addComponent(new GuestListExportButton('buttons-before-left'));
    }
}
