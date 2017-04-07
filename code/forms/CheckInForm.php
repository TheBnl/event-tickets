<?php
/**
 * CheckInForm.php
 *
 * @author Bram de Leeuw
 * Date: 07/04/17
 */

namespace Broarm\EventTickets;

use FieldList;
use Form;
use FormAction;
use RequiredFields;
use TextField;

class CheckInForm extends Form
{
    public function __construct($controller, $name = 'CheckInForm')
    {
        $fields = FieldList::create(
            TextField::create('TicketCode', _t('CheckInForm.TICKET_CODE', 'Ticket code'))
        );

        $actions = FieldList::create(
            FormAction::create('doCheckIn', _t('CheckInForm.CHECK_IN', 'Check in'))
        );

        $required = new RequiredFields(array('TicketCode'));

        parent::__construct($controller, $name, $fields, $actions, $required);
    }

    /**
     * Do the check in, if all checks pass return a success
     *
     * @param             $data
     * @param CheckInForm $form
     *
     * @return bool
     */
    public function doCheckIn($data, CheckInForm $form)
    {
        /** @var CheckInController $controller */
        $controller = $form->getController();

        // Check if the ticket code is set
        if (!isset($data['TicketCode'])) {
            $form->addErrorMessage('TicketCode', _t(
                'CheckInForm.NO_CODE',
                'Please submit a ticket code'
            ), 'error');

            $controller->redirect("{$controller->Link()}?success=-3");
            return false;
        }

        // Check if the event has registered attendees
        if (!$controller->Attendees()->exists()) {
            $form->addErrorMessage('TicketCode', _t(
                'CheckInForm.NO_ATTENDEES',
                'This event has no registered attendees'
            ), 'error');

            $controller->redirect("{$controller->Link()}?success=-2");
            return false;
        }

        // Check if the ticket is found on the current event
        /** @var Attendee $attendee */
        if (!$attendee = $controller->Attendees()->find('TicketCode', $data['TicketCode'])) {
            $form->addErrorMessage('TicketCode', _t(
                'CheckInForm.CODE_NOT_FOUND',
                'The given ticket is not found on this event'
            ), 'error');

            $controller->redirect("{$controller->Link()}?success=-1");
            return false;
        }

        // Check if the ticket is already used
        if ($attendee->CheckedIn) {
            $form->addErrorMessage('TicketCode', _t(
                'CheckInForm.ALREADY_CHECKED_IN',
                'This ticket is already checked in'
            ), 'error');

            $controller->redirect("{$controller->Link()}?success=0");
            return false;
        } else {
            $attendee->CheckedIn = true;
            $attendee->write();
            $form->sessionMessage(_t(
                'CheckInForm.SUCCESS',
                'The ticket is valid. {name} has a {ticket} ticket with number {number}',
                null, array(
                    'name' => $attendee->getName(),
                    'ticket' => $attendee->Ticket()->Title,
                    'number' => $attendee->TicketCode
                )
            ), 'good', false);
            $controller->redirect("{$controller->Link()}?success=1");
            return true;
        }
    }

}