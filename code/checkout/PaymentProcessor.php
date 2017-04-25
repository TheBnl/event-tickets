<?php
/**
 * PaymentProcessor.php
 *
 * @author Bram de Leeuw
 * Date: 24/03/17
 */

namespace Broarm\EventTickets;

use Object;
use Payment;
use SilverStripe\Omnipay\Exception\Exception;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\Omnipay\Service\ServiceFactory;
use SilverStripe\Omnipay\Service\ServiceResponse;

/**
 * Class PaymentProcessor
 *
 * @package Broarm\EventTickets
 */
class PaymentProcessor extends Object
{
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
        // fixme: dependent on configured gateway. add trough extension .. ?
        'description' => 'test'
    );

    /**
     * PaymentProcessor constructor.
     *
     * @param Reservation $reservation
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        parent::__construct();
    }

    /**
     * Create a payment trough the given payment gateway
     *
     * @param string $gateway
     *
     * @return Payment
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
     * @return null|ServiceResponse
     */
    public function createServiceFactory()
    {
        $factory = ServiceFactory::create();
        $service = $factory->getService($this->payment, ServiceFactory::INTENT_PAYMENT);

        try {
            $serviceResponse = $service->initiate($this->getGatewayData());
        } catch (Exception $ex) {
            // error out when an exception occurs
            user_error($ex->getMessage(), E_USER_WARNING);
            return null;
        }

        return $serviceResponse;
    }

    /**
     * Set and merges the gateway data
     *
     * @param array $data
     */
    public function setGatewayData($data = array())
    {
        array_merge($data, $this->gatewayData);
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
}
