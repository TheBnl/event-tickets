<?php

namespace Broarm\EventTickets\Forms\GridField;

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridFieldExportButton;

/**
 * Class GuestListExportButton
 */
class GuestListExportButton extends GridFieldExportButton
{
    private static $default_export_columns = [
        'TicketCode' => 'Ticket #',
        'Title' => 'Naam',
        'Email' => 'Email',
        'Ticket.Title' => 'Ticket',
        'CheckedIn.Nice' => 'Checked in',
    ];

    public function __construct($targetFragment = "after", $exportColumns = null)
    {
        if (!$exportColumns) {
            $exportColumns = Config::inst()->get(__CLASS__, 'default_export_columns');
        }

        parent::__construct($targetFragment, $exportColumns);
    }

    public function getHTMLFragments($gridField)
    {
        $button = new GridField_FormAction(
            $gridField,
            'export',
            _t(__CLASS__ . '.ExportGuestList', 'Export Guestlist'),
            'export',
            null
        );
        $button->addExtraClass('btn btn-secondary no-ajax font-icon-down-circled action_export');
        $button->setForm($gridField->getForm());
        return [
            $this->targetFragment => $button->Field(),
        ];
    }
}
