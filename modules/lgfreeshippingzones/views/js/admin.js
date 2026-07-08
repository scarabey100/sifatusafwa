/**
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
 */

document.addEventListener("DOMContentLoaded", function(){
    $("#freeshipping").show(); $("#configuration").hide();
    $("#buttonfreeshipping").removeClass("btn-default").addClass("btn-primary");
    $("#buttonconfiguration").removeClass("btn-primary").addClass("btn-default");
    $("#buttonfreeshipping").click(function(){
        $("#freeshipping").show(); $("#configuration").hide();
        $("#buttonfreeshipping").removeClass("btn-default").addClass("btn-primary");
        $("#buttonconfiguration").removeClass("btn-primary").addClass("btn-default");
    });
    $("#buttonconfiguration").click(function(){
        $("#freeshipping").hide(); $("#configuration").show();
        $("#buttonfreeshipping").removeClass("btn-primary").addClass("btn-default");
        $("#buttonconfiguration").removeClass("btn-default").addClass("btn-primary");
    });
    $("[name^='act_carrier_']").click(function(){
        var id = $(this).attr('id');
        var rad = $("[name^='def_carrier_'][id='" + id + "']");
        $(rad).attr('checked', false);
    });
});

function addDebugIp()
{
    fetch('https://api.ipify.org?format=json')
    .then(response => response.json())
    .then(data => {
        // console.log(data.ip);
        $('#debugip').val(data.ip);
    })
    .catch(error => {
        console.log('Error:', error);
    });
}
