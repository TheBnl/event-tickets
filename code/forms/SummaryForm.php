<?php
/**
 * SummaryForm.php
 *
 * @author Bram de Leeuw
 * Date: 10/03/17
 */

namespace Broarm\EventTickets;

use Config;
use FieldList;
use FormAction;
use Payment;
use SilverStripe\Omnipay\GatewayFieldsFactory;
use SilverStripe\Omnipay\Service\ServiceFactory;
use TextareaField;
use TextField;

/**
 * Class SummaryForm
 *
 * @package Broarm\EventTickets
 */
class SummaryForm extends FormStep
{
    /**
     * @var Reservation
     */
    protected $reservation;

    public function __construct($controller, $name, Reservation $reservation)
    {
        $fields = FieldList::create(
            SummaryField::create('Summary', '', $this->reservation = $reservation, true),
            TextareaField::create('Comments', _t('SummaryForm.COMMENTS', 'Comments')),
            PaymentGatewayField::create()
        );

        $actions = FieldList::create(
            FormAction::create('makePayment', _t('ReservationForm.PAYMENT', 'Continue to payment'))
        );

        // Update the summary form with extra fields
        $this->extend('updateSummaryForm');
        
        parent::__construct($controller, $name, $fields, $actions);
    }

    /**
     * Handle the ticket form registration
     *
     * @param array       $data
     * @param SummaryForm $form
     *
     * @return string
     */
    public function makePayment(array $data, SummaryForm $form)
    {
        // If the summary is changed and email receivers are set
        if (isset($data['Summary']) && is_array($data['Summary'])) {
            foreach ($data['Summary'] as $attendeeID => $fields) {
                $attendee = $form->reservation->Attendees()->find('ID', $attendeeID);
                foreach ($fields as $field => $value) {
                    $attendee->setField($field, $value);
                }
                $attendee->write();
            }
        }

        // If comments are added
        if (isset($data['Comments'])) {
            $form->reservation->Comments = $data['Comments'];
        }

        // Hook trough where optional extra field data can be saved on the reservation
        $this->extend('updateReservationBeforePayment', $form->reservation, $data, $form);

        // If there is need to check out make a payment
        if ($form->reservation->Total > 0) {
            $form->reservation->changeState('PENDING');
            $form->reservation->write();

            $paymentProcessor = PaymentProcessor::create($this->reservation);
            $paymentProcessor
                ->createPayment($data['Gateway'])
                ->setSuccessUrl($this->getController()->Link($this->nextStep))
                ->setFailureUrl($this->getController()->Link())
                ->write();

            $paymentProcessor->setGateWayData(array(
                'transactionId' => $this->reservation->ReservationCode
            ));

            $response = $paymentProcessor->createServiceFactory();

            $this->extend('beforeNextStep', $data, $form, $response);
            return $response->redirectOrRespond();
        }

        // else go straight to success
        else {
            $form->reservation->changeState('PENDING');
            $form->reservation->write();

            $this->extend('beforeNextStep', $data, $form);
            return $this->nextStep();
        }
    }


}