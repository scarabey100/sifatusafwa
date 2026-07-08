CREATE TABLE IF NOT EXISTS `PREFIX_presta_cart_save` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`id_customer` int(11) unsigned NOT NULL,
	`id_product` int(11) unsigned NOT NULL,
	`id_product_attribute` int(11) unsigned NOT NULL,
	`id_product_customization` int(11) unsigned,
	`quantity` int(11) unsigned NOT NULL,
	`is_notified` int(2) unsigned NOT NULL DEFAULT 0,
	`date_add` datetime NOT NULL,
	`date_upd` datetime NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;