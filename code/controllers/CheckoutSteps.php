<?php
/**
 * CheckoutSteps.php
 *
 * @author Bram de Leeuw
 * Date: 29/03/17
 */

namespace Broarm\EventTickets;

use ArrayList;
use Controller;
use Object;
use ViewableData;

class CheckoutSteps extends Object
{
    private static $checkout_steps = array(
        'register',
        'summary',
        'success'
    );

    /**
     * Return the first step
     *
     * @return string
     */
    public static function start()
    {
        $steps = self::getSteps();
        return $steps[0];
    }

    /**
     * Return the next step
     *
     * @param $step
     *
     * @return null
     */
    public static function nextStep($step)
    {
        $steps = self::getSteps();
        $key = self::getStepIndex($step) + 1;
        if (key_exists($key, $steps)) {
            return $steps[$key];
        } else {
            return null;
        }
    }

    /**
     * Return an unique array of steps
     *
     * @return array
     */
    public static function getSteps()
    {
        return array_unique(self::config()->get('checkout_steps'));
    }

    /**
     * Get the formatted steps
     *
     * @param CheckoutStepController $controller
     *
     * @return ArrayList
     */
    public static function get(CheckoutStepController $controller)
    {
        $list = new ArrayList();
        $steps = self::getSteps();
        foreach ($steps as $step) {
            $data = new ViewableData();
            $data->Link = $controller->Link($step);
            $data->Title = _t("CheckoutSteps.$step", ucfirst($step));
            $data->InPast = self::inPast($step, $controller);
            $data->InFuture = self::inFuture($step, $controller);
            $data->Current = self::current($step, $controller);
            $list->add($data);
        }

        return $list;
    }

    /**
     * Get the index of the given step
     *
     * @param $step
     *
     * @return mixed
     */
    private static function getStepIndex($step) {
        $steps = self::getSteps();
        return array_search($step, array_unique($steps));
    }

    /**
     * Check if the step is in the past
     *
     * @param                        $step
     * @param CheckoutStepController $controller
     *
     * @return bool
     */
    private static function inPast($step, CheckoutStepController $controller)
    {
        $currentStep = $controller->getURLParams()['Action'];
        return self::getStepIndex($step) < self::getStepIndex($currentStep);
    }

    /**
     * Check if the step is in the future
     *
     * @param                        $step
     * @param CheckoutStepController $controller
     *
     * @return bool
     */
    private static function inFuture($step, CheckoutStepController $controller)
    {
        $currentStep = $controller->getURLParams()['Action'];
        return self::getStepIndex($step) > self::getStepIndex($currentStep);
    }

    /**
     * Check at the current step
     *
     * @param                        $step
     * @param CheckoutStepController $controller
     *
     * @return bool
     */
    private static function current($step, CheckoutStepController $controller)
    {
        $currentStep = $controller->getURLParams()['Action'];
        return self::getStepIndex($step) === self::getStepIndex($currentStep);
    }
}