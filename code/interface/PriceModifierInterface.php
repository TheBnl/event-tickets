<?php
/**
 * PriceModifierInterface.php
 *
 * @author Bram de Leeuw
 * Date: 06/04/17
 */

namespace Broarm\EventTickets;

interface PriceModifierInterface
{
    /**
     * Modify the given total
     *
     * @param $total
     */
    public function updateTotal(&$total);
}