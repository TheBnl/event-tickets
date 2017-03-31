<?php
/**
 * SummaryController.php
 *
 * @author Bram de Leeuw
 * Date: 16/03/17
 */

namespace Broarm\EventTickets;

/**
 * Class SummaryController
 *
 * @package Broarm\EventTickets
 */
class SummaryController extends CheckoutStepController
{
    protected $step = 'summary';

    private static $allowed_actions = array(
        'SummaryForm'
    );

    /**
     * Get the summary form
     *
     * @return SummaryForm
     */
    public function SummaryForm()
    {
        $summary = new SummaryForm($this, 'SummaryForm', ReservationSession::get());
        $summary->setNextStep(CheckoutSteps::nextStep($this->step));
        return $summary;
    }
}
