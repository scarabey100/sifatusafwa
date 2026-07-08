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
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
class PackAttributeGroupQueryFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->reflClass->getName() != 'PrestaShopBundle\Entity\AttributeGroup') {
            return '';
        }
        return $targetTableAlias . '.id_attribute_group != ' . (int)\AdvancedPack::getPackAttributeGroupId();
    }
}
