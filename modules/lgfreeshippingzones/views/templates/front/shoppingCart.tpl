{**
 * Copyright 2024 LÍNEA GRÁFICA E.C.E S.L.
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *}

{*<link href="{$content_dir|escape:'htmlall':'UTF-8'}modules/lgfreeshippingzones/views/css/lgfreeshippingzones.css" rel="stylesheet" type="text/css" />*}
{if !$lgfreshippingzones_is_ajax}
<div id="lgfreshippingzones-container">
{/if}
<script>
    var lg_fs_url = "{$lg_fs_url|escape:'html':'UTF-8'}";
</script>
{include file='./_partials/shipping_info.tpl'}
{if $show_debug}
    {if $lgfreeshipping_ps_version == '17'}
        {include file='./_partials/debug_info_17.tpl'}
    {else}
        {include file='./_partials/debug_info.tpl'}
    {/if}
{/if}
{if !$lgfreshippingzones_is_ajax}
</div>
{/if}
