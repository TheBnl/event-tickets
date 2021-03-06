<?php

namespace Broarm\EventTickets;

use DataExtension;
use SilverStripe\Omnipay\Service\ServiceResponse;

/**
 * Class TicketPayment
 *
 * @author Bram de Leeuw
 * @property TicketPayment|\Payment $owner
 *
 * @property int                    ReservationID
 */
class TicketPayment extends DataExtension
{
    private static $has_one = array(
        'Reservation' => 'Broarm\EventTickets\Reservation',
    );

    /**
     * Fix issue manual gateway doesn't call onCaptured hook
     *
     * @param ServiceResponse $response
     */
    public function onAuthorized(ServiceResponse $response)
    {
        if ($response->getPayment()->Gateway === 'Manual') {
            if (($reservation = Reservation::get()->byID($this->owner->ReservationID)) && $reservation->exists()) {
                $reservation->complete();
            }
        }
    }

    /**
     * Complete the order on a successful transaction
     *
     * @param ServiceResponse $response
     *
     * @throws \ValidationException
     */
    public function onCaptured(ServiceResponse $response)
    {
        /** @var Reservation $reservation */
        if (($reservation = Reservation::get()->byID($this->owner->ReservationID)) && $reservation->exists()) {
            $reservation->complete();
        }
    }
}
