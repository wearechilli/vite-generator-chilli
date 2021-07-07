<?php
/**
 * PickupRules module for Craft CMS 3.x
 *
 * Pickup Validtion Rules for Commerce
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace modules\pickuprulesmodule\variables;

use craft\commerce\Plugin;
use modules\pickuprulesmodule\PickupRulesModule;

use Craft;
use DateInterval;
use DatePeriod;
use DateTime;

/**
 * PickupRules Variable
 *
 * Craft allows modules to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.pickupRulesModule }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Sten Van den Bergh
 * @package   PickupRulesModule
 * @since     1.0.0
 */
class PickupRulesModuleVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Get the intersect between multiple pickup dates rules
     *
     * @param null $arr
     * @return array
     */
    public function arrayIntersect($arr = null)
    {
        $list = [];
        $openingHoursPerDay = [];

        foreach ($arr as $sku) {
            $days = [];
            foreach ($sku as $rule) {
                array_push($days, $rule['day']);
                $openingHoursPerDay[$rule['day']] = $rule['openingHours'];
            }

            if (!empty($days)) {
                array_push($list, $days);
            }
        }

        if (count($list) > 1) {
            $uniqueDays = call_user_func_array('array_intersect', $list);

            // When we're here, there are multiple pickup restrictions applicable.
            // Rebuild the array based on the intersection of all the rules
            $intersect = [];
            foreach ($uniqueDays as $day) {
                $rule = [];
                $rule['day'] = $day;
                $rule['openingHours'] = $openingHoursPerDay[$day];
                array_push($intersect, $rule);
            }
            return $intersect;
        }

        if (count($list) === 1) {
            return reset($arr);
        }

        return false;
    }

    // Remove any dates that are too soon
    public function removeInvalidDates($dates)
    {
        $now = new DateTime();
        $today = new DateTime();
        $today->setTime( 0, 0, 0 );

        // Remove all dates before or equal than today
        foreach($dates as $i => $date) {
            if ($date <= $today) {
                unset($dates[$i]);
            }
        }

        // If ordered between saturday 16:00 and monday, the first order can only be wednesday
        if (
            $dates &&
            ((int)$today->diff(reset($dates))->format('%R%a') <= 3) && ( // Only skip one day if the first 2 dates are consecutive i.e. no more than 2 days difference
            in_array($today->format('w'), [0, 1])
            || ((int)$today->format('w') === 6 && (int)$now->format('H') >= 16))
        ) {
            // Remove the first dates in case they are sunday, monday or tuesday
            while(reset($dates)->format('w') < 3) {
                array_shift($dates);
            }
        }

        // Check the next day and current time and optionally skip the next day
        if ($dates) {
            $diff = $today->diff(reset($dates))->format('%R%a');
            if ($dates && (int)$now->format('H') >= 16 && $diff === '+1') {
                array_shift($dates);
            }
        }

        return $dates;
    }

    public function locationPickupTimes() {
        // Get this shop's pickup points
        $locations = PickupRulesModule::$instance->pickupRulesModuleService->getLocations();

        // Get a list of dates to match against each pickup points opening hours
        // First, check if the current cart has any pickup day restrictions, if so use them
        $pickupDays = [];
        $restrictedPickupDays = PickupRulesModule::$instance->pickupRulesModuleService->getCartRestrictions();

        if (empty($restrictedPickupDays['pickupAllowed'])) {
            // No restrictions apply, get a range between tomorrow and the next 31 days
            $startDate = date_modify(new DateTime(date('m/d/Y')), '+ 1 day');
            $endDate = date_modify(new DateTime(date('m/d/Y')), '+ 31 days');
            $pickupDaysPeriod = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);
            // Convert it to an array
            foreach($pickupDaysPeriod as $d) {
                array_push($pickupDays, $d);
            }
        } else {
            // Keep an original reference to $restrictedPickupDays
            $pickupDays = $restrictedPickupDays['pickupAllowed'];
        }

        // Loop over each location, map its opening hours against our list of available $pickupdays
        $locationsPickupTimes = [];

        foreach($locations as $item) {
            // For each location, set its pickup days in one big array
            $currentOpts = [];

            // Remove any restricted pickup days
            foreach($restrictedPickupDays['pickupDisallowed'] as $disallowed) {
                foreach ($pickupDays as $i => $day) {
                    if ($disallowed->format('U') === $day->format('U')) {
                        unset($pickupDays[$i]);
                    }
                }
            }

            $pickupDays = $this->removeInvalidDates($pickupDays);

            foreach($pickupDays as $d) {
                // day = the row in our table field
                $day = array_filter($item->pickupTimes, function($row) use ($d) {
                    return Craft::t('site', strtolower($row['day']), [], 'en') === Craft::t('site', strtolower($d->format('l')), [], 'en');
                });

                if (!empty($day)) {
                    $day = reset($day);
                }

                // i.e. 2; e.g. the 2nd monday in this month
                $nthDayThisMonth = ceil($d->format('j') / 7);

                // i.e. 5; e.g. there are 5 mondays in this month
                $writtenDay = Craft::t('site', $d->format('l'), [], 'en');
                $writtenMonth = Craft::t('site', $d->format('F'), [], 'en');
                $writtenYear = $d->format('Y');
                $totalDaysThisMonth = new DateTime("last {$writtenDay} of {$writtenMonth} {$writtenYear}");
                $totalDaysThisMonth = ceil($totalDaysThisMonth->format('j') / 7);

                // Match this day with the one in our pickup times field and check if it is open
                if (in_array($d->format('U'), array_map(function($row) {
                        return $row['date']->format('U');
                    }, $item->openingDays ?? [])) // => In the special opening hours
                    || ($day // In the regular opening hours
                    && $day['open']
                    && $day['close']
                    && (
                        (array_key_exists("day{$nthDayThisMonth}", $day) and $day["day{$nthDayThisMonth}"])
                        || ($nthDayThisMonth == $totalDaysThisMonth && $day['dayLast'])
                        || ($nthDayThisMonth == $totalDaysThisMonth - 1 && $day['daySecondLast'])
                    ))) {
                    // We're open during normal opening hours,
                    // But now we need to check against special closing days

                    if (empty($item->closingDays) || (!empty($item->closingDays) && !in_array($d->format('U'), array_map(function($row) {
                        if ($row['date']) {
                            return $row['date']->format('U');
                        }
                    }, $item->closingDays)))) {
                        // We're open! But for how long?

                        $hours = [];

                        // Get start & end hour from regular opening hours or special opening hours
                        if ($day['open'] && $day['close']) {
                            // The shop is open, get its opening hours
                            $open = $day['open']->format('U');
                            $close = $day['close']->format('U');
                        } else {
                            // The shop is not open, check it's special opening hours
                            $specialOpen = array_filter($item->openingDays, function ($row) use ($d) {
                                return $row['date']->format('U') === $d->format('U');
                            });

                            if (!empty($specialOpen)) {
                                $specialOpen = reset($specialOpen);
                                $openDate = $specialOpen['open'] ?? new DateTime('today 10:00'); // Fallback time
                                $closeDate = $specialOpen['close'] ?? new DateTime('today 18:15'); // Fallback time
                                $open = $openDate->format('U');
                                $close = $closeDate->format('U');
                            }
                        }

                        // Check if we have opening hours
                        if (isset($open) && isset($close)) {
                            foreach (range($open, $close, 1800) as $interval) {
                                array_push($hours, date('H:i:s', $interval));
                            }
                        } else {
                            // Add default opening hours if none were found?
                        }

                        array_push($currentOpts, [
                            'day' => $d->format(DATE_ATOM),
                            'openingHours' => $hours
                        ]);
                    }
                }
            }

            $locationsPickupTimes[$item->id] = array_reverse($currentOpts);
        }

        // Craft::dd($locationsPickupTimes);
        return $locationsPickupTimes;
    }

    public function validPickupDate()
    {
        $locationsPickupTimes = $this->locationPickupTimes();
        // $cart = Plugin::getInstance()->getCarts()->getCart();
        $cart = Plugin::getInstance()->getCarts()->getCart();
        $cartPickupDate = $cart->pickupDate;

        if (!$cartPickupDate) {
            return false;
        }

        $isValid = false;

        foreach($locationsPickupTimes as $location) {
            foreach($location as $days) {
                if ($cartPickupDate->format(DATE_ATOM) === $days['day']) {
                    $isValid = true;
                }
            }
        }

        return $isValid;
    }
}
