<?php

namespace Broarm\EventTickets\Checkout\Steps;

use Broarm\EventTickets\Forms\SummaryForm;
use SilverStripe\Control\HTTPResponse;

class SummaryStep extends CheckoutStep
{
    public $step = 'summary';

    private static $allowed_actions = array(
        'summary',
        'SummaryForm'
    );

    /**
     * @return array|HTTPResponse
     */
    public function summary()
    {
        if (!$reservation = $this->getReservation()) {
            return $this->owner->redirect($this->owner->Link());
        }

        // todo add configuration to validate all users
        if (!($mainContact = $reservation->MainContact()) || !$mainContact->isValid()) {
            return $this->owner->redirect($this->owner->Link());
        }

        return [
            'Form' => $this->SummaryForm()
        ];
    }

    public function SummaryForm()
    {
        $summary = new SummaryForm($this->owner, 'SummaryForm', $this->getReservation());
        $summary->setSummaryStep($this->step);
        $summary->setNextStep(CheckoutSteps::nextStep($this->step));
        return $summary;
    }
}
