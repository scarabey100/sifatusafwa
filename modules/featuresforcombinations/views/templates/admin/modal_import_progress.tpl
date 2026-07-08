{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *}
<div class="modal-body">
  <div class="alert alert-warning" id="import_details_stop" style="display:none;">
    {l s='Aborting, please wait...' mod='featuresforcombinations'}
  </div>
  <p id="import_details_progressing">
    {l s='Importing your data...' mod='featuresforcombinations'}
  </p>
  <div class="alert alert-success" id="import_details_finished" style="display:none;">
    {l s='Data imported!' mod='featuresforcombinations'}
    <br/>
    {l s='Look at your listings to make sure it\'s all there as you wished.' mod='featuresforcombinations'}
  </div>
  <div id="import_messages_div" style="max-height:250px; overflow:auto;">
    <div class="alert alert-danger" id="import_details_error" style="display:none;">
      {l s='Errors occurred:' mod='featuresforcombinations'}<br/><ul></ul>
    </div>
    <div class="alert alert-warning" id="import_details_post_limit" style="display:none;">
      {l s='Warning, the current import may require a PHP setting update, to allow more data to be transferred. If the current import stops before the end, you should increase your PHP "post_max_size" setting to ' mod='featuresforcombinations'}
      <span id="import_details_post_limit_value">16</span>MB
      {l s='at least, and try again.' mod='featuresforcombinations'}
    </div>
    <div class="alert alert-warning" id="import_details_warning" style="display:none;">
      {l s='Some errors were detected. Please check the details:' mod='featuresforcombinations'}<br/><ul></ul>
    </div>
    <div class="alert alert-info" id="import_details_info" style="display:none;">
      {l s='We made the following adjustments:' mod='featuresforcombinations'}<br/><ul></ul>
    </div>
  </div>

  <div id="import_validate_div" style="margin-top:17px;">
    <div class="pull-right" id="import_validation_details" default-value="{l s='Validating data...' mod='featuresforcombinations'}">
      &nbsp;
    </div>
    <div class="progress active progress-striped" style="display: block; width: 100%">
      <div class="progress-bar progress-bar-info" role="progressbar" style="width: 0%" id="validate_progressbar_done">
        <span><span id="validate_progression_done">0</span>% {l s='validated' mod='featuresforcombinations'}</span>
      </div>
      <div class="progress-bar progress-bar-info" role="progressbar" id="validate_progressbar_next" style="opacity: 0.5 ;width: 0%">
        <span class="sr-only">{l s='Processing next page...' mod='featuresforcombinations'}</span>
      </div>
    </div>
  </div>

  <div id="import_progress_div" style="display:none;">
    <div class="pull-right" id="import_progression_details" default-value="{l s='Importing your data...' mod='featuresforcombinations'}">
      &nbsp;
    </div>
    <div class="progress active progress-striped" style="display: block; width: 100%">
      <div class="progress-bar progress-bar-info" role="progressbar" style="width: 0%" id="import_progressbar_done2">
        <span>{l s='Linking accessories...' mod='featuresforcombinations'}</span>
      </div>
      <div class="progress-bar progress-bar-success" role="progressbar" style="width: 0%" id="import_progressbar_done">
        <span><span id="import_progression_done">0</span>% {l s='imported' mod='featuresforcombinations'}</span>
      </div>
      <div class="progress-bar progress-bar-success progress-bar-stripes active" role="progressbar" id="import_progressbar_next" style="opacity: 0.5 ;width: 0%">
        <span class="sr-only">{l s='Processing next page...' mod='featuresforcombinations'}</span>
      </div>
    </div>
  </div>

  <div class="modal-footer">
    <div class="input-group pull-right">
      <button type="button" class="btn btn-primary" tabindex="-1" id="import_continue_button" style="display: none;">
        {l s='Ignore warnings and continue?' mod='featuresforcombinations'}
      </button>
      &nbsp;
      <button type="button" class="btn btn-default" tabindex="-1" id="import_stop_button">
        {l s='Abort import' mod='featuresforcombinations'}
      </button>
      &nbsp;
      <button type="button" class="btn btn-success" data-dismiss="modal" tabindex="-1" id="import_close_button" style="display: none;">
        {l s='Close' mod='featuresforcombinations'}
      </button>
    </div>
  </div>
</div>
