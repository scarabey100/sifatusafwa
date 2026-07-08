<?php
/**
 * Advanced Pack 5
 *
 * @author    Presta-Module.com <support@presta-module.com> - https://www.presta-module.com
 * @copyright Presta-Module - https://www.presta-module.com
 * @license   see file: LICENSE.txt
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

namespace PrestaModule\AdvancedPack;
if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShopBundle\Api\QueryParamsCollection;
use PrestaShopBundle\Entity\Repository\StockRepository;
class PackStockRepository extends StockRepository
{
    protected function andWhere(QueryParamsCollection $queryParams)
    {
        $sqlWhere = parent::andWhere($queryParams);
        $sqlWhere .= ' AND p.id_product NOT IN (SELECT id_pack FROM {table_prefix}pm_advancedpack) ';
        return $sqlWhere;
    }
}
