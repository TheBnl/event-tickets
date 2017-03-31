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
use Tab;
use TabSet;
use TextField;

/**
 * Class PriceModifier
 *
 * @property string Title
 */
class PriceModifier extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(255)'
    );

    private static $belongs_many_many = array(
        'Reservations' => 'Broarm\EventTickets\Reservation'
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
     * Return a title to display in the summary table
     *
     * @return string
     */
    public function getTableTitle() {
        return $this->Title;
    }

    /**
     * Return a value to display in the summary table
     */
    public function getTableValue() {}

    /**
     * Modify the given total
     *
     * @param $total
     */
    public function updateTotal(&$total) {}
}

