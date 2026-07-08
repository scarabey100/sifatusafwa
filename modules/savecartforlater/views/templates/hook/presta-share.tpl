{**
* 2008-2024 Prestaworld
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one website.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
*
* DISCLAIMER
*
* Do not alter or add/update to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author    prestaworld
* @copyright 2008-2024 Prestaworld
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
* International Registered Trademark & Property of prestaworld
*}

 <div id="presta-share" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Share Cart' mod='savecartforlater'}</h4>
            </div>
            <div class="modal-body">
                <form id="prestaform" method="POST" class="form-horizontal" role="form" action="">
                    <div id="prestaerror" class="alert alert-danger hidecontent"></div>
                    <div id="prestasuccess" class="alert alert-success hidecontent"></div>
                    <div class="clearfix form-group">
                        <label class="control-label">{l s='Name' mod='savecartforlater'}</label>
                        <div class="col-xs-12 col-md-12">
                            <input id="presta-name" type="text" class="form-control" name="presta-name" placeholder="{l s='Name' mod='savecartforlater'}">
                        </div>
                    </div>
                    <div class="clearfix form-group">
                        <label class="control-label">{l s='Email' mod='savecartforlater'}</label>
                        <div class="col-xs-12 col-md-12">
                            <input id="presta-email" type="text" class="form-control" name="presta-email" placeholder="{l s='demo@demo.com' mod='savecartforlater'}">
                        </div>
                    </div>
                    <div class="clearfix form-group">
                        <label class="control-label">{l s='Message' mod='savecartforlater'}</label>
                        <div class="col-xs-12 col-md-12">
                            <textarea id="presta-textarea" class="form-control" name="presta-textarea" placeholder="{l s='Sharing cart with you' mod='savecartforlater'}"></textarea>
                        </div>
                    </div>
                    <div class="clearfix form-group">
                        <div class="col-md-6 nopaddingleft">
                            <button type="submit" id="presta-btn-login" class="btn btn-success">{l s='Send' mod='savecartforlater'}</button>
                            <img class="hidecontent prestaloader" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/loading.gif" width="25">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
