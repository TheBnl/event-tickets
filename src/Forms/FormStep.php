<?php

namespace Broarm\EventTickets\Forms;

use Broarm\EventTickets\Model\Reservation;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\Validator;

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

    public function __construct(
        RequestHandler $controller = null,
        $name = self::DEFAULT_NAME,
        FieldList $fields = null,
        FieldList $actions = null,
        Validator $validator = null
    )
    {
        parent::__construct($controller, $name, $fields, $actions, $validator);
        $this->extend('updateForm');
    }
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
