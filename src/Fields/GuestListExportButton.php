<?php

namespace Broarm\EventTickets\Extensions;

use SilverStripe\Forms\GridField\GridFieldExportButton;

/**
 * Class GuestListExportButton
 * @deprecated Depends on PHPExcel, move to default CSV export
 */
class GuestListExportButton extends GridFieldExportButton
{
//    public function getHTMLFragments($gridField) {
//        $title = $this->buttonTitle ?: _t('TableListField.XLSEXPORT', 'Export to Excel');
//        $name = $this->getActionName();
//        $button = new GridField_FormAction($gridField, $name, $title, $name, null);
//        $button->addExtraClass('btn btn-secondary font-icon-down-circled btn--icon-large action_export no-ajax');
//        $button->setForm($gridField->getForm());
//
//        return array(
//            $this->targetFragment => '<p class="grid-excel-button">' . $button->Field() . '</p>',
//        );
//    }
//
//    /**
//     * Generate export fields for Excel.
//     *
//     * @param GridField $gridField
//     * @return PHPExcel
//     * @throws \PHPExcel_Exception
//     */
//    public function generateExportFileData($gridField)
//    {
//        $class = $gridField->getModelClass();
//        $columns = $this->exportColumns ?: ExcelImportExport::exportFieldsForClass($class);
//        $plural = $class ? singleton($class)->i18n_plural_name() : '';
//
//        $filter = new FileNameFilter();
//        $this->exportName = $filter->filter($this->exportName ?: 'export-' . $plural);
//
//        $excel = new PHPExcel();
//        $excelProperties = $excel->getProperties();
//        $excelProperties->setTitle($this->exportName);
//
//        $sheet = $excel->getActiveSheet();
//        if ($plural) {
//            $sheet->setTitle($plural);
//        }
//
//        $row = 1;
//        $col = 0;
//
//        if ($this->hasHeader) {
//            $headers = array();
//
//            // determine the headers. If a field is callable (e.g. anonymous function) then use the
//            // source name as the header instead
//            foreach ($columns as $columnSource => $columnHeader) {
//                $headers[] = (!is_string($columnHeader) && is_callable($columnHeader))
//                    ? $columnSource
//                    : $columnHeader;
//            }
//
//            foreach ($headers as $header) {
//                $sheet->setCellValueByColumnAndRow($col, $row, $header);
//                $col++;
//            }
//
//            $endCol = PHPExcel_Cell::stringFromColumnIndex($col - 1);
//            $sheet->setAutoFilter("A1:{$endCol}1");
//            $sheet->getStyle("A1:{$endCol}1")->getFont()->setBold(true);
//
//            $col = 0;
//            $row++;
//        }
//
//        // Autosize
//        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
//        try {
//            $cellIterator->setIterateOnlyExistingCells(true);
//        } catch (Exception $ex) {
//            // Ignore exceptions
//        }
//        foreach ($cellIterator as $cell) {
//            $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
//        }
//
//        //Remove GridFieldPaginator as we're going to export the entire list.
//        $gridField->getConfig()->removeComponentsByType('GridFieldPaginator');
//        $items = $gridField->getManipulatedList();
//
//        // @todo should GridFieldComponents change behaviour based on whether others are available in the config?
//        foreach ($gridField->getConfig()->getComponents() as $component) {
//            if ($component instanceof GridFieldFilterHeader || $component instanceof GridFieldSortableHeader) {
//                $items = $component->getManipulatedData($gridField, $items);
//            }
//        }
//
//        $list = $items->limit(null);
//        if (!empty($this->listFilters)) {
//            $list = $list->filter($this->listFilters);
//        }
//
//        /** @var Attendee $item */
//        foreach ($list as $item) {
//            if (!$this->checkCanView || !$item->hasMethod('canView') || $item->canView()) {
//                foreach ($columns as $columnSource => $columnHeader) {
//                    if (!is_string($columnHeader) && is_callable($columnHeader)) {
//                        if ($item->hasMethod($columnSource)) {
//                            $relObj = $item->{$columnSource}();
//                        } else {
//                            $relObj = $item->relObject($columnSource);
//                        }
//                        $value = $columnHeader($relObj);
//                    } elseif ($field = $item->Fields()->byID($columnSource)) {
//                        $value = $field->getValue();
//                    } elseif ($field = $item->Fields()->find('Name', $columnSource)) {
//                        $value = $field->getValue();
//                    } else {
//                        $value = $gridField->getDataFieldValue($item, $columnSource);
//                    }
//
//                    $sheet->setCellValueByColumnAndRow($col, $row, $value);
//                    $col++;
//                }
//            }
//
//            if ($item->hasMethod('destroy')) {
//                $item->destroy();
//            }
//
//            $col = 0;
//            $row++;
//        }
//
//        return $excel;
//    }
}
