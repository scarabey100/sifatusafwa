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

class WidgetHiFAQs extends WidgetBase
{
    public function getName()
    {
        return 'hi-faq-faqs';
    }

    public function getTitle()
    {
        return $this->l('Frequently Asked Questions - FAQs');
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
                'label' => $this->l('Frequently Asked Questions - FAQs'),
            ]
        );

        $faqs = \HiFAQItem::getFAQs(true);
        $faqs_options = [];
        if (is_array($faqs) && $faqs) {
            foreach ($faqs as $faq) {
                $faqs_options[$faq['id_faq']] = $faq['title'];
            }
        } else {
            $posts_options[0] = $this->l('FAQs not found');
        }

        $this->addControl(
            'id_faq',
            [
                'label' => $this->l('FAQs'),
                'type' => ControlsManager::SELECT,
                'options' => $faqs_options,
                'default' => isset($faqs[0]['id_faq']) ? $faqs[0]['id_faq'] : 0,
                'description' => $this->l('You can create more FAQs from Frequently Asked Questions module configuration page'),
            ]
        );

        $this->endControlsSection();
    }

    protected function render()
    {
        $settings = $this->getSettings();

        $module = \Module::getInstanceByName('hifaq');
        echo $module->renderFAQForCreativeElements($settings['id_faq']);
    }

    protected function l($string)
    {
        if (function_exists('trans')) {
            return trans($string, 'hifaqswidget', basename(__FILE__, '.php'));
        } elseif (function_exists('translate')) {
            return translate($string, 'hifaqswidget', basename(__FILE__, '.php'));
        } else {
            return $string;
        }
    }
}
