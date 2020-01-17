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
     * @param float $total
     * @param Reservation $reservation
     */
    public function updateTotal(&$total, Reservation $reservation);

    /**
     * Get the title for display in the summary table
     *
     * @return string
     */
    public function getTableTitle();

    /**
     * Get the modification value for display in the summary table
     * Can be a discount or an addition
     *
     * @return float
     */
    public function getTableValue();
}
