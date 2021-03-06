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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order status history comments
 */
class Mage_Sales_Model_Order_Status_History extends Mage_Sales_Model_Abstract
{
    /**
     * Order instance
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Whether setting order again is required (for example when setting non-saved yet order)
     * @var bool
     */
    private $_shouldSetOrderBeforeSave = false;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('sales/order_status_history');
    }

    /**
     * Set order object and grab some metadata from it
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  Mage_Sales_Model_Order_Status_History
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        $id = $order->getId();
        if (!$id) {
            $this->_shouldSetOrderBeforeSave = true;
        }
        return $this->setParentId($id)->setStoreId($order->getStoreId());
    }

    /**
     * Retrieve order instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Retrieve status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        if($this->getOrder()) {
            return $this->getOrder()->getConfig()->getStatusLabel($this->getStatus());
        }
    }

    /**
     * Get store object
     *
     * @return unknown
     */
    public function getStore()
    {
        if ($this->getOrder()) {
            return $this->getOrder()->getStore();
        }
        return Mage::app()->getStore();
    }

    /**
     * Set order again if required
     * @return Mage_Sales_Model_Order_Status_History
     */
    protected function _beforeSave()
    {
        if ($this->_shouldSetOrderBeforeSave) {
            $this->setOrder($this->_order);
        }
        return parent::_beforeSave();
    }
}
