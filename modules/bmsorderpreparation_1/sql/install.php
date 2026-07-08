<?php 

$sql = array();

/* Information on content EG egwishlist_product */
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bms_orderpreparation_inprogress` (
            `id_bms_orderpreparation_inprogress` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_employee` int(10) UNSIGNED NOT NULL,
            `id_warehouse` int(10) UNSIGNED NOT NULL,
            `id_order` int(10) UNSIGNED NOT NULL,
            `id_state_ori` int(10) UNSIGNED NOT NULL,
            `date_add` datetime NOT NULL,
            PRIMARY KEY (`id_bms_orderpreparation_inprogress`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bms_orderpreparation_mime_type` (
            `id_bms_orderpreparation_mime_type` int(10) UNSIGNED NOT NULL,
            `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            `class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id_bms_orderpreparation_mime_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'; 

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bms_orderpreparation_shipping_template` (
            `id_bms_orderpreparation_shipping_template` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(50) CHARACTER SET utf8 NOT NULL,
            `date_add` datetime NOT NULL,
            `id_template_type` int(10) UNSIGNED NOT NULL,
            `active` tinyint(1) UNSIGNED NOT NULL,
            `id_export_mime_type` int(10) UNSIGNED NOT NULL,
            `id_import_mime_type` int(10) UNSIGNED NOT NULL DEFAULT 1,
            `shipping_methods` text CHARACTER SET utf8 NOT NULL,
            `export_file_name` varchar(50) CHARACTER SET utf8 NOT NULL,
            `export_file_header` text CHARACTER SET utf8 NOT NULL,
            `export_file_order_header` text CHARACTER SET utf8 NOT NULL,
            `export_file_order_products` text CHARACTER SET utf8 NOT NULL,
            `export_file_order_footer` text CHARACTER SET utf8 NOT NULL,
            `export_file_footer_line` text CHARACTER SET utf8 NOT NULL,
            `import_file_separator` varchar(5) CHARACTER SET utf8 NOT NULL,
            `import_file_skip_first_line` tinyint(1) UNSIGNED NOT NULL,
            `import_file_shipment_reference_index` smallint(6) NOT NULL,
            `import_file_tracking_index` smallint(6) NOT NULL,
            PRIMARY KEY (`id_bms_orderpreparation_shipping_template`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'; 

 $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bms_orderpreparation_shipping_template_type` (
            `id_bms_orderpreparation_shipping_template_type` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id_bms_orderpreparation_shipping_template_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

$sql[] = "INSERT INTO `"._DB_PREFIX_."bms_orderpreparation_mime_type` (`id_bms_orderpreparation_mime_type`, `name`, `class`) VALUES
(1, 'CSV, XML', 'CsvXml');"; 

$sql[] = "INSERT INTO `"._DB_PREFIX_."bms_orderpreparation_shipping_template_type` (`id_bms_orderpreparation_shipping_template_type`, `name`) VALUES
(1, 'Order details file export');"; 

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}


