<?php
/**
 * PriceModifier.php
 *
 * @author Bram de Leeuw
 * Date: 31/03/17
 */


namespace Broarm\EventTickets;

use DataObject;
use FieldList;
use ManyManyList;
use SQLUpdate;
use Tab;
use TabSet;
use TextField;

/**
 * Class PriceModifier
 *
 * @package Broarm\EventTickets
 *
 * @property string Title
 * @property float  PriceModification
 * @method ManyManyList Reservations
 */
class PriceModifier extends DataObject implements PriceModifierInterface
{
    private static $db = array(
        'Title' => 'Varchar(255)',
        'Sort' => 'Int'
    );

    private static $default_sort = 'Sort ASC, ID DESC';

    private static $defaults = array(
        'Sort' => 0
    );

    private static $many_many = array(
        'Reservations' => 'Broarm\EventTickets\Reservation'
    );

    private static $many_many_extraFields = array(
        'Reservations' => array(
            'PriceModification' => 'Currency'
        )
    );

    private static $casting = array(
        'TableValue' => 'Currency'
    );

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root', $mainTab = new Tab('Main')));
        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', _t('PriceModifier.TITLE', 'Title'))
        ));

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    /**
     * Modify the given total
     * Implement this on your modifier
     *
     * @param float $total
     */
    public function updateTotal(&$total) {}

    /**
     * Return a title to display in the summary table
     *
     * @return string
     */
    public function getTableTitle()
    {
        return $this->Title;
    }

    /**
     * Return a value to display in the summary table
     *
     * By default go out from a price reduction.
     * if you created a modifier that adds value, like a shipping calculator, make sure to overwrite this method
     *
     * @return float
     */
    public function getTableValue()
    {
        return $this->PriceModification * -1;
    }

    /**
     * Set the price modification on the join
     *
     * @param $value
     */
    public function setPriceModification($value)
    {
        if ($this->exists()) {
            $join = $this->manyMany('Reservations');
            $table = end($join);
            $where = $this->getSourceQueryParam('Foreign.Filter');
            $where["`{$this->baseTable()}ID`"] = $this->ID;
            SQLUpdate::create(
                "`{$table}`",
                array('`PriceModification`' => $value),
                $where
            )->execute();
        }
    }
}
