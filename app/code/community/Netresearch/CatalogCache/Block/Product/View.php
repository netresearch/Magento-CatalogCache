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
 * Rewrite of Product view to enable caching
 *
 * replace this parent class by your inhereted version of th Product_View Block
 * e.g. class Netresearch_CatalogCache_Block_Product extends MyNameSpace_MyModule_Catalog_Block_Product_View
 *
 * @category   Netresearch
 * @package    Netresearch_CatalogCache
 * @author     Netresearch <info@netresearch.de>
 */
class Netresearch_CatalogCache_Block_Product_View extends Mage_Catalog_Block_Product_View
{
    protected function _isCacheActive()
    {
        if (!Mage::getStoreConfig('catalog/frontend/cache_view')) {
            return false;
        }

        /* if there are any messages dont read from cache to show them */
        if (0 < Mage::getSingleton('core/session')->getMessages(true)->count()
            || $this->getMessagesBlock()->getMessages()
        ) {
            return false;
        }

        return true;
    }

    public function getCacheLifetime()
    {
        if ($this->_isCacheActive()) {
            return 60 * 30; //30min
        }
    }

    public function getCacheKey()
    {
        if (!$this->_isCacheActive()) {
            return parent::getCacheKey();
        }
        $_taxCalculator = Mage::getModel('tax/calculation');
        $_customer = Mage::getSingleton('customer/session')->getCustomer();
        $_product = $this->getProduct();
        return 'ProductView'.
            /* Create different caches for different products */
            $_product->getId().'_'.
            /* ... for different stores */
            Mage::App()->getStore()->getCode().'_'.
            /* ... currency */
            Mage::App()->getStore()->getCurrentCurrencyCode().'_'.
            /* ... for different login state */
            $this->helper('customer')->isLoggedIn().'_'.
            /* ... for different customer groups */
            $_customer->getGroupId().'_'.
            /* ... for different tax classes (related to customer and product) */
            $_taxCalculator->getRate(
                $_taxCalculator
                ->getRateRequest()
                ->setProductClassId($_product->getTaxClassId())
            ).'_';
    }


    public function getCacheTags()
    {
        if (!$this->_isCacheActive()) {
            return parent::getCacheTags();
        }
        return array(
            Mage_Catalog_Model_Product::CACHE_TAG,
            Mage_Catalog_Model_Product::CACHE_TAG."_".$this->getProduct()->getId()
        );
    }
}
