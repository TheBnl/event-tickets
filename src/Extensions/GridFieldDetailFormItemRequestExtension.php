<?php

namespace Broarm\EventTickets\Extensions;

use Broarm\EventTickets\Model\Reservation;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;

/**
 * Class GridFieldDetailFormItemRequestExtension
 * @package Broarm\EventTickets\Extensions
 *
 * @property GridFieldDetailForm_ItemRequest $owner
 */
class GridFieldDetailFormItemRequestExtension extends \SilverStripe\Core\Extension
{
    private static $allowed_actions = [
        'sendTicketEmail'
    ];

    public function updateItemEditForm(Form $form)
    {
        if (($record = $this->owner->getRecord()) && $record instanceof Reservation) {
            $connectionActions = CompositeField::create()->setName('ConnectionActions');
            $connectionActions->setFieldHolderTemplate(CompositeField::class . '_holder_buttongroup');

            $action = FormAction::create('sendTicketEmail', _t(__CLASS__ . '.SendTicketEmail', 'Resend ticket mail'))
                ->addExtraClass('grid-print-button btn btn-outline-secondary font-icon-p-mail')
                ->setAttribute('data-icon', 'p-mail')
                ->setUseButtonTag(true);

            $connectionActions->push($action);
            $form->Actions()->insertBefore('MajorActions', $connectionActions);
        }
    }

    public function sendTicketEmail()
    {
        /** @var Reservation $record */
        if (($record = $this->owner->getRecord()) && $record instanceof Reservation) {
            $record->send();
        }
    }
}
