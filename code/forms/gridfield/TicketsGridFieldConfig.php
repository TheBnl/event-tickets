<?php

namespace Broarm\EventTickets;

use GridFieldAddNewButton;
use GridFieldConfig_RecordEditor;
use GridFieldOrderableRows;

/**
 * Class TicketsGridFieldConfig
 *
 * @author Bram de Leeuw
 * @package Broarm\EventTickets
 */
class TicketsGridFieldConfig extends GridFieldConfig_RecordEditor
{
    public function __construct($editable = true, $itemsPerPage = null)
    {
        parent::__construct($itemsPerPage);
        $this->addComponent(new GridFieldOrderableRows('Sort'));

        if (!$editable) {
            $this->removeComponentsByType(new GridFieldAddNewButton());
        }

        $this->extend('updateConfig');
    }
}