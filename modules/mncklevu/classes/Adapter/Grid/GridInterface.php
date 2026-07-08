<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Grid;

interface GridInterface
{
    /**
     * @return string
     */
    public function getTable();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getAddAction();

    /**
     * @return string
     */
    public function getEditAction();

    /**
     * @return string
     */
    public function getDeleteAction();
}
