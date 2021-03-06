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
 * @category    Mage
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer Shared Wishlist Rss Block
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rss_Block_Wishlist extends Mage_Wishlist_Block_Abstract
{
    /**
     * Customer instance
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    /**
     * Retrieve Wishlist model
     *
     * @return Mage_Wishlist_Model_Wishlist
     */
    protected function _getWishlist()
    {
        if (is_null($this->_wishlist)) {
            $this->_wishlist = Mage::getModel('wishlist/wishlist');
            if ($this->_getCustomer()->getId()) {
                $this->_wishlist->loadByCustomer($this->_getCustomer());
            }
        }
        return $this->_wishlist;
    }

    /**
     * Retrieve Customer instance
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        if (is_null($this->_customer)) {
            $this->_customer = Mage::getModel('customer/customer');

            $params = Mage::helper('core')->urlDecode($this->getRequest()->getParam('data'));
            $data   = explode(',', $params);
            $cId    = abs(intval($data[0]));
            if ($cId) {
                $this->_customer->load($cId);
            }
        }

        return $this->_customer;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        /* @var $rssObj Mage_Rss_Model_Rss */
        $rssObj = Mage::getModel('rss/rss');

        if ($this->_getWishlist()->getId()) {
            $newUrl = Mage::getUrl('wishlist/shared/index', array(
                'code'  => $this->_getWishlist()->getSharingCode()
            ));

            $title  = Mage::helper('rss')->__('%s\'s Wishlist', $this->_getCustomer()->getName());
            $lang   = Mage::getStoreConfig('general/locale/code');

            $rssObj->_addHeader(array(
                'title'         => $title,
                'description'   => $title,
                'link'          => $newUrl,
                'charset'       => 'UTF-8',
                'language'      => $lang
            ));

            /* @var $product Mage_Catalog_Model_Product */
            foreach ($this->getWishlistItems() as $product) {
                $description = '<table><tr><td><a href="' . $this->getProductUrl($product)
                    . '"><img src="' . $this->helper('catalog/image')->init($product, 'thumbnail')->resize(75, 75)
                    . '" border="0" align="left" height="75" width="75"></a></td>'
                    . '<td style="text-decoration:none;">' . $this->htmlEscape($product->getShortDescription()) . '<p>';
                if ($product->getPrice() != $product->getFinalPrice()) {
                    $description .= Mage::helper('catalog')->__('Regular Price:') . ' <strike>'
                        . Mage::helper('core')->currency($product->getPrice()) . '</strike> '
                        . Mage::helper('catalog')->__('Special Price:') . ' <strong>'
                        . Mage::helper('core')->currency($product->getFinalPrice()).'</strong>';
                }
                else {
                    $description .= Mage::helper('catalog')->__('Price:') . ' '
                        . Mage::helper('core')->currency($product->getFinalPrice());
                }
                $description .= '</p>';
                if ($this->hasDescription($product)) {
                    $description .= '<p>' . Mage::helper('wishlist')->__('Comment:')
                        . ' ' . $this->getEscapedDescription($product) . '<p>';
                }

                $description .= '</td></tr></table>';

                $rssObj->_addEntry(array(
                    'title'         => $product->getName(),
                    'link'          => $this->getProductUrl($product),
                    'description'   => $description,
                ));
            }
        }
        else {
            $rssObj->_addHeader(array(
                'title'         => Mage::helper('rss')->__('Cannot retrieve the wishlist'),
                'description'   => Mage::helper('rss')->__('Cannot retrieve the wishlist'),
                'link'          => Mage::getUrl(),
                'charset'       => 'UTF-8',
            ));
        }

        return $rssObj->createRssXml();
    }

    /**
     * Retrieve Product View URL
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getProductUrl($product, $additional = array())
    {
        $additional['_rss'] = true;
        return parent::getProductUrl($product, $additional);
    }
}
