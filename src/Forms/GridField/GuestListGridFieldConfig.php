<?php

namespace Broarm\EventTickets\Forms\GridField;

use Broarm\EventTickets\Controllers\GuestListImportController;
use Broarm\EventTickets\Model\Attendee;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\ORM\DataObject;

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
    public function __construct(DataObject $event, $itemsPerPage = null)
    {
        parent::__construct($itemsPerPage);

        
        
        

        $columns = Config::inst()->get(GuestListExportButton::class, 'default_export_columns');
        $fields = $event->Fields()->map('Name', 'Title')->toArray();
        // if (!empty($fields)) {
        //     foreach ($fields as $name => $title) {
        //         $fields[$name] = function($relObj) use ($name, $title) {
        //             if ($name == 'Organisatie') {
        //                 echo '<pre>';
        //                 print_r($relObj);
        //                 echo '</pre>';
        //                 exit();
        //             }

        //             return $relObj;
        //         };
        //     }
        // }

        $columns = array_merge($columns, $fields);
        // $columns += $fields;


        // echo '<pre>';
        // print_r($fields);
        // echo '</pre>';
        // exit();

        /** @var GridFieldDataColumns $dataColumns */
        // $dataColumns = $this->getComponentByType(GridFieldDataColumns::class);
        // $dataColumns->setDisplayFields($fields);

        $this->addComponent(new GuestListExportButton('buttons-before-left', $columns));
        $this->addComponent($importButton = new GridFieldImportButton('buttons-before-left'));
        $importButton->setModalTitle(_t(__CLASS__ . '.ImportGuestList', 'Import guestlist'));
        $importButton->setImportForm(
            GuestListImportController::singleton()->GuestListUploadForm($event->ID)
        );
    }
}
