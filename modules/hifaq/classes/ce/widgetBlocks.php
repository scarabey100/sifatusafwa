<?php
/**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 */

namespace CE;

if (!defined('_PS_VERSION_')) {
    exit;
}

class WidgetHiFAQBlocks extends WidgetBase
{
    public function getName()
    {
        return 'hi-faq-blocks';
    }

    public function getTitle()
    {
        return $this->l('Frequently Asked Questions - Blocks');
    }

    public function getIcon()
    {
        return 'fa fa-question';
    }

    public function getCategories()
    {
        return ['premium'];
    }

    protected function _registerControls()
    {
        $this->startControlsSection(
            'section_title',
            [
                'label' => $this->l('Frequently Asked Questions - Blocks'),
            ]
        );

        $blocks = \HiFAQBlock::getBlocks(true);
        $blocks_options = [];
        if (is_array($blocks) && $blocks) {
            foreach ($blocks as $block) {
                $blocks_options[$block['id_block']] = $block['title'];
            }
        } else {
            $blocks_options[0] = $this->l('Blocks not found');
        }

        $this->addControl(
            'block',
            [
                'label' => $this->l('Select FAQs Block'),
                'type' => ControlsManager::SELECT,
                'options' => $blocks_options,
                'default' => isset($blocks[0]['id_block']) ? $blocks[0]['id_block'] : 0,
                'description' => $this->l('You can create more blocks from Frequently Asked Questions module configuration page'),
            ]
        );

        $this->endControlsSection();
    }

    protected function render()
    {
        $settings = $this->getSettings();

        $module = \Module::getInstanceByName('hifaq');
        echo $module->getBlockForCreativeElements($settings['block']);
    }

    protected function l($string)
    {
        if (function_exists('trans')) {
            return trans($string, 'hifaqblockswidget', basename(__FILE__, '.php'));
        } elseif (function_exists('translate')) {
            return translate($string, 'hifaqblockswidget', basename(__FILE__, '.php'));
        } else {
            return $string;
        }
    }
}
