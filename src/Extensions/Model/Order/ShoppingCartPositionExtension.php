<?php

namespace SilverCart\ProductWizard\Extensions\Model\Order;

use SilverCart\Model\Customer\Customer;
use SilverCart\Model\Order\ShoppingCart;
use SilverCart\Model\Order\ShoppingCartPosition;
use SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Member;

/**
 * Extension for SilverCart ShoppingCartPosition.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Extensions\Model\Order
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.09.2020
 * @copyright 2020 pixeltricks GmbH
 * @license see license file in modules root directory
 * 
 * @property ShoppingCartPosition $owner Owner
 */
class ShoppingCartPositionExtension extends DataExtension
{
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $has_one = [
        'ProductWizard' => ProductWizardStepPage::class,
    ];
    
    /**
     * Returns the position matching with the given $positionData previously added by the given $wizard.
     * 
     * @param array                 $positionData Position data to get position for
     * @param ProductWizardStepPage $wizard       Product wizard to get position for
     * 
     * @return ShoppingCartPosition|null
     */
    public static function getWizardPosition(array $positionData, ProductWizardStepPage $wizard) : ?ShoppingCartPosition
    {
        $position = null;
        $member   = Customer::currentUser();
        if ($member instanceof Member) {
            $cart = $member->getCart();
            if ($cart instanceof ShoppingCart
             && $cart->exists()
            ) {
                $position = $cart->ShoppingCartPositions()->filter([
                    'ProductWizardID' => $wizard->ID,
                    'ProductID'       => (int) $positionData['productID'],
                ])->first();
            }
        }
        return $position;
    }
    
    /**
     * Deletes the positions belonging to the given $wizard.
     * If the $excludeIDs ID list is given, all containing IDs won't be deleted.
     * 
     * @param ProductWizardStepPage $wizard     Product wizard to delete positions for
     * @param array                 $excludeIDs IDs to exclude from deletion
     * 
     * @return void
     */
    public static function deleteWizardPositions(ProductWizardStepPage $wizard, array $excludeIDs = []) : void
    {
        $member   = Customer::currentUser();
        if ($member instanceof Member) {
            $cart = $member->getCart();
            if ($cart instanceof ShoppingCart
             && $cart->exists()
            ) {
                if (empty($excludeIDs)) {
                    $positions = $cart->ShoppingCartPositions()->filter([
                        'ProductWizardID' => $wizard->ID,
                    ]);
                } else {
                    $positions = $cart->ShoppingCartPositions()->filter([
                        'ProductWizardID' => $wizard->ID,
                    ])->exclude([
                        'ID' => $excludeIDs,
                    ]);
                }
                foreach ($positions as $position) {
                    $position->delete();
                }
            }
        }
    }
}