<?php

namespace Broarm\EventTickets\Tasks;

use Broarm\EventTickets\Model\Reservation;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;

/**
 * Class CleanCartTask
 * Cleanup discarded tasks
 *
 * @package Broarm\EventTickets
 */
class CleanCartTask extends BuildTask
{
    protected $title = 'Cleanup cart task';

    protected $description = 'Cleanup discarded ticket shop carts';

    private static $dependencies = [
        'Logger' => '%$' . LoggerInterface::class,
    ];
    
    /**
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        if (!Director::is_cli()) echo '<pre>';
        $this->logger->debug("Start cleaning");

        /** @var Reservation $reservation */
        foreach (Reservation::get()->filter(['Status' => Reservation::STATUS_CART]) as $reservation) {
            if ($reservation->isDiscarded()) {
                $this->logger->debug("Delete reservation #{$reservation->ID}");
                $reservation->delete();
            }
        }

        // Update status on stalled payments
        foreach (Reservation::get()->filter(['Status' => Reservation::STATUS_PENDING]) as $reservation) {
            if ($reservation->isStalledPayment()) {
                $this->logger->debug("Set reservation #{$reservation->ID} to payment failed ");
                $reservation->Status = Reservation::STATUS_PAYMENT_FAILED;
                $reservation->write();
            }
        }

        $this->logger->debug("Done cleaning");
        if (!Director::is_cli()) echo '</pre>';
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }
}
