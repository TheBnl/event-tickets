<?php

namespace Broarm\EventTickets\Checkout\Steps;

use Broarm\EventTickets\Checkout\Steps\CheckoutStep;
use Broarm\EventTickets\Checkout\Steps\CheckoutSteps;
use Broarm\EventTickets\Forms\TicketForm;
use SilverStripe\Forms\FormAction;

class TicketsStep extends CheckoutStep
{
    protected $step = 'tickets';

    private static $allowed_actions = [
        'SteppedTicketForm',
        'ticketaction',
        'goBack'
    ];

    private static $url_handlers = [
        'tickets' => 'ticketaction'
    ];

    public function ticketaction()
    {
        return [
            'TicketForm' => $this->SteppedTicketForm(),
            'Form' => $this->SteppedTicketForm()
        ];
    }

    public function SteppedTicketForm()
    {
        if ($this->owner->Tickets()->count() && $this->owner->getTicketsAvailable()) {
            $ticketForm = new TicketForm($this->owner, 'SteppedTicketForm', $this->owner->Tickets(), $this->owner->data());
            $ticketForm->setNextStep(CheckoutSteps::nextStep($this->step));

            // Add go back action
            $ticketForm->Actions()->insertBefore(
                'action_handleTicketForm',
                FormAction::create('goBack', _t(__CLASS__ . '.Cancel', 'Annuleren'))
                    ->setAttribute('formnovalidate', true)    
                    ->setValidationExempt(true)
                    ->addExtraClass('button clear')
            );

            return $ticketForm;
        } else {
            return null;
        }
    }

    public function goBack($data, $form)
    {
        $controller = $form->getController();
        $currentStep = CheckoutSteps::prevStep($form->nextStep);
        $prevStep = CheckoutSteps::prevStep($currentStep);
        $controller->redirect($this->owner->Link($prevStep));    
    }
}