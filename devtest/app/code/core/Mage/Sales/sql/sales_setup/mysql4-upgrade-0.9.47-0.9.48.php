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


/* @var $installer Mage_Sales_Model_Mysql4_Setup */
$installer = $this;

$this->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales/invoiced_aggregated')}`
    (
        `id`                        int(11) unsigned NOT NULL auto_increment,
        `period`                    date NOT NULL DEFAULT '0000-00-00',
        `store_id`                  smallint(5) unsigned NULL DEFAULT NULL,
        `order_status`              varchar(50) NOT NULL default '',
        `orders_count`              int(11) NOT NULL DEFAULT '0',
        `orders_invoiced`           decimal(12,4) NOT NULL DEFAULT '0',
        `invoiced`                  decimal(12,4) NOT NULL DEFAULT '0',
        `invoiced_captured`         decimal(12,4) NOT NULL DEFAULT '0',
        `invoiced_not_captured`     decimal(12,4) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        UNIQUE KEY `UNQ_INVOICED_AGGREGATED_CREATED_PSS` (`period`,`store_id`, `order_status`),
        KEY `FK_INVOICED_AGGREGATED_CREATED_STORE` (`store_id`),
        CONSTRAINT `FK_INVOICED_AGGREGATED_CREATED_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`)
            ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales/invoiced_aggregated_order')}`
    (
        `id`                        int(11) unsigned NOT NULL auto_increment,
        `period`                    date NOT NULL DEFAULT '0000-00-00',
        `store_id`                  smallint(5) unsigned NULL DEFAULT NULL,
        `order_status`              varchar(50) NOT NULL default '',
        `orders_count`              int(11) NOT NULL DEFAULT '0',
        `orders_invoiced`           decimal(12,4) NOT NULL DEFAULT '0',
        `invoiced`                  decimal(12,4) NOT NULL DEFAULT '0',
        `invoiced_captured`         decimal(12,4) NOT NULL DEFAULT '0',
        `invoiced_not_captured`     decimal(12,4) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        UNIQUE KEY `UNQ_INVOICED_AGGREGATED_UPDATED_PSS` (`period`,`store_id`, `order_status`),
        KEY `FK_INVOICED_AGGREGATED_UPDATED_STORE` (`store_id`),
        CONSTRAINT `FK_INVOICED_AGGREGATED_UPDATED_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`)
            ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
