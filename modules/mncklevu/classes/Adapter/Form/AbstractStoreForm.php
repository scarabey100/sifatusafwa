<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Form;

use MncKlevu\PrestaShop\Adapter\Grid\StoreGrid;
use Module;

abstract class AbstractStoreForm extends AbstractForm
{
    /**
     * @param Module $module
     * @param StoreGrid $grid
     */
    public function __construct(Module $module, StoreGrid $grid)
    {
        parent::__construct($module, $grid);
    }
}
