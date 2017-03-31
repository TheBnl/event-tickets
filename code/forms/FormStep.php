<?php
/**
 * FormStep.php
 *
 * @author Bram de Leeuw
 * Date: 16/03/17
 */

namespace Broarm\EventTickets;

use Form;
use SS_HTTPResponse;

/**
 * Class FormStep
 *
 * @package Broarm\EventTickets
 */
abstract class FormStep extends Form
{
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
     * @return SS_HTTPResponse
     */
    public function nextStep()
    {
        return $this->getController()->redirect($this->getController()->Link($this->nextStep));
    }
}
