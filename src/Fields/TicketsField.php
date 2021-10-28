<?php

namespace Broarm\EventTickets\Fields;

use Broarm\EventTickets\Model\Ticket;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\Validator;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Class TicketsField
 *
 * @package Broarm\EventTickets
 */
class TicketsField extends FormField
{

    protected $tickets;

    //protected $template = 'TicketsField';

    public function __construct($name, $title, DataList $tickets)
    {
        $this->tickets = $tickets;
        parent::__construct($name, $title);
    }

    /**
     * Set the ticket list
     *
     * @param DataList $tickets
     */
    public function setTickets(DataList $tickets)
    {
        $this->tickets = $tickets;
    }

    /**
     * Get the ticket list
     *
     * @return DataList
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * Get a list of editable tickets
     * These have an numeric input field
     *
     * @return ArrayList
     */
    private function getEditableTickets()
    {
        $tickets = ArrayList::create();
        foreach ($this->getTickets() as $ticket) {
            /** @var Ticket $ticket */
            $fieldName = $this->name . "[{$ticket->ID}][Amount]";
            $range = range($ticket->OrderMin, $ticket->OrderMax);

            $ticket->AmountField = DropdownField::create($fieldName, 'Amount', array_combine($range, $range))
                ->setHasEmptyDefault(true)
                ->setEmptyString(_t('TicketsField.EMPTY', 'Tickets'));

            // Set the first to hold the minimum
            if ($this->getTickets()->count() === 1) {
                $ticket->AmountField->setValue($ticket->OrderMin);
            }

            $availability = $ticket->TicketPage()->getAvailability();
            if ($availability < $ticket->OrderMax) {
                $disabled = range($availability + 1, $ticket->OrderMax);
                $ticket->AmountField->setDisabledItems(array_combine($disabled, $disabled));
            }

            if (!$ticket->getAvailable()) {
                $ticket->AmountField->setDisabled(true);
            }

            $tickets->push($ticket);
        }
        return $tickets;
    }

    /**
     * Get the field customized with tickets and reservation
     *
     * @param array $properties
     *
     * @return DBHTMLText|string
     */
    public function Field($properties = array())
    {
        $context = $this;
        $properties['Tickets'] = $this->getEditableTickets();

        if (count($properties)) {
            $context = $context->customise($properties);
        }

        $this->extend('onBeforeRender', $this);
        $result = $context->renderWith($this->getTemplates());

        if (is_string($result)) {
            $result = trim($result);
        } else {
            if ($result instanceof DBField) {
                $result->setValue(trim($result->getValue()));
            }
        }

        return $result;
    }

    /**
     * Make sure a ticket is selected and that the selected amount is available
     *
     * @param Validator $validator
     *
     * @return bool
     */
    public function validate($validator)
    {
        // Throw an error when there are no tickets selected
        if (empty($this->value)) {
            $validator->validationError($this->name, _t(
                'TicketsField.VALIDATION_EMPTY',
                'Select at least one ticket'
            ), 'validation');

            return false;
        }

        // Get the availability
        $available = $this->getForm()->getController()->getAvailability();
        // get the sum of selected tickets
        $ticketCount = array_sum(array_map(function ($item) {
            return $item['Amount'];
        }, $this->value));

        // If the sum of tickets is 0 trow the same error as empty
        if ($ticketCount === 0) {
            $validator->validationError($this->name, _t(
                'TicketsField.VALIDATION_EMPTY',
                'Select at least one ticket'
            ), 'validation');

            return false;
        }

        // Throw an error when there are more tickets selected than available
        if ($ticketCount > $available) {
            $validator->validationError($this->name, _t(
                'TicketsField.VALIDATION_TO_MUCH',
                'There are {ticketCount} tickets left',
                null,
                array(
                    'ticketCount' => $available
                )
            ), 'validation');

            return false;
        }

        return false;
    }
}
