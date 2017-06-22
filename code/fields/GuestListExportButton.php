<?php
/**
 * GuestListExportButton.php
 *
 * @author Bram de Leeuw
 * Date: 01/06/17
 */

namespace Broarm\EventTickets;

use CalendarEvent;
use GridField;
use GridFieldExportButton;
use SS_HTTPRequest;

/**
 * Class GuestListExportButton
 */
class GuestListExportButton extends GridFieldExportButton
{
    /**
     * @var CalendarEvent
     */
    protected $event;

    /**
     * GuestListExportButton constructor.
     *
     * @param CalendarEvent $event
     * @param string        $targetFragment
     * @param null          $exportColumns
     */
    public function __construct(CalendarEvent $event, $targetFragment = "after", $exportColumns = null)
    {
        $this->event = $event;
        parent::__construct($targetFragment, $exportColumns);
    }

    /**
     * Get the parent event
     *
     * @return CalendarEvent|TicketExtension
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param      $gridField
     * @param null $request
     *
     * @return mixed
     */
    public function handleExport($gridField, $request = null) {
        $now = Date("d-m-Y-H-i");
        $fileName = "guestlist-$now.csv";

        if($fileData = $this->generateExportFileData($gridField)){
            return SS_HTTPRequest::send_file($fileData, $fileName, 'text/csv');
        }
    }

    /**
     * Generate export fields for CSV.
     *
     * @param GridField $gridField
     * @return array
     */
    public function generateExportFileData($gridField) {
        $separator = $this->csvSeparator;
        $csvColumns = $this->getExportColumnsForGridField($gridField);
        // Get a map of added fields and fieldnames
        $extraFieldColumns = $this->getEvent()->Fields()->map()->toArray();
        $fileData = '';

        $headers = array();
        
        // determine the CSV headers. If a field is callable (e.g. anonymous function) then use the
        // source name as the header instead
        // todo add connected extra fields headers
        foreach($csvColumns as $columnSource => $columnHeader) {
            if (is_array($columnHeader) && array_key_exists('title', $columnHeader)) {
                $headers[] = $columnHeader['title'];
            } else {
                $headers[] = (!is_string($columnHeader) && is_callable($columnHeader)) ? $columnSource : $columnHeader;
            }
        }

        // Add the extra fields as headers
        foreach ($extraFieldColumns as $source => $header) {
            $headers[] = $header;
        }

        $fileData .= "\"" . implode("\"{$separator}\"", array_values($headers)) . "\"";
        $fileData .= "\n";

        //Remove GridFieldPaginator as we're going to export the entire list.
        $gridField->getConfig()->removeComponentsByType('GridFieldPaginator');

        $items = $gridField->getManipulatedList();


        foreach($gridField->getConfig()->getComponents() as $component){
            if($component instanceof GridFieldFilterHeader || $component instanceof GridFieldSortableHeader) {
                $items = $component->getManipulatedData($gridField, $items);
            }
        }

        foreach($items->limit(null) as $item) {
            /** @var Attendee $item */
            if(!$item->hasMethod('canView') || $item->canView()) {
                $columnData = array();

                foreach($csvColumns as $columnSource => $columnHeader) {
                    if(!is_string($columnHeader) && is_callable($columnHeader)) {
                        if($item->hasMethod($columnSource)) {
                            $relObj = $item->{$columnSource}();
                        } else {
                            $relObj = $item->relObject($columnSource);
                        }

                        $value = $columnHeader($relObj);
                    } else {
                        $value = $gridField->getDataFieldValue($item, $columnSource);

                        if($value === null) {
                            $value = $gridField->getDataFieldValue($item, $columnHeader);
                        }
                    }

                    $value = str_replace(array("\r", "\n"), "\n", $value);
                    $columnData[] = '"' . str_replace('"', '""', $value) . '"';
                }

                // Add the extra field data
                foreach ($extraFieldColumns as $source => $header) {
                    if ($field = $item->Fields()->byID($source)) {
                        $columnData[] = "\"{$field->getValue()}\"";
                    } else {
                        $columnData[] = '""';
                    }
                }

                $fileData .= implode($separator, $columnData);
                $fileData .= "\n";
            }

            if($item->hasMethod('destroy')) {
                $item->destroy();
            }
        }

        return $fileData;
    }
}
