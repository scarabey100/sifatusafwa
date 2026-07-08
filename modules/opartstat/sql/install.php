<?php

/**
 * 2007-2020 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
        exit;
}

$sql = array();

$mysqlVersion = Db::getInstance()->getVersion();
$dateTimOrTimeStamp = ($mysqlVersion < '5.6.5') ? 'TIMESTAMP' : 'datetime';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_sessions` (
        `visiteId` int(10) NOT NULL AUTO_INCREMENT,
        `createdAt` ' . $dateTimOrTimeStamp . ' DEFAULT CURRENT_TIMESTAMP,
        `userIp` varchar(64) NOT NULL,
        `pageUrl` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
        `device` int(1),
        `country` varchar(100),
        `referrer` varchar(250) NOT NULL,
        `userLanguage` varchar(20) NOT NULL,
        `idCart` int(10) DEFAULT NULL,
        `elementId` int(10) DEFAULT NULL,
        `controllerName` varchar(100),
        `shopId` int(10) DEFAULT NULL,
        `utm_medium` varchar(250),
        `utm_campaign` varchar(250),
        `gclid` varchar(255) DEFAULT NULL,
        `userId` int(12) DEFAULT NULL,
        `userAgent` text CHARACTER SET utf8mb4 DEFAULT NULL,
        PRIMARY KEY (`visiteId`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_robots` (
        `robotId` int(10) NOT NULL AUTO_INCREMENT,
        `name` varchar(250),
        PRIMARY KEY (`robotId`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'INSERT INTO `' . _DB_PREFIX_ . 'opartstat_robots` (name) VALUES 
        ("BotLink"),
        ("ahoy"),
        ("AlkalineBOT"),
        ("anthill"),
        ("appie"),
        ("arale"),
        ("araneo"),
        ("AraybOt"),
        ("ariadne"),
        ("arks"),
        ("ATN_Worldwide"),
        ("Atomz"),
        ("bbot"),
        ("Bjaaland"),
        ("Ukonline"),
        ("borg-bot/0.9"),
        ("boxseabot"),
        ("bspider"),
        ("calif"),
        ("christcrawler"),
        ("CMC/0.01"),
        ("combine"),
        ("confuzzledbot"),
        ("CoolBot"),
        ("cosmos"),
        ("Internet Cruiser Robot"),
        ("cusco"),
        ("cyberspyder"),
        ("cydralspider"),
        ("desertrealm, desert realm"),
        ("digger"),
        ("DIIbot"),
        ("grabber"),
        ("downloadexpress"),
        ("DragonBot"),
        ("dwcp"),
        ("ecollector"),
        ("ebiness"),
        ("elfinbot"),
        ("esculapio"),
        ("esther"),
        ("fastcrawler"),
        ("FDSE"),
        ("FELIX IDE"),
        ("ESI"),
        ("fido"),
        ("KIT-Fireball"),
        ("fouineur"),
        ("Freecrawl"),
        ("gammaSpider"),
        ("gazz"),
        ("gcreep"),
        ("golem"),
        ("googlebot"),
        ("griffon"),
        ("Gromit"),
        ("gulliver"),
        ("gulper"),
        ("hambot"),
        ("havIndex"),
        ("hotwired"),
        ("htdig"),
        ("iajabot"),
        ("INGRID/0.1"),
        ("Informant"),
        ("InfoSpiders"),
        ("inspectorwww"),
        ("irobot"),
        ("Iron33"),
        ("JBot"),
        ("jcrawler"),
        ("Teoma"),
        ("Jeeves"),
        ("jobo"),
        ("image.kapsi.net"),
        ("KDD-Explorer"),
        ("ko_yappo_robot"),
        ("label-grabber"),
        ("larbin"),
        ("legs"),
        ("Linkidator"),
        ("linkwalker"),
        ("Lockon"),
        ("logo_gif_crawler"),
        ("marvin"),
        ("mattie"),
        ("mediafox"),
        ("MerzScope"),
        ("NEC-MeshExplorer"),
        ("MindCrawler"),
        ("udmsearch"),
        ("moget"),
        ("Motor"),
        ("msnbot"),
        ("muncher"),
        ("muninn"),
        ("MuscatFerret"),
        ("MwdSearch"),
        ("sharp-info-agent"),
        ("WebMechanic"),
        ("combine"),
        ("NetScoop"),
        ("newscan-online"),
        ("ObjectsSearch"),
        ("Occam"),
        ("Orbsearch/1.0"),
        ("packrat"),
        ("pageboy"),
        ("ParaSite"),
        ("patric"),
        ("pegasus"),
        ("perlcrawler"),
        ("phpdig"),
        ("piltdownman"),
        ("Pimptrain"),
        ("pjspider"),
        ("PlumtreeWebAccessor"),
        ("PortalBSpider"),
        ("psbot"),
        ("Getterrobo"),
        ("-Plus"),
        ("Raven"),
        ("RHCS"),
        ("RixBot"),
        ("roadrunner"),
        ("Robbie"),
        ("robi"),
        ("RoboCrawl"),
        ("robofox"),
        ("Scooter"),
        ("Search-AU"),
        ("searchprocess"),
        ("Senrigan"),
        ("Shagseeker"),
        ("sift"),
        ("SimBot"),
        ("Site Valet"),
        ("skymob"),
        ("SLCrawler/2.0"),
        ("slurp"),
        ("ESI"),
        ("snooper"),
        ("solbot"),
        ("speedy"),
        ("spider_monkey"),
        ("SpiderBot/1.0"),
        ("spiderline"),
        ("nil"),
        ("suke"),
        ("http://www.sygol.com"),
        ("tach_bw"),
        ("TechBOT"),
        ("templeton"),
        ("titin"),
        ("topiclink"),
        ("UdmSearch"),
        ("urlck"),
        ("Valkyrie libwww-perl"),
        ("verticrawl"),
        ("Victoria"),
        ("void-bot"),
        ("Voyager"),
        ("VWbot_K"),
        ("crawlpaper"),
        ("wapspider"),
        ("WebBandit/1.0"),
        ("webcatcher"),
        ("T-H-U-N-D-E-R-S-T-O-N-E"),
        ("WebMoose"),
        ("webquest"),
        ("webreaper"),
        ("webs"),
        ("webspider"),
        ("WebWalker"),
        ("wget"),
        ("winona"),
        ("whowhere"),
        ("wlm"),
        ("WOLP"),
        ("WWWC"),
        ("none"),
        ("XGET"),
        ("Nederland.zoek"),
        ("AISearchBot"),
        ("woriobot"),
        ("NetSeer"),
        ("Nutch"),
        ("YandexBot")
        ';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_ips_blocking` (
        `ipId` int(10) NOT NULL AUTO_INCREMENT,
        `ip` varchar(64) NOT NULL,
        PRIMARY KEY (`ipId`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_partnermodules_hook` (
        `partnerModuleHookId` int(10) NOT NULL AUTO_INCREMENT,
        `moduleName` varchar(250),
        `hookName` varchar(250),
        PRIMARY KEY (`partnerModuleHookId`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';


$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_trackable_links_preset` (
        `presetId` int(10) NOT NULL AUTO_INCREMENT,
        `utmSource` varchar(250),
        `utmMedium` varchar(250),
        `utmCampaign` varchar(250),
        PRIMARY KEY (`presetId`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_commissions` (
        `commissionId` int(10) NOT NULL AUTO_INCREMENT,
        `startDate` ' . $dateTimOrTimeStamp . ' NOT NULL,
        `endDate` ' . $dateTimOrTimeStamp . ',
        `fixedFees` decimal(20,6),        
        `variableFees` decimal(20,6),
        `paymentMethod` varchar(255),
        PRIMARY KEY (`commissionId`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_config` (
        `id` int(10) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) UNIQUE,
        `value` varchar(512),
        PRIMARY KEY (`id`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_shared_reports` (
        `id` int(10) NOT NULL AUTO_INCREMENT,
        `reportName` varchar(255),
        `ownerUserId` int(10),
        `guestUserId` int(10),
        `rights` int(1),
        PRIMARY KEY (`id`),
        UNIQUE KEY unique_report_access (reportName, ownerUserId, guestUserId)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

return $sql;
