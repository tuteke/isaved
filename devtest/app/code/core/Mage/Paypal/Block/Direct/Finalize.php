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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Display Finalize 3D secure page to manage 3D secure result
 */

class Mage_Paypal_Block_Direct_Finalize extends Mage_Payment_Block_Form
{
    /**
     * Init Finalize block, setup Js object names, output layout/template
     *
     * @return Mage_Paypal_Block_Direct_Finalize
     */
    protected function _construct()
    {
        $this->setJsObjectName('payPalCentinel');
        $this->setCentinelIframeId('paypal_3dsecure_iframe');
        parent::_construct();
        return $this;
    }

    /**
     * Return Centinel js object name, used to call parent object with correcponding action
     *
     * @return string
     */
    public function getCentinelJsObjectName()
    {
        return $this->getJsObjectName();
    }
}
