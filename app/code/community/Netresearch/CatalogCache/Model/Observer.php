<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Observer that handles clearance of product cache if it was sold
 *
 * @category   Netresearch
 * @package    Netresearch_CatalogCache
 * @author     Netresearch <info@netresearch.de>
 */
class Netresearch_CatalogCache_Model_Observer
{
    public function clearProductCache($observer)
    {
        return;
        
        $_product = $observer['item']->getProduct();
        if(trim(Mage::getStoreConfig('catalog/frontend/refresh_cache_when_stock_is')) == "") {
            $_product->cleanCache();
            return;
        }
        $_currentStock = $_product->getStockItem()->getQty();
        $_futureStock = $_currentStock - $observer['item']->getQty();
        $stocks = explode(',',','.Mage::getStoreConfig('catalog/frontend/refresh_cache_when_stock_is'));
        foreach($stocks as $stock) {
            $stock = trim($stock);
            if(
                $stock &&
                $_currentStock > $stock && $_futureStock <= $stock
            ) {
                $_product->cleanCache();
                return;
            }
        }
    }

    /**
     * Clean review-related product cache
     * 
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function cleanProductCache($observer)
    {
        return;

        $review = $observer->getEvent()->getObject();
        $productId = $review->getEntityPkValue();
        $product = Mage::getModel('catalog/product')->load(
            $productId
        );

        if ($product instanceof Mage_Catalog_Model_Product
            && $product->getId()) {
            $product->cleanCache();
        }
    }
}
