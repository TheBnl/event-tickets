<?php

namespace Broarm\EventTickets\Forms;

use Broarm\EventTickets\Model\Reservation;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\Form;

/**
 * Class FormStep
 *
 * @package Broarm\EventTickets
 */
abstract class FormStep extends Form
{
    /**
     * @var Reservation
     */
    protected $reservation;

    /**
     * @var string the next action to go to
     */
    protected $nextStep;

    /**
     * Get the next step
     *
     * @return string
     */
    public function getNextStep()
    {
        return $this->nextStep;
    }

    /**
     * Set the next step
     * should this be configurable .. ?
     *
     * @param $step
     */
    public function setNextStep($step)
    {
        $this->nextStep = $step;
    }

    /**
     * Continue to the next step
     *
     * @return HTTPResponse
     */
    public function nextStep()
    {
        return $this->getController()->redirect($this->getController()->Link($this->nextStep));
    }

    /**
     * Accessor to the reservation
     *
     * @return Reservation
     */
    public function getReservation()
    {
        return $this->reservation;
    }
}
