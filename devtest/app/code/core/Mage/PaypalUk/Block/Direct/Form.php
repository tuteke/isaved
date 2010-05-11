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
 * @package     Mage_PaypalUk
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * UK domestic cards specific information block
 */
class Mage_PaypalUk_Block_Direct_Form extends Mage_Payment_Block_Form_Cc
{

    /**
     * Payment method object getter
     * @return Mage_PayPalUk_Model_Direct
     */
    protected function _getDirect()
    {
        return Mage::getSingleton('paypaluk/direct');
    }

    /**
     * Set 3dsecure-specific parameters
     */
    public function __construct()
    {
        parent::__construct();
        $this->setJsObjectName('payPalUkCentinel');
        $this->setCentinelIframeId('paypaluk_3dsecure_iframe');
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_getDirect()->getApi()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code=>$name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }

    /*
    * solo/switch card start year
    * @return array
    */
     public function getSsStartYears()
    {
        $years = array();
        $first = date("Y");

        for ($index=5; $index>=0; $index--) {
            $year = $first - $index;
            $years[$year] = $year;
        }
        $years = array(0=>$this->__('Year'))+$years;
        return $years;
    }

    /*
    * switch/solo card type available
    */
    public function hasSsCardType()
    {
        $availableTypes =$this->getMethod()->getConfigData('cctypes');
        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
             if (in_array('SS', $availableTypes)) {
                 return true;
             }
        }
        return false;
    }

    /**
     * Add UK domestic cards additional fields as child block
     *
     * Forks a clone, but with a different form
     *
     * @return Mage_PaypalUk_Block_Direct_Form
     */
    public function _beforeToHtml()
    {
        $child = clone $this;
        $this->setChild('uk_domestic',
            $child->setTemplate('paypaluk/direct/form.phtml')
        );
        return parent::_beforeToHtml();
    }

    /**
     * Return 3D secure validate url
     *
     * @return string
     */
    public function getValidateUrl()
    {
        return $this->getUrl('paypaluk/direct/lookup', array('_secure' => true));
    }

    /**
     * Return formated centinel js object name
     *
     * @return string
     */
    public function getCentinelJsObjectName()
    {
        return $this->getJsObjectName();
    }
}
