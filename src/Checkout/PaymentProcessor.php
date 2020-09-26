<?php

namespace Broarm\EventTickets\Checkout;

use Broarm\EventTickets\Model\Reservation;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Omnipay\Exception\InvalidConfigurationException;
use SilverStripe\Omnipay\Exception\InvalidStateException;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\ServiceFactory;
use SilverStripe\Omnipay\Service\ServiceResponse;

/**
 * Class PaymentProcessor
 *
 * @package Broarm\EventTickets
 */
class PaymentProcessor
{
    use Configurable;
    use Extensible;

    /**
     * @config
     * @var string
     */
    private static $currency = 'EUR';

    /**
     * @var Reservation
     */
    protected $reservation;

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @var array
     */
    protected $gatewayData = array(
        'transactionId' => null,
        'firstName' => null,
        'lastName' => null,
        'email' => null,
        'company' => null,
        'billingAddress1' => null,
        'billingAddress2' => null,
        'billingCity' => null,
        'billingPostcode' => null,
        'billingState' => null,
        'billingCountry' => null,
        'billingPhone' => null,
        'shippingAddress1' => null,
        'shippingAddress2' => null,
        'shippingCity' => null,
        'shippingPostcode' => null,
        'shippingState' => null,
        'shippingCountry' => null,
        'shippingPhone' => null,
        'description' => null
    );

    /**
     * PaymentProcessor constructor.
     *
     * @param Reservation $reservation
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        $this->setGatewayData(array(
            'transactionId' => $reservation->Status,
            'firstName' => $reservation->MainContact()->FirstName,
            'lastName' => $reservation->MainContact()->Surname,
            'email' => $reservation->MainContact()->Email,
            'description' => $reservation->ReservationCode
        ));

        $this->extend('updatePaymentProcessor');
    }

    /**
     * Create a payment trough the given payment gateway
     *
     * @param $gateway
     * @return Payment
     * @throws InvalidConfigurationException
     */
    public function createPayment($gateway)
    {
        if (!GatewayInfo::isSupported($gateway)) {
            user_error(_t(
                "PaymentProcessor.INVALID_GATEWAY",
                "`{gateway}` is not supported.",
                null,
                array('gateway' => (string)$gateway)
            ), E_USER_ERROR);
        }

        // Create a payment
        $this->payment = Payment::create()->init(
            $gateway,
            $this->reservation->Total,
            self::config()->get('currency')
        );

        // Set a reference to the reservation
        $this->payment->ReservationID = $this->reservation->ID;

        return $this->payment;
    }

    /**
     * Create the service factory
     * Catch any exceptions that might occur
     *
     * @return ServiceResponse
     * @throws InvalidConfigurationException
     * @throws InvalidStateException
     */
    public function createServiceFactory()
    {
        $factory = ServiceFactory::create();
        $service = $factory->getService($this->payment, ServiceFactory::INTENT_PAYMENT);
        return $service->initiate($this->getGatewayData());
    }

    /**
     * Set and merges the gateway data
     *
     * @param array $data
     */
    public function setGatewayData($data = array())
    {
        $this->gatewayData = array_merge($this->gatewayData, $data);
    }

    /**
     * Get the gateway data
     *
     * @return array
     */
    public function getGateWayData()
    {
        return $this->gatewayData;
    }

    /**
     * Get the reservation
     *
     * @return Reservation
     */
    public function getReservation()
    {
        return $this->reservation;
    }
}
