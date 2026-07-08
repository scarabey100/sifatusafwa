{**
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
 *}
{extends file='page.tpl'}
{block name='head_seo_title'}{$meta_title}{/block}
{block name='head_seo_description'}{$meta_description}{/block}
{block name='head_seo_keywords'}{$meta_keywords}{/block}
{block name='page_content'}
    {include file='module:hifaq/views/templates/front/faq.tpl'}
{/block}
