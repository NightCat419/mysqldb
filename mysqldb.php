<?php
/**
 * WHMCS SDK Sample Provisioning Module
 *
 * Provisioning Modules, also referred to as Product or Server Modules, allow
 * you to create modules that allow for the provisioning and management of
 * products and services in WHMCS.
 *
 * This sample file demonstrates how a provisioning module for WHMCS should be
 * structured and exercises all supported functionality.
 *
 * Provisioning Modules are stored in the /modules/servers/ directory. The
 * module name you choose must be unique, and should be all lowercase,
 * containing only letters & numbers, always starting with a letter.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "mysqldb" and therefore all
 * functions begin "mysqldb_".
 *
 * If your module or third party API does not support a given function, you
 * should not define that function within your module. Only the _ConfigOptions
 * function is required.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/provisioning-modules/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license https://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}


use WHMCS\Database\Capsule;
// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related abilities and
 * settings.
 *
 * @see https://developers.whmcs.com/provisioning-modules/meta-data-params/
 *
 * @return array
 */
function mysqldb_MetaData()
{
    return array(
        'DisplayName' => 'MySQL DB',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => true, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '1111', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '1112', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'Login to Panel as User',
        'AdminSingleSignOnLabel' => 'Login to Panel as Admin',
    );
}

/**
 * Define product configuration options.
 *
 * The values you return here define the configuration options that are
 * presented to a user when configuring a product for use with the module. These
 * values are then made available in all module function calls with the key name
 * configoptionX - with X being the index number of the field from 1 to 24.
 *
 * You can specify up to 24 parameters, with field types:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each and their possible configuration parameters are provided in
 * this sample function.
 *
 * @see https://developers.whmcs.com/provisioning-modules/config-options/
 *
 * @return array
 */
function mysqldb_ConfigOptions()
{

    return array(
        // a text field type allows for single line text input
        'Database Server IP' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter Database Server IP',
        ),
        'Database Server Port' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter Database Server Port',
        ),
        'Database Name' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter database name',
        ),
        'User Name' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter username',
        ),
        // a password field type allows for masked text input
        'Password' => array(
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter secret value here',
        ),
        'PhpMyAdmin Url' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter PhpMyAdmin Url',
        ),

//        // the yesno field type displays a single checkbox option
//        'Checkbox Field' => array(
//            'Type' => 'yesno',
//            'Description' => 'Tick to enable',
//        ),
//        // the dropdown field type renders a select menu of options
//        'Dropdown Field' => array(
//            'Type' => 'dropdown',
//            'Options' => array(
//                'option1' => 'Display Value 1',
//                'option2' => 'Second Option',
//                'option3' => 'Another Option',
//            ),
//            'Description' => 'Choose one',
//        ),
//        // the radio field type displays a series of radio button options
//        'Radio Field' => array(
//            'Type' => 'radio',
//            'Options' => 'First Option,Second Option,Third Option',
//            'Description' => 'Choose your option!',
//        ),
//        // the textarea field type allows for multi-line text input
//        'Textarea Field' => array(
//            'Type' => 'textarea',
//            'Rows' => '3',
//            'Cols' => '60',
//            'Description' => 'Freeform multi-line text input field',
//        ),
    );
}

/**
 * Provision a new instance of a product/service.
 *
 * Attempt to provision a new instance of a given product/service. This is
 * called any time provisioning is requested inside of WHMCS. Depending upon the
 * configuration, this can be any of:
 * * When a new order is placed
 * * When an invoice for a new order is paid
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function mysqldb_CreateAccount(array $params)
{
    try {
        logModuleCall('mysqldb', __FUNCTION__, 'product', $params);
        // Get User Name
        $userid = $params['userid'];
        $params1 = array(
            'action'=>'GetClientsDetails',
            'clientid' => $userid,
            'stats' => true,
        );
        $response = request_whmcs_api($params1);

        $username = $response->email;

        $username = str_replace(["@", "."], "_", $username);
        $username = substr($username, 0, 30);
        $db_name = $username."_db";
        $new_password = $username;

        // Perform DB actions
        $host = $params['configoption1'];
        $db_admin = $params['configoption4'];
        $db_admin_pass = $params['configoption5'];

        $mysqli = new mysqli($host, $db_admin, $db_admin_pass);

        if($mysqli->connect_error){
            logModuleCall('mysqldb',__FUNCTION__, "db connection error", array('host'=>$host, 'user'=>$db_admin,'pass'=>$db_admin_pass));
            return "db connection error";
        }
        $query = "CREATE DATABASE $db_name;";
        if($mysqli->query($query) !== TRUE){
            logModuleCall('mysqldb',__FUNCTION__, "create database error", $query."\n".$mysqli->error);
            return "create database error";
        }

        $query = "CREATE USER $username@'%' IDENTIFIED BY '$new_password';";
        if($mysqli->query($query) !== TRUE){
            logModuleCall('mysqldb',__FUNCTION__, "create user error", $query."\n".$mysqli->error);
            return "create user error";
        }

        $query = "GRANT ALL PRIVILEGES ON $db_name.* TO $username@'%'";
        if($mysqli->query($query) !== TRUE){
            logModuleCall('mysqldb',__FUNCTION__, "grant privileges error", $query."\n".$mysqli->error);
            return "grant privileges error";
        }

        // create initial tables
        $query = "SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rma_id` varchar(255) NOT NULL,
  `att_desc` varchar(255) DEFAULT NULL,
  `att_type` varchar(255) DEFAULT NULL,
  `file` mediumblob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

CREATE TABLE `contacts` (
  `contactid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `custid` int(11) NOT NULL,
  `ContactName` varchar(255) DEFAULT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Phone` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`contactid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fname` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `lname` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `postcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `company` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `mobile1` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `country` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `custom1` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `custom2` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `custom3` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `custom4` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `custom5` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `registerDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=143 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `damages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

CREATE TABLE `devices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;

CREATE TABLE `emailtemplates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

CREATE TABLE `increment` (
  `incrementid` bigint(20) NOT NULL,
  `rmaid` varchar(255) NOT NULL,
  PRIMARY KEY (`incrementid`,`rmaid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `multiparts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `partRMA` varchar(255) NOT NULL,
  `partCode` varchar(255) DEFAULT NULL,
  `partDescription` varchar(255) DEFAULT NULL,
  `partSerial` varchar(255) DEFAULT NULL,
  `partQty` int(11) DEFAULT NULL,
  `partProblem` longtext,
  `partTechNotes` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `category` bigint(20) unsigned NOT NULL,
  `vendorID` int(10) unsigned NOT NULL,
  `unitPrice` decimal(8,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `tax` int(11) unsigned NOT NULL,
  `stock` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_categories_product` (`category`) USING BTREE,
  KEY `fk_tax_product` (`tax`) USING BTREE,
  KEY `fk_vendor_product` (`vendorID`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

CREATE TABLE `product_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productId` int(10) unsigned NOT NULL,
  `serviceId` bigint(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `qty` decimal(8,2) DEFAULT NULL,
  `unitPrice` decimal(8,2) DEFAULT NULL,
  `total` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_service_productservice_idx` (`serviceId`) USING BTREE,
  KEY `fk_product_service_product_idx` (`productId`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `rma_status` (
  `statusID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `statusName` varchar(255) NOT NULL,
  `statusColor` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`statusID`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

CREATE TABLE `roles` (
  `ID` int(11) DEFAULT NULL,
  `UserName` varchar(40) DEFAULT NULL,
  `MainApp` varchar(10) NOT NULL DEFAULT 'false',
  `MainNewCust` varchar(10) NOT NULL DEFAULT 'false',
  `MainSaveCust` varchar(10) NOT NULL DEFAULT 'false',
  `MainDelCust` varchar(10) NOT NULL DEFAULT 'false',
  `MainSearchCust` varchar(10) NOT NULL DEFAULT 'false',
  `MainNewRMA` varchar(10) NOT NULL DEFAULT 'false',
  `MainSaveRMA` varchar(10) NOT NULL DEFAULT 'false',
  `MainPrintRMA` varchar(10) NOT NULL DEFAULT 'false',
  `MainPrintTicket` varchar(10) NOT NULL DEFAULT 'false',
  `MainPrintParcel` varchar(10) NOT NULL DEFAULT 'false',
  `MainPrintSN` varchar(10) NOT NULL DEFAULT 'false',
  `MainGenSN` varchar(10) NOT NULL DEFAULT 'false',
  `ServicesMgm` varchar(10) NOT NULL DEFAULT 'false',
  `SMServiceMgm` varchar(10) NOT NULL DEFAULT 'false',
  `SMAttachments` varchar(10) NOT NULL DEFAULT 'false',
  `SMItemService` varchar(10) NOT NULL DEFAULT 'false',
  `SMNotification` varchar(10) NOT NULL DEFAULT 'false',
  `SMActionLog` varchar(10) NOT NULL DEFAULT 'false',
  `SMSearchRMA` varchar(10) NOT NULL DEFAULT 'false',
  `SMAddPart` varchar(10) NOT NULL DEFAULT 'false',
  `SMDelPart` varchar(10) NOT NULL DEFAULT 'false',
  `SMPrintRMA` varchar(10) NOT NULL DEFAULT 'false',
  `SMPrintParcel` varchar(10) NOT NULL DEFAULT 'false',
  `SMPrintSupp` varchar(10) NOT NULL DEFAULT 'false',
  `SMPrintCheckout` varchar(10) NOT NULL DEFAULT 'false',
  `SMSaveRMA` varchar(10) NOT NULL DEFAULT 'false',
  `SMDelRMA` varchar(10) NOT NULL DEFAULT 'false',
  `SettingsPanel` varchar(10) NOT NULL DEFAULT 'false',
  `SPGen` varchar(10) NOT NULL DEFAULT 'false',
  `SPRMAStat` varchar(10) NOT NULL DEFAULT 'false',
  `SPSupp` varchar(10) NOT NULL DEFAULT 'false',
  `SPDevices` varchar(10) NOT NULL DEFAULT 'false',
  `SPDamages` varchar(10) NOT NULL DEFAULT 'false',
  `SPCateg` varchar(10) NOT NULL DEFAULT 'false',
  `SPTech` varchar(10) NOT NULL DEFAULT 'false',
  `SPWarrenty` varchar(10) NOT NULL DEFAULT 'false',
  `SPSMS` varchar(10) NOT NULL DEFAULT 'false',
  `SPEmail` varchar(10) NOT NULL DEFAULT 'false',
  `SPBusiLoc` varchar(10) NOT NULL DEFAULT 'false',
  `SPCustField` varchar(10) NOT NULL DEFAULT 'false',
  `SPBackupExport` varchar(10) NOT NULL DEFAULT 'false',
  `SPRoles` varchar(10) NOT NULL DEFAULT 'false',
  `Exporting` varchar(10) NOT NULL DEFAULT 'false',
  `ExportSearch` varchar(10) NOT NULL DEFAULT 'false',
  `InventoryMgm` varchar(10) NOT NULL DEFAULT 'false',
  `IMNew` varchar(10) NOT NULL DEFAULT 'false',
  `IMEdit` varchar(10) NOT NULL DEFAULT 'false',
  `IMDel` varchar(10) NOT NULL DEFAULT 'false',
  `IMSearch` varchar(10) NOT NULL DEFAULT 'false',
  `Reporting` varchar(10) NOT NULL DEFAULT 'false'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `clientid` int(10) unsigned DEFAULT NULL,
  `rmaid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `clientname` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `cost` decimal(10,2) DEFAULT NULL,
  `opendate` datetime DEFAULT NULL,
  `timespent` time DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `includesoftware` int(11) DEFAULT NULL,
  `includecharger` int(11) DEFAULT NULL,
  `includecase` int(11) DEFAULT NULL,
  `includebattery` int(11) DEFAULT NULL,
  `includeother` int(11) DEFAULT NULL,
  `otherdesc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `machinetype` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `serialnumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `technician` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `machinecond` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `techdesc` longtext COLLATE utf8_unicode_ci,
  `finished` int(11) DEFAULT NULL,
  `delivered` int(11) DEFAULT NULL,
  `shelf` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `rma_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `supplier_cost` decimal(10,2) DEFAULT NULL,
  `supplier` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `estimatedate` date DEFAULT NULL,
  `warranty` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `custom1` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `custom2` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `custom3` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `custom4` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `custom5` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `internalnotes` longtext COLLATE utf8_unicode_ci,
  `store` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `accessories` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=201 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `smstemplates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `smstext` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE `stores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `telephone1` varchar(255) DEFAULT NULL,
  `telephone2` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `postcode` varchar(255) DEFAULT NULL,
  `keepstock` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

CREATE TABLE `tax` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `rate` decimal(8,6) unsigned NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

CREATE TABLE `technicians` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TechName` varchar(255) NOT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rmaid` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `details` varchar(255) NOT NULL,
  `technician` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `vendors` (
  `vendorID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendorName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendorAddress` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendorCity` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendorTel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendorFax` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendorEmail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendorZipCode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendorCountry` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`vendorID`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `warranties` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Warrant Code` varchar(255) NOT NULL,
  `Description` varchar(255) DEFAULT '',
  `Duration` int(255) unsigned NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

INSERT INTO `categories` VALUES ('1','Product'), ('2','Service'), ('3','Labour'), ('4','Mobile'), ('5','Laptop'), ('6','Desktop'), ('7','Tools'), ('8','Software'), ('9','CPU'), ('10','Graphics Card'), ('11','Hard Drive'), ('12','RAM Memory'), ('13','Printer'), ('14','Tablet'), ('15','Power Supply'), ('16','Power Adapter'), ('17','Scanner'), ('18','Keyboard'), ('19','Mouse'), ('20','Monitor');
INSERT INTO `customers` VALUES ('1','Hewlett','Packard','1501 Page Mill Road','Palo Alto','CA 94304','Hewlett-Packard','1-650-857-1501','1-650-857-1501','','1501 Page Mill Road','State','United States of America','Custom Field','Custom Field','Custom Field','Custom Field','Custom Field','2018-09-28');
INSERT INTO `damages` VALUES ('1','Broken Screen'), ('2','Display needs repairing'), ('3','Display not workig'), ('4','The keyboard is not functioning'), ('5','Format required'), ('6','Backup data required'), ('7','Recovery required'), ('8','Not working'), ('9','Not Opening'), ('10','Computer keeps restarting'), ('11','Computer is slow'), ('12','Computer freezes'), ('13','The Screen is Frozen'), ('14','Computer Won’t Start'), ('15','PC blue screen of death'), ('16','Pop-up ads'), ('17','Unusual noises'), ('18','The Screen is Blank'), ('19','Microphone not working'), ('20','Speaker not working'), ('21','On/off button not working'), ('22','Volume buttons not working');
INSERT INTO `devices` VALUES ('1','HP Pro Desk - Elite Desk'), ('2','Google Pixel 2 XL'), ('3','Honor 9'), ('4','HTC U11'), ('5','Huawei Mate 10 Pro'), ('6','iPad 1'), ('7','iPad 3'), ('8','iPad 3'), ('9','iPad 4'), ('10','iPhone'), ('11','iPhone 3G'), ('12','iPhone 3GS'), ('13','iPhone 4'), ('14','iPhone 4S'), ('15','iPhone 5'), ('16','iPhone 5c'), ('17','iPhone 5s'), ('18','iPhone 6'), ('19','iPhone 6 Plus'), ('20','iPhone 6s'), ('21','iPhone 6s Plus'), ('22','iPhone 7'), ('23','iPhone 7 Plus'), ('24','iPhone 8'), ('25','iPhone 8 Plus'), ('26','iPhone SE'), ('27','iPhone X'), ('28','iPhoneXs'), ('29','iPhone Xs MAX'), ('30','OnePlus 5T'), ('31','Samsung Galaxy (original)'), ('32','Samsung Galaxy 5'), ('33','Samsung Galaxy Chat'), ('34','Samsung Galaxy Core'), ('35','Samsung Galaxy Core 2'), ('36','Samsung Galaxy Express'), ('37','Samsung Galaxy Express 2'), ('38','Samsung Galaxy J'), ('39','Samsung Galaxy J2 Prime'), ('40','Samsung Galaxy J5'), ('41','Samsung Galaxy J5 (2016)'), ('42','Samsung Galaxy J7'), ('43','Samsung Galaxy Note 8'), ('44','Samsung Galaxy S'), ('45','Samsung Galaxy S II'), ('46','Samsung Galaxy S III'), ('47','Samsung Galaxy S4'), ('48','Samsung Galaxy S5'), ('49','Samsung Galaxy S6'), ('50','Samsung Galaxy S7'), ('51','Samsung Galaxy S8'), ('52','Samsung Galaxy S8'), ('53','Samsung Galaxy S8 Plus'), ('54','Samsung Galaxy Tab S'), ('55','Samsung Galaxy Tab S2'), ('56','Samsung Galaxy Tab S3'), ('57','Samsung Galaxy Trend 2 Lite');
INSERT INTO `emailtemplates` VALUES ('1','Notification of Repair for {PRODUCT}. Your Repair Service {RMA} is completed.Notification of Repair for {PRODUCT}. Your Repair Service {RMA} is completed.','Dear {CUSTOMER}, this is to inform you that your repair order with RMA ID:{RMA} for product {PRODUCT} has completed and it\'s ready for pickup from our repair shop.'), ('2','Cost quote for your Repair','This is to inform you about the cost of your repair.'), ('3','No fault found for your repair','This is to inform you that we have no found any problem with your repair.'), ('4','Unit is Replaced','This is to inform you that your unit has replaced and will sent back to you soon.'), ('9','Unit is Ready','This is to inform you that your repair has complete and is ready to pick up.');
INSERT INTO `product` VALUES ('13','001','2','2','20.00','Windows Recovery for Laptops','1','1'), ('14','002','1','1','30.00','Windows Recovery for Desktops','1','1'), ('15','003','1','1','15.00','Tablet System Recovery','1','1'), ('16','004','1','1','30.00','Virus Removal Service','1','1'), ('17','005','1','1','50.00','HDD Data Recovery','1','1'), ('18','006','1','1','120.00','Display Replacement','1','1'), ('19','007','1','1','50.00','Laptop Internal Cleaning','1','1'), ('20','008','1','1','50.00','Antivirus Installation','1','1'), ('21','009','1','1','80.00','Microsoft Office Installation','3','1'), ('22','010','2','1','20.00','Hardware Component Installation','3','150'), ('23','011','2','2','30.00','Intel CPU Fan Standard','2','150'), ('24','012','2','2','40.00','Intel CPU Fan Noiseless','2','150'), ('25','013','2','2','25.00','Intel PCI-e Network Card','1','150'), ('26','014','2','2','30.00','Intel PCI Network Card','1','150'), ('27','015','2','3','20.00','Nvidia GPU Fan Standard','1','150'), ('28','016','2','3','30.00','Nvidia GPU Fan Noiseless','1','150'), ('29','017','2','3','50.00','Nvidia Generic GPU','1','150');
INSERT INTO `rma_status` VALUES ('1','Awaiting Repair','Orange'), ('2','Repair in Proccess','Yellow'), ('3','Awaiting Supplier','LightPink'), ('4','Awaiting Parts','Yellow'), ('5','Repair Completed','Green'), ('6','Sent to Customer','Blue'), ('7','Awaiting QA','Purple'), ('8','Awaiting Customer Confirmation','YellowGreen'), ('9','Awaiting to be sent to Supplier','BlueViolet'), ('10','Sent to Supplier','Pink'), ('11','Repaired/Replacement from Supplier','Red');
INSERT INTO `roles` VALUES ('-1',NULL,'true','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','true','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false','false'), ('1','admin','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true'), ('2','tech','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true','true');
INSERT INTO `services` VALUES ('1','1','201789201053-RMA','Packard Hewlett','1-650-857-1501','15.00','2018-09-28 00:00:00','00:00:01','Black Color is not printing','0','0','1','0','1','USB Cable','HP Laserjet 7000','982017201054','Assistant Technician','Scratched','Awaiting Supplier Note.','0','0','C4','1-650-857-1501','Repair Completed','0.00','Hewlett Packard','2018-03-12','Not in Warranty','Custom Field1','Custom Field2','Custom Field3','Custom Field4','Custom Field5','Internal Notes.  Just for Internal Use.','Main Store','Charger,Display,USB Cable');
INSERT INTO `smstemplates` VALUES ('1','Your Repair is ready. You can pick it up.'), ('2','Your Repair has completed. We will deliver it soon.'), ('3','Your Repair is out of Warranty plan. Please call us for more details.'), ('4','There was a problem with your repair. Please call us for more information.');
INSERT INTO `stores` VALUES ('1','Main Store','60 Charles St','Manchester','+44 000 000 00','+44 000 000 01','+44 000 000 02','info@openrma.dom','Manchester','M1 7DF','0');
INSERT INTO `tax` VALUES ('1','Standard Tax','United Kingdom','20.000000','1'), ('2','Reduced Tax','United Kingdom','5.000000','1'), ('3','Zero Tax','United Kingdom','0.000000','1');
INSERT INTO `technicians` VALUES ('1','Administrator','admin@openrma.com','admin','admin'), ('2','Technician','tech@openrma.com','tech','tech');
INSERT INTO `vendors` VALUES ('1','Apple',NULL,'Dubai','9710509871848',NULL,'ahed@amt.tv',NULL,'UAE'), ('2','Nvidia Corporation','2701 San Tomas Expressway','Santa Clara','1+ (408) 486-2000','1+ (408) 486-2000','info@nvidia.com','CA 95050','United States'), ('3','Hewlett Packard','1501 Page Mill Road','Palo Alto','1-650-857-1501','1-650-857-1501','info@hp.com','CA 94304','United States');
INSERT INTO `warranties` VALUES ('1','WarrantPlan1','Warranty Plan For 1 Year','365'), ('2','WarrantPlan2','Warranty Plan For 2 Years','730'), ('3','WarrantPlan3','Warranty Plan For 3 Years','1095'), ('4','DOA15','Dead On Arrival Warranty Active for 15 Days ','15');
";

        $mysqli->select_db($db_name);
        if($mysqli->multi_query($query) !== TRUE){
            logModuleCall('mysqldb',__FUNCTION__, "create tables error", $query."\n".$mysqli->error);
            return "create tables error";
        }


        // send email
        $params2 = array(
            'action'=>'SendEmail',
            'messagename' => 'MySQL Product Order Email',
            'id' => $userid,
            'customtype' => 'general',
            'customsubject' => 'Product Welcome Email',
            'custommessage' => "<p>Thank you for choosing us</p><p>Your database information is following</p><br><p>DB Host:</p><p>$host</p><br><p>User id:</p><p>$username</p><br><p>User password:</p><p>$new_password</p><br><p>Database name:</p><p>$db_name</p><br>",
        );
        $response = request_whmcs_api($params2);

        // store database info to whmcs database
        Capsule::table('mys_products')->insert([
            'accountid'=>$params['accountid'],
            'serviceid'=>$params['serviceid'],
            'userid'=>$params['userid'],
            'packageid'=>$params['packageid'],
            'pid'=>$params['pid'],
            'producttype'=>$params['producttype'],
            'moduletype'=>$params['moduletype'],
            'dbhost'=>$host,
            'port'=>$params['configoption2'],
            'dbuser'=>$username,
            'dbpassword'=>$new_password,
            'dbname'=>$db_name,
            'adminurl'=>$params['configoption6']
        ]);

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Suspend an instance of a product/service.
 *
 * Called when a suspension is requested. This is invoked automatically by WHMCS
 * when a product becomes overdue on payment or can be called manually by admin
 * user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function mysqldb_SuspendAccount(array $params)
{
    try {
        // Call the service's suspend function, using the values provided by
        // WHMCS in `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Un-suspend instance of a product/service.
 *
 * Called when an un-suspension is requested. This is invoked
 * automatically upon payment of an overdue invoice for a product, or
 * can be called manually by admin user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function mysqldb_UnsuspendAccount(array $params)
{
    try {
        // Call the service's unsuspend function, using the values provided by
        // WHMCS in `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Terminate instance of a product/service.
 *
 * Called when a termination is requested. This can be invoked automatically for
 * overdue products if enabled, or requested manually by an admin user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function mysqldb_TerminateAccount(array $params)
{
    try {
        // Call the service's terminate function, using the values provided by
        // WHMCS in `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Change the password for an instance of a product/service.
 *
 * Called when a password change is requested. This can occur either due to a
 * client requesting it via the client area or an admin requesting it from the
 * admin side.
 *
 * This option is only available to client end users when the product is in an
 * active status.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function mysqldb_ChangePassword(array $params)
{
    try {
        // Call the service's change password function, using the values
        // provided by WHMCS in `$params`.
        //
        // A sample `$params` array may be defined as:
        //
        // ```
        // array(
        //     'username' => 'The service username',
        //     'password' => 'The new service password',
        // )
        // ```
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Upgrade or downgrade an instance of a product/service.
 *
 * Called to apply any change in product assignment or parameters. It
 * is called to provision upgrade or downgrade orders, as well as being
 * able to be invoked manually by an admin user.
 *
 * This same function is called for upgrades and downgrades of both
 * products and configurable options.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function mysqldb_ChangePackage(array $params)
{
    try {

        // Call the service's change password function, using the values
        // provided by WHMCS in `$params`.
        //
        // A sample `$params` array may be defined as:
        //
        // ```
        // array(
        //     'username' => 'The service username',
        //     'configoption1' => 'The new service disk space',
        //     'configoption3' => 'Whether or not to enable FTP',
        // )
        // ```
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Test connection with the given server parameters.
 *
 * Allows an admin user to verify that an API connection can be
 * successfully made with the given configuration parameters for a
 * server.
 *
 * When defined in a module, a Test Connection button will appear
 * alongside the Server Type dropdown when adding or editing an
 * existing server.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function mysqldb_TestConnection(array $params)
{
    try {
        // Call the service's connection test function.

        $success = true;
        $errorMsg = '';
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $success = false;
        $errorMsg = $e->getMessage();
    }

    return array(
        'success' => $success,
        'error' => $errorMsg,
    );
}

/**
 * Additional actions an admin user can invoke.
 *
 * Define additional actions that an admin user can perform for an
 * instance of a product/service.
 *
 * @see mysqldb_buttonOneFunction()
 *
 * @return array
 */
function mysqldb_AdminCustomButtonArray()
{
    return array(

    );
}

/**
 * Additional actions a client user can invoke.
 *
 * Define additional actions a client user can perform for an instance of a
 * product/service.
 *
 * Any actions you define here will be automatically displayed in the available
 * list of actions within the client area.
 *
 * @return array
 */
function mysqldb_ClientAreaCustomButtonArray()
{
    return array(

    );
}

/**
 * Custom function for performing an additional action.
 *
 * You can define an unlimited number of custom functions in this way.
 *
 * Similar to all other module call functions, they should either return
 * 'success' or an error message to be displayed.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see mysqldb_AdminCustomButtonArray()
 *
 * @return string "success" or an error message
 */
function mysqldb_buttonOneFunction(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Custom function for performing an additional action.
 *
 * You can define an unlimited number of custom functions in this way.
 *
 * Similar to all other module call functions, they should either return
 * 'success' or an error message to be displayed.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see mysqldb_ClientAreaCustomButtonArray()
 *
 * @return string "success" or an error message
 */
function mysqldb_actionOneFunction(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Admin services tab additional fields.
 *
 * Define additional rows and fields to be displayed in the admin area service
 * information and management page within the clients profile.
 *
 * Supports an unlimited number of additional field labels and content of any
 * type to output.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see mysqldb_AdminServicesTabFieldsSave()
 *
 * @return array
 */
function mysqldb_AdminServicesTabFields(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
        $response = array();

        // Return an array based on the function's response.
        return array(
            'Number of Apples' => (int) $response['numApples'],
            'Number of Oranges' => (int) $response['numOranges'],
            'Last Access Date' => date("Y-m-d H:i:s", $response['lastLoginTimestamp']),
            'Something Editable' => '<input type="hidden" name="mysqldb_original_uniquefieldname" '
                . 'value="' . htmlspecialchars($response['textvalue']) . '" />'
                . '<input type="text" name="mysqldb_uniquefieldname"'
                . 'value="' . htmlspecialchars($response['textvalue']) . '" />',
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, simply return no additional fields to display.
    }

    return array();
}

/**
 * Execute actions upon save of an instance of a product/service.
 *
 * Use to perform any required actions upon the submission of the admin area
 * product management form.
 *
 * It can also be used in conjunction with the AdminServicesTabFields function
 * to handle values submitted in any custom fields which is demonstrated here.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see mysqldb_AdminServicesTabFields()
 */
function mysqldb_AdminServicesTabFieldsSave(array $params)
{
    // Fetch form submission variables.
    $originalFieldValue = isset($_REQUEST['mysqldb_original_uniquefieldname'])
        ? $_REQUEST['mysqldb_original_uniquefieldname']
        : '';

    $newFieldValue = isset($_REQUEST['mysqldb_uniquefieldname'])
        ? $_REQUEST['mysqldb_uniquefieldname']
        : '';

    // Look for a change in value to avoid making unnecessary service calls.
    if ($originalFieldValue != $newFieldValue) {
        try {
            // Call the service's function, using the values provided by WHMCS
            // in `$params`.
        } catch (Exception $e) {
            // Record the error in WHMCS's module log.
            logModuleCall(
                'mysqldb',
                __FUNCTION__,
                $params,
                $e->getMessage(),
                $e->getTraceAsString()
            );

            // Otherwise, error conditions are not supported in this operation.
        }
    }
}

/**
 * Perform single sign-on for a given instance of a product/service.
 *
 * Called when single sign-on is requested for an instance of a product/service.
 *
 * When successful, returns a URL to which the user should be redirected.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function mysqldb_ServiceSingleSignOn(array $params)
{
    try {
        // Call the service's single sign-on token retrieval function, using the
        // values provided by WHMCS in `$params`.
        $response = array();

        return array(
            'success' => true,
            'redirectTo' => $response['redirectUrl'],
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}

/**
 * Perform single sign-on for a server.
 *
 * Called when single sign-on is requested for a server assigned to the module.
 *
 * This differs from ServiceSingleSignOn in that it relates to a server
 * instance within the admin area, as opposed to a single client instance of a
 * product/service.
 *
 * When successful, returns a URL to which the user should be redirected to.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function mysqldb_AdminSingleSignOn(array $params)
{
    try {
        // Call the service's single sign-on admin token retrieval function,
        // using the values provided by WHMCS in `$params`.
        $response = array();

        return array(
            'success' => true,
            'redirectTo' => $response['redirectUrl'],
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}

/**
 * Client area output logic handling.
 *
 * This function is used to define module specific client area output. It should
 * return an array consisting of a template file and optional additional
 * template variables to make available to that template.
 *
 * The template file you return can be one of two types:
 *
 * * tabOverviewModuleOutputTemplate - The output of the template provided here
 *   will be displayed as part of the default product/service client area
 *   product overview page.
 *
 * * tabOverviewReplacementTemplate - Alternatively using this option allows you
 *   to entirely take control of the product/service overview page within the
 *   client area.
 *
 * Whichever option you choose, extra template variables are defined in the same
 * way. This demonstrates the use of the full replacement.
 *
 * Please Note: Using tabOverviewReplacementTemplate means you should display
 * the standard information such as pricing and billing details in your custom
 * template or they will not be visible to the end user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function mysqldb_ClientArea(array $params)
{
    // Determine the requested action and set service call parameters based on
    // the action.

    $requestedAction = isset($_REQUEST['customAction']) ? $_REQUEST['customAction'] : '';

    if ($requestedAction == 'manage') {
        $serviceAction = 'get_usage';
        $templateFile = 'templates/manage.tpl';
    } else {
        $serviceAction = 'get_stats';
        $templateFile = 'templates/overview.tpl';
    }

    try {
        // Call the service's function based on the request action, using the
        // values provided by WHMCS in `$params`.
        $response = array();

        $mys_products = Capsule::table('mys_products')->where('accountid', $params['accountid'])->get();
        if(count($mys_products) > 0){
            logModuleCall(
                'mysqldb',
                __FUNCTION__,
                $params,
                $mys_products
            );
            $product = $mys_products[0];
            return array(
                'tabOverviewReplacementTemplate' => $templateFile,
                'templateVariables' => array(
                    'dbhost' => $product->dbhost,
                    'port' => $product->port,
                    'dbuser' => $product->dbuser,
                    'dbpassword' => $product->dbpassword,
                    'dbname' => $product->dbname,
                    'adminurl'=> $product->adminurl
                ),
            );
        }

        $extraVariable1 = 'abc';
        $extraVariable2 = '123';

        return array(
            'tabOverviewReplacementTemplate' => $templateFile,
            'templateVariables' => array(
                'extraVariable1' => $extraVariable1,
                'extraVariable2' => $extraVariable2,
            ),
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'mysqldb',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, display an error page.
        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}

function request_whmcs_api($params){
    $whmcs_api_identifier = "ypwzLE0J9gdEvbEuYLYCED41pm23URKf";
    $whmcs_api_secret = "W7R6t38soBBDe8XDCSbFGFiPAmazgObd";
    $whmcs_api_access_key = "vasilieva";

    $params['username'] = $whmcs_api_identifier;
    $params['password'] = $whmcs_api_secret;
    $params['accesskey'] = $whmcs_api_access_key;
    $params['responsetype'] = 'json';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://test.openrma.com/includes/api.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
        http_build_query($params)
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    logModuleCall(
        'mysqldb',
        'OrderPaid',
        $params,
        $response
    );
    return json_decode($response);
}

function request_cpanel_api($query, $params){
    $cpusername = "openrmaprojects";
    $cppassword = "IGc7W^v]anCo";

    $query = "https://127.0.0.1:2083/execute/$query";

    $curl = curl_init();                                // Create Curl Object
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);       // Allow self-signed certs
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);       // Allow certs that do not match the hostname
    curl_setopt($curl, CURLOPT_HEADER,0);               // Do not include header in output
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);       // Return contents of transfer on curl_exec
    $header[0] = "Authorization: Basic " . base64_encode($cpusername.":".$cppassword) . "\n\r";
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
    curl_setopt($curl, CURLOPT_URL, $query);            // execute the query
    $result = curl_exec($curl);
    if ($result == false) {
        logModuleCall(
            'mysqldb',
            "OrderPaid",
            curl_error($curl),
            $query
        );
        error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
        // log error if curl exec fails
    }
    curl_close($curl);
    logModuleCall(
        'mysqldb',
        "OrderPaid",
        $query,
        $result
    );
    return json_decode($result);
}