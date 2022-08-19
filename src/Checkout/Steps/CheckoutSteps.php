<?php

namespace Broarm\EventTickets\Checkout\Steps;

use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class CheckoutSteps
{
    use Configurable;
    use Extensible;

    private static $checkout_steps = [];

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
     * Return the previous step
     *
     * @param $step
     *
     * @return null
     */
    public static function prevStep($step)
    {
        $steps = self::getSteps();
        $key = self::getStepIndex($step) - 1;
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
        return self::config()->get('checkout_steps');
    }

    /**
     * Get the formatted steps
     *
     * @param RequestHandler $controller
     *
     * @return ArrayList
     */
    public static function get(RequestHandler $controller)
    {
        $list = [];
        $steps = array_unique(self::getSteps());
        foreach ($steps as $step) {
            $title = _t(__CLASS__ . ".$step", ucfirst($step));
            $current = self::current($step, $controller);
            if (!count($list) || !isset($list[$title])) {
                $list[$title] = [
                    'Link' => $controller->Link($step),
                    'Title' => _t(__CLASS__ . ".$step", ucfirst($step)),
                    'InPast' => self::inPast($step, $controller),
                    'InFuture' => self::inFuture($step, $controller),
                    'Current' => $current, // todo check if has two steps
                    'Step' => $step
                ];
            } elseif (!$list[$title]['Current']) {
                $list[$title]['Current'] = $current;
            }
        }

        return new ArrayList($list);
    }

    /**
     * Get the index of the given step
     *
     * @param $step
     *
     * @return mixed
     */
    public static function getStepIndex($step)
    {
        $steps = self::getSteps();
        return array_search($step, array_unique($steps));
    }

    /**
     * Check if the step is in the past
     *
     * @param                        $step
     * @param RequestHandler $controller
     *
     * @return bool
     */
    private static function inPast($step, RequestHandler $controller)
    {
        $currentStep = $controller->getRequest()->param('Action');
        return self::getStepIndex($step) < self::getStepIndex($currentStep);
    }

    /**
     * Check if the step is in the future
     *
     * @param                        $step
     * @param RequestHandler $controller
     *
     * @return bool
     */
    private static function inFuture($step, RequestHandler $controller)
    {
        $currentStep = $controller->getRequest()->param('Action');
        return self::getStepIndex($step) > self::getStepIndex($currentStep);
    }

    /**
     * Check at the current step
     *
     * @param                        $step
     * @param RequestHandler $controller
     *
     * @return bool
     */
    private static function current($step, RequestHandler $controller)
    {
        $currentStep = $controller->getRequest()->param('Action');
        return self::getStepIndex($step) === self::getStepIndex($currentStep);
    }
}
