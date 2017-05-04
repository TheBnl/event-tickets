<?php
/**
 * CurrencyExtension.php
 *
 * @author Bram de Leeuw
 * Date: 27/03/17
 */

namespace Broarm\EventTickets;

use DataExtension;
use Currency;

/**
 * Class CurrencyExtension
 *
 * @property CurrencyExtension|Currency $owner
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
        $currencySymbol = $this->owner->config()->currency_symbol;
        $decimalPoint = $this->owner->config()->decimal_point;
        $thousandSeparator = $this->owner->config()->thousand_seperator;
        $val = $currencySymbol . number_format(abs($this->owner->value), 2, $decimalPoint, $thousandSeparator);
        if ((double)$this->owner->value === (double)0) {
            return _t('CurrencyExtension.FREE', 'Free');
        } elseif ((double)$this->owner->value < 0) {
            return "($val)";
        } else {
            return $val;
        }
    }
}
