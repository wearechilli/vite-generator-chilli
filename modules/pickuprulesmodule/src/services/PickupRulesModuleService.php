<?php
/**
 * PickupRules module for Craft CMS 3.x
 *
 * Pickup Validtion Rules for Commerce
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace modules\pickuprulesmodule\services;

use craft\commerce\Plugin;
use craft\elements\Entry;
use modules\pickuprulesmodule\PickupRulesModule;

use Craft;
use craft\base\Component;
use DateTime;

/**
 * PickupRulesModuleService Service
 *
 * All of your module’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other modules can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Sten Van den Bergh
 * @package   PickupRulesModule
 * @since     1.0.0
 */
class PickupRulesModuleService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin/module file, call it like this:
     *
     *     PickupRulesModule::$instance->pickupRulesModuleService->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';

        return $result;
    }

    public function getLocations()
    {
        $locations = Entry::find()
            ->section('locations')
            ->all();

        return $locations;
    }

    public function getCartRestrictions() {
        $cart = Plugin::getInstance()->getCarts()->getCart();
        $result = [];
        $pickupAllowed = [];
        $pickupDisallowed = [];

        foreach ($cart->lineItems as $item) {
            $pickupRules = Entry::find()
                ->section('pickupRules')
                ->relatedTo([
                    'or',
                    ['targetElement' => $item->purchasable->product],
                    ['targetElement' => $item->purchasable->product->shopCategory->one() ?? []]
                ])
                ->all();

            foreach ($pickupRules as $pickupRule) {

                $currentOptsAllowed = [];
                $currentOptsDisallowed = [];

                if (!empty($pickupRule->pickupAllowed)) {
                    foreach ($pickupRule->pickupAllowed as $day) {
                        if (!empty($day['date'])) {
                            array_push($currentOptsAllowed, $day['date']->format(DATE_ATOM));
                        }
                    }
                    $pickupAllowed[$item->sku] = $currentOptsAllowed;
                }

                if (!empty($pickupRule->pickupDisallowed)) {
                    foreach ($pickupRule->pickupDisallowed as $day) {
                        if (!empty($day['date'])) {
                            array_push($currentOptsDisallowed, $day['date']->format(DATE_ATOM));
                        }
                    }
                    $pickupDisallowed[$item->sku] = $currentOptsDisallowed;
                }
            }
        }

        $result['pickupAllowed'] = $pickupAllowed;
        $result['pickupDisallowed'] = $pickupDisallowed;

        // Cleanup the results

        /* if (empty($pickupAllowed)) {
            return false;
        } */

        // Craft::dd($result);

        if (count($result['pickupAllowed']) === 1) {
            $result['pickupAllowed'] = reset($result['pickupAllowed']);
        } elseif (count($result['pickupAllowed']) > 1) {
            // Get intersect
            $result['pickupAllowed'] = $this->pickupDaysIntersect($result['pickupAllowed']);
        }

        if (count($result['pickupDisallowed']) === 1) {
            $result['pickupDisallowed'] = reset($result['pickupDisallowed']);
        } elseif (count($result['pickupDisallowed']) > 1) {
            // Remove doubles
            $result['pickupDisallowed'] = $this->pickupDaysUnique($result['pickupDisallowed']);
        }

        // Craft::dd($result);

        // Convert the date strings back to DateTime() objects
        if ($result['pickupAllowed']) {
            $result['pickupAllowed'] = array_map(function ($day) {
                return new DateTime($day);
            }, $result['pickupAllowed']);
        }

        if ($result['pickupDisallowed']) {
            $result['pickupDisallowed'] = array_map(function ($day) {
                return new DateTime($day);
            }, $result['pickupDisallowed']);
        }

        return $result;
    }

    private function pickupDaysIntersect($arr) {
        $list = [];

        foreach ($arr as $sku) {
            $days = [];
            foreach ($sku as $day) {
                array_push($days, $day);
            }

            if (!empty($days)) {
                array_push($list, $days);
            }
        }

        if (count($list) > 1) {
            $uniqueDays = call_user_func_array('array_intersect', $list);

            return $uniqueDays;
        }

        if (count($list) === 1) {
            return reset($list);
        }

        return false;
    }

    private function pickupDaysUnique($arr)
    {
        $days = [];

        foreach ($arr as $sku) {
            foreach ($sku as $day) {
                array_push($days, $day);
            }
        }

        return array_unique($days);
    }
}
