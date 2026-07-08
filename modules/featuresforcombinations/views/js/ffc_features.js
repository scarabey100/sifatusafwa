/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2025 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$(document).on('click', '.ffc-add-feature', function () {
    let ffc_content = $(this).parents('.ffc-features').find('.ffc-features-content');
    let ffc_prototype = $(ffc_content).data('prototype').replace(/__iter__/g, $(ffc_content).data('iter'));
    $(ffc_content).data('iter', $(ffc_content).data('iter') + 1);
    $(ffc_content).find('.ffc-feature-collection').append(ffc_prototype);
});

$(document).on('click', '.ffc-feature-collection .delete', function () {
    $(this).closest('.row.product-feature').remove();
});

$(document).ready(() => {
    $(document).on('change', '.ffc-feature-selector', function () {
        let id_feature = $(this).val();
        let row = $(this).closest('.row.product-feature');
        let feature_value_selector = $(row).find('select.ffc-feature-value-selector');

        $(feature_value_selector).val('').attr('disabled', 'disabled').find('option[value!=""]').remove();

        $.post(admin_ffc_ajax_url, {
            id_feature: id_feature,
            action: 'get_feature_values',
            ajax: true,
            controller: 'AdminFeaturesForCombinations'
        }, (feature_values) => {
            feature_values = JSON.parse(feature_values);

            let options = [];
            $.each(feature_values, (i, v) => {
                options.push('<option value="' + v.id_feature_value + '">' + v.value + '</option>');
            });

            $(feature_value_selector).append(options.join('')).removeAttr('disabled');
        })
    });
});
