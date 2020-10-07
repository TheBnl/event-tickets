<?php

namespace Broarm\EventTickets\Model;

use Broarm\EventTickets\Interfaces\PriceModifierInterface;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\Connect\DBSchemaManager;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectSchema;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\Queries\SQLUpdate;

/**
 * Class PriceModifier
 *
 * @package Broarm\EventTickets
 *
 * @property string Title
 * @property float  PriceModification
 *
 * @method ManyManyList Reservations()
 */
class PriceModifier extends DataObject implements PriceModifierInterface
{
    private static $table_name = 'EventTickets_PriceModifier';

    private static $db = array(
        'Title' => 'Varchar(255)',
        'Sort' => 'Int'
    );

    private static $default_sort = 'Sort ASC, ID DESC';

    private static $defaults = array(
        'Sort' => 0
    );

    private static $many_many = array(
        'Reservations' => Reservation::class
    );

    private static $many_many_extraFields = array(
        'Reservations' => array(
            'PriceModification' => 'Currency'
        )
    );

    private static $casting = array(
        'TableValue' => 'Currency'
    );

    private static $summary_fields = array(
        'TableTitle',
        'TableValue'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', _t(__CLASS__ . '.Title', 'Title'))
        ));

        return $fields;
    }

    /**
     * Modify the given total
     * Implement this on your modifier
     *
     * @param float $total
     * @param Reservation $reservation
     */
    public function updateTotal(&$total, Reservation $reservation) {}

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
            $manyMany = DataObject::getSchema()->manyManyComponent(self::class, 'Reservations');
            $table = $manyMany['join'];
            $where = $this->getSourceQueryParam('Foreign.Filter');
            $where[$manyMany['parentField']] = $this->ID;
            SQLUpdate::create(
                "`{$table}`",
                array('`PriceModification`' => $value),
                $where
            )->execute();
        }
    }
}
