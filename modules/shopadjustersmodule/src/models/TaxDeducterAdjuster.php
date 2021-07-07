<?php

namespace modules\shopadjustersmodule\models;

use Craft;
use craft\base\Component;
use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use craft\commerce\helpers\Currency;
use craft\commerce\models\OrderAdjustment;
use craft\commerce\services\Currencies;

class TaxDeducterAdjuster extends Component implements AdjusterInterface
{
    public function adjust(Order $order): array
    {
        $adjustments = [];

        if (!empty($order->shippingAddress)) {
            $validBusinessTaxId = Craft::$app->getCache()->exists('commerce:validVatId:' . $order->shippingAddress->businessTaxId ?? '');
        } else {
            $validBusinessTaxId = false;
        }

        if ($validBusinessTaxId && $order->shippingAddress->countryIso !== 'BE') {
            $amount = 0;
            foreach ($order->getLineItems() as $item) {
                foreach($item->getTaxCategory()->getTaxRates() as $rate) {
                    $amount += $item->subtotal - ($item->subtotal / (1 + $rate->rate));
                }
            }

            if ($order->getTotalShippingCost() > 0) {
                $shippingCostTax = ($order->getTotalShippingCost() - ($order->getTotalShippingCost() / (1 + 0.21)));
                $amount += $shippingCostTax;
            }

            $adjustment = new OrderAdjustment;
            $adjustment->type = 'taxDeducter';
            $adjustment->name = 'BTW Verwijderd';
            $adjustment->description = 'Geen BTW voor geldige bedrijven buiten België';
            $adjustment->sourceSnapshot = ['taxDeducter' => 'yes'];
            $adjustment->amount = Currency::round($amount * -1);
            $adjustment->setOrder($order);
            $adjustment->setLineItem($item);

            $adjustments[] = $adjustment;

        }

        return $adjustments;
    }
}
