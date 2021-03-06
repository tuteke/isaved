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
 * @package     Mage_Cybersource
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Cybersource_Model_Config extends Mage_Payment_Model_Config
{
    protected $_ccTypes = array();
    /**
     * Retrieve array of credit card types
     *
     * @return array
    */
    public function getCcTypes()
    {
        $pTypes = parent::getCcTypes();
        $this->_ccTypes = array();
        $added = false;
        foreach ($pTypes as $code => $name) {
             if ($code=='OT') {
                $added = true;
                $this->addExtraCcTypes();
            }
            $this->_ccTypes[$code] = $name;
        }
        if (!$added) {
            $this->addExtraCcTypes();
        }
        return $this->_ccTypes;
    }

    public function addExtraCcTypes()
    {
        $this->_ccTypes['JCB'] = Mage::helper('cybersource')->__('JCB');
        $this->_ccTypes['LASER'] = Mage::helper('cybersource')->__('Laser');
        $this->_ccTypes['UATP'] = Mage::helper('cybersource')->__('UATP');
        $this->_ccTypes['MCI'] = Mage::helper('cybersource')->__('Maestro (International)');
        $this->_ccTypes[Mage_Cybersource_Model_Soap::CC_CARDTYPE_SS] = Mage::helper('cybersource')->__('Maestro/Solo(UK Domestic)');

    }

}
