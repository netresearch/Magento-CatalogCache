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
 * Rewrite of Product list to enable caching
 *
 * @category   Netresearch
 * @package    Netresearch_CatalogCache
 * @author     Netresearch <info@netresearch.de>
 */
class Netresearch_CatalogCache_Block_Product_List extends Mage_Catalog_Block_Product_List
{
    protected function _isCacheActive()
    {
        if (!Mage::getStoreConfig('catalog/frontend/cache_list')) {
            return false;
        }

        /* if there are any messages dont read from cache to show them */
        if (Mage::getSingleton('core/session')->getMessages(true)->count() > 0) {
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
            parent::getCacheKey();
        }
        $_taxRateRequest = Mage::getModel('tax/calculation')->getRateRequest();
        $_customer = Mage::getSingleton('customer/session')->getCustomer();
        $this->_category = Mage::getSingleton('catalog/layer')->getCurrentCategory();
        $_page = $this->getPage();

        $toolbar = new Mage_Catalog_Block_Product_List_Toolbar();
        $cacheKey = 'ProductView_'.
            /* Create different caches for different categories */
            $this->_category->getId().'_'.
            /* ... orders */
            $toolbar->getCurrentOrder().'_'.
            /* ... direction */
            $toolbar->getCurrentDirection().'_'.
            /* ... mode */
            $toolbar->getCurrentMode().'_'.
            /* ... page */
            $toolbar->getCurrentPage().'_'.
            /* ... items per page */
            $toolbar->getLimit().'_'.
            /* ... stores */
            Mage::App()->getStore()->getCode().'_'.
            /* ... currency */
            Mage::App()->getStore()->getCurrentCurrencyCode().'_'.
            /* ... customer groups */
            $_customer->getGroupId().'_'.
            $_taxRateRequest->getCountryId()."_".
            $_taxRateRequest->getRegionId()."_".
            $_taxRateRequest->getPostcode()."_".
            $_taxRateRequest->getCustomerClassId()."_".
            /* ... tags */
            Mage::registry('current_tag').'_'.
            '';
        /* ... layern navigation + search */
        foreach (Mage::app()->getRequest()->getParams() as $key=>$value) {
            $cacheKey .= $key.'-'.$value.'_';
        }
        return $cacheKey;
    }


    public function getCacheTags()
    {
        if (!$this->_isCacheActive()) {
            return parent::getCacheTags();
        }
        $cacheTags = array(
            Mage_Catalog_Model_Category::CACHE_TAG,
            Mage_Catalog_Model_Category::CACHE_TAG.'_'.$this->_category->getId()
        );
        foreach ($this->_getProductCollection() as $_product) {
            $cacheTags[] = Mage_Catalog_Model_Product::CACHE_TAG."_".$_product->getId();
        }
        return $cacheTags;

    }
}

