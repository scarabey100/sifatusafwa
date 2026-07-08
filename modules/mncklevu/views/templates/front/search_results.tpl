{**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{extends file=$layout}

{block name='head' append}
    {include file='module:mncklevu/views/templates/front/_partials/custom_templates.tpl'}
{/block}

{block name='content'}
    <section id="main">
        {block name='product_list_header'}
            <h1 class="page_heading mb-3">{$page_title}</h1>
        {/block}

        {block name='klevu_landing'}
            <div class="klevuLanding"></div>
        {/block}
    </section>
{/block}
