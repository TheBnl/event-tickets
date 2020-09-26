<?php

namespace Broarm\EventTickets\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBCurrency;

/**
 * Class CurrencyExtension
 *
 * @property CurrencyExtension|DBCurrency $owner
 */
class CurrencyExtension extends DataExtension
{
    private static $decimal_point = '.';

    private static $thousand_separator = ',';

    /**
     * Returns the number as a currency, eg “$1,000.00”.
     * Where the decimal point and thousand separator are configurable
     *
     * @return string
     */
    public function NiceDecimalPoint()
    {
        $value = $this->owner->getValue();
        $currencySymbol = DBCurrency::config()->get('currency_symbol');
        $decimalPoint = DBCurrency::config()->get('decimal_point');
        $thousandSeparator = DBCurrency::config()->get('thousand_seperator');
        $val = $currencySymbol . number_format(abs($value), 2, $decimalPoint, $thousandSeparator);
        if ((double)$value  === (double)0) {
            return _t(__CLASS__ . '.FREE', 'Free');
        } elseif ((double)$value  < 0) {
            return "($val)";
        } else {
            return $val;
        }
    }
}
