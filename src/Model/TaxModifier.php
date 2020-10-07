<?php

namespace Broarm\EventTickets\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;

/**
 * Class TaxModifier
 *
 * Adds a configurable tax rate on the receipt
 *
 * @package Broarm\EventTickets
 */
class TaxModifier extends PriceModifier
{
    private static $table_name = 'EventTickets_TaxModifier';

    /**
     * Set the tax rate as a percentage
     *
     * @var string
     */
    private static $tax_rate = 21;

    /**
     * Set if the tax rate is inclusive or exclusive
     *
     * @var string
     */
    private static $inclusive = false;

    /**
     * Set the default sort value to a large int so it always shows and calculates as last
     *
     * @var array
     */
    private static $defaults = array(
        'Title' => 'Tax modifier',
        'Sort' => 9999
    );

    /**
     * Update the total, if the tax is not inclusive the total gets altered
     *
     * @param float $total
     * @param Reservation $reservation
     */
    public function updateTotal(&$total, Reservation $reservation) {
        $rate = (float)self::config()->get('tax_rate') / 100;
        $tax = $total * $rate;
        $this->setPriceModification($tax);
        if (!(bool)self::config()->get('inclusive')) {
            $total += $tax;
        }
    }

    /**
     * Show the used tax rate in the table title
     *
     * @return string
     */
    public function getTableTitle()
    {
        $rate = _t(
            __CLASS__ . '.TableTitle',
            '{rate}% BTW',
            null,
            array(
                'rate' => (float)self::config()->get('tax_rate')
            )
        );

        if ((bool)self::config()->get('inclusive')) {
            $inc = _t(__CLASS__ . '.Inclusive', '(Incl.)');
            $rate .= " $inc";
        }

        return $rate;
    }

    /**
     * Show the calculated tax value as a positive value
     *
     * @return float
     */
    public function getTableValue()
    {
        return $this->PriceModification;
    }

    /**
     * Create a tax modifier if it does not already exists
     *
     * @param Reservation $reservation
     * @return TaxModifier|DataObject|null
     * @throws ValidationException
     */
    public static function findOrMake(Reservation $reservation)
    {
        if (!$modifier = $reservation->PriceModifiers()->find('ClassName', self::class)) {
            $modifier = self::create();
            $modifier->write();
        }

        return $modifier;
    }
}
