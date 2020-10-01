<?php

namespace SilverCart\ProductWizard\Extensions\Model\Order;

use SilverCart\Model\Order\OrderPosition;
use SilverCart\Model\Order\ShoppingCartPosition;
use SilverStripe\ORM\DataExtension;

/**
 * Extension for SilverCart Order.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Extensions\Model\Order
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.09.2020
 * @copyright 2020 pixeltricks GmbH
 * @license see license file in modules root directory
 * 
 * @property \SilverCart\Model\Order\Order $owner Owner
 */
class OrderExtension extends DataExtension
{
    /**
     * Resets product wizard data while converting shopping cart positions after 
     * placing an order.
     * 
     * @param ShoppingCartPosition $shoppingCartPosition Shopping cart position
     * @param OrderPosition        $orderPosition        Order position
     * 
     * @return void
     */
    public function onAfterConvertSingleShoppingCartPositionToOrderPosition(ShoppingCartPosition $shoppingCartPosition, OrderPosition $orderPosition) : void
    {
        if ($shoppingCartPosition->ProductWizard()->exists()) {
            $shoppingCartPosition->ProductWizard()->resetPostVars();
            $shoppingCartPosition->ProductWizard()->resetCurrentStep();
        }
    }
}
