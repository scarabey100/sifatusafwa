<?php
/*
* 2007-2012 PrestaShop
* NOTICE OF LICENSE
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
* DISCLAIMER
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*  @author Webkul Sogtware Pvt. Ltd <www.webkul.com>
*  @copyright  2009-2015 Webkul Software Pvt. Ltd.
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class pob_log{

	public function getFilename(){
		$name='log_'.date('m-d-Y').'.log';
		return $name;
	}
	
	public function logMessage($fname,$lineno,$message,$level='ERROR'){
		$filename = $this->getFilename();	
		
		$formatted_message = '*'.$level.'*'."\t".date('Y/m/d - H:i:s').': '.'File name: '.basename($fname)." - ".'Line No: '.$lineno."\r\nMessage: ".$message."\r\n";
		return (bool)file_put_contents(_PS_MODULE_DIR_.'prestarealtimesync/logs/'.$filename, $formatted_message, FILE_APPEND);
	}
}