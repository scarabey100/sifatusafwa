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

<div id="menubar">
    {if false && $bootstrap}<fieldset>{else}<div class="panel">{/if}
            <a id="buttonfreeshipping" class="button btn btn-default" style="width:280px;">
                <i class="icon-table"></i> {l s='Free shipping configuration' mod='lgfreeshippingzones'}
            </a>
            <a id="buttonconfiguration" class="button btn btn-default" style="width:280px;">
                <i class="icon-wrench"></i> {l s='Additional configuration' mod='lgfreeshippingzones'}
            </a>
        {if false && $bootstrap}</fieldset>{else}</div>{/if}
</div>
