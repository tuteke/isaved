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
 * Sales transaction resource model
 */
class Mage_Sales_Model_Mysql4_Order_Payment_Transaction extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize main table and the primary key field name
     */
    protected function _construct()
    {
        $this->_init('sales/payment_transaction', 'transaction_id');
    }

    /**
     * Update transactions in database using provided transaction as parent for them
     * have to repeat the business logic to avoid accidental injection of wrong transactions
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     */
    public function injectAsParent(Mage_Sales_Model_Order_Payment_Transaction $transaction)
    {
        $txnId = $transaction->getTxnId();
        if ($txnId && Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT === $transaction->getTxnType()
            && $id = $transaction->getId()) {
            $adapter = $this->_getWriteAdapter();

            // verify such transaction exists, determine payment and order id
            $verificationRow = $adapter->fetchRow(
                $adapter->select()->from($this->getMainTable(), array('payment_id', 'order_id'))
                ->where("{$this->getIdFieldName()} = ?", (int)$id)
            );
            if (!$verificationRow) {
                return;
            }
            list($paymentId, $orderId) = array_values($verificationRow);

            // inject
            $adapter->update($this->getMainTable(), array('parent_id' => $id),
                sprintf('%s <> %d AND parent_id IS NULL AND payment_id = %d AND order_id = %d AND parent_txn_id = %s',
                    $this->getIdFieldName(), $id,
                    (int)$paymentId, (int)$orderId,
                    $adapter->quote($txnId)
            ));
        }
    }

    /**
     * Load the tansaction object by specified txn_id
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     * @param int $orderId
     * @param int $paymentId
     * @param string $txnId
     */
    public function loadObjectByTxnId(Mage_Sales_Model_Order_Payment_Transaction $transaction, $orderId, $paymentId, $txnId)
    {
        $select = $this->_getLoadByUniqueKeySelect($orderId, $paymentId, $txnId);
        $data = $this->_getWriteAdapter()->fetchRow($select);
        $transaction->setData($data);
        $this->_afterLoad($transaction);
    }

    /**
     * Lookup for parent_id in already saved transactions of this payment by the order_id
     * Also serialize additional information, if any
     *
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     * @return Mage_Sales_Model_Mysql4_Order_Payment_Transaction
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $transaction)
    {
        $parentTxnId = $transaction->getData('parent_txn_id');
        if ($parentTxnId) {
            $txnId = $transaction->getData('txn_id');
            $orderId = $transaction->getData('order_id');
            $paymentId = $transaction->getData('payment_id');
            if (!$txnId || !$orderId || !$paymentId) {
                Mage::throwException(Mage::helper('sales')->__('Not enough valid data to save parent transaction ID.'));
            }
            $parentId = (int)$this->_lookupByTxnId($orderId, $paymentId, $parentTxnId, $this->getIdFieldName());
            if ($parentId) {
                $transaction->setData('parent_id', $parentId);
            }
        }

        // serialize or set additional information to null
        $additionalInformation = $transaction->getData('additional_information');
        if (empty($additionalInformation)) {
            $transaction->setData('additional_information', null);
        } elseif (is_array($additionalInformation)) {
            $transaction->setData('additional_information', serialize($additionalInformation));
        }
        return parent::_beforeSave($transaction);
    }

    /**
     * Unserialize additional data after loading the object
     *
     * @param Mage_Core_Model_Abstract $transaction
     * @return Mage_Sales_Model_Mysql4_Order_Payment_Transaction
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $transaction)
    {
        $this->unserializeFields($transaction);
        return parent::_afterLoad($transaction);
    }

    /**
     * Unserialize additional data after saving the object (to have the data and orig_data consistent)
     *
     * @param Mage_Core_Model_Abstract $transaction
     * @return Mage_Sales_Model_Mysql4_Order_Payment_Transaction
     */
    protected function _afterSave(Mage_Core_Model_Abstract $transaction)
    {
        $this->unserializeFields($transaction);
        return parent::_afterSave($transaction);
    }

    /**
     * Unserialize additional information if required
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     */
    public function unserializeFields(Mage_Sales_Model_Order_Payment_Transaction $transaction)
    {
        $additionalInformation = $transaction->getData('additional_information');
        if (empty($additionalInformation)) {
            $transaction->setData('additional_information', array());
        } elseif (!is_array($additionalInformation)) {
            $transaction->setData('additional_information', unserialize($additionalInformation));
        }
    }

    /**
     * Load cell/row by specified unique key parts
     * @param int $orderId
     * @param int $paymentId
     * @param string $txnId
     * @param mixed (array|string|object) $columns
     * @param bool $isRow
     * @param string $txnType
     * @return mixed (array|string)
     */
    private function _lookupByTxnId($orderId, $paymentId, $txnId, $columns, $isRow = false, $txnType = null)
    {
        $select = $this->_getLoadByUniqueKeySelect($orderId, $paymentId, $txnId, $columns);
        if ($txnType) {
            $select->where('txn_type = ?', $txnType);
        }
        if ($isRow) {
            return $this->_getWriteAdapter()->fetchRow($select);
        }
        return $this->_getWriteAdapter()->fetchOne($select);
    }

    /**
     * Get select object for loading transaction by the unique key of order_id, payment_id, txn_id
     * @param int $orderId
     * @param int $paymentId
     * @param string $txnId
     * @param string|array|Zend_Db_Expr $columns
     */
    private function _getLoadByUniqueKeySelect($orderId, $paymentId, $txnId, $columns = '*')
    {
        return $this->_getWriteAdapter()->select()
            ->from($this->getMainTable(), $columns)
            ->where('order_id = ?', $orderId)
            ->where('payment_id = ?', $paymentId)
            ->where('txn_id = ?', $txnId);
    }
}
