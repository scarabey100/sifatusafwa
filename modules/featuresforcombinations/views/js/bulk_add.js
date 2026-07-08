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

$(document).ready(() => {
    const ffc_collectionContainer = $('#ffc_container');
    const ffc_collectionRowsContainer = $('#ffc_container > .ffc_rows');
    const ffc_collectionRow = 'div.product-feature';
    const ffc_addFeatureValue = '.feature-value-add-button';
    const ffc_deleteFeatureValue = 'button.delete-feature-value';
    const ffc_customValueInput = 'input.custom-values';
    const ffc_featureValueSelect = 'select.ffc-feature-value-selector';
    const ffc_featureSelect = 'select.ffc-feature-selector';
    const ffc_customFeatureIdInput = 'input.custom-value-id';
    const ffc_updateSubmitButtonState = 'updateSubmitButtonState';
    const ffc_importButton = '#import';
    let iter = 0;

    $('#FFC_attribute_group').on('change', function() {
        ffc_collectionRowsContainer.empty();
        $(ffc_importButton).hide();
        if ($(this).val() != 0) {
            const ffcProto = ffc_collectionContainer.data('prototype');

            $.post(admin_ffc_ajax_url, {
                id_attribute_group: $(this).val(),
                action: 'get_attributes',
                ajax: true,
                controller: 'AdminFeaturesForCombinations'
            }, (attributes) => {
                attributes = JSON.parse(attributes);
                const addButton = `<button type="button" class="btn btn-outline-primary ffc-add-feature ffc_add_feature_button">${addFeatureLabel}</button>`;
                $(ffc_importButton).show();

                $.each(attributes, (i, v) => {
                    ffc_collectionRowsContainer.append(
                        `<div class="form-group row" data-attribute-id="${v.id_attribute}"><div style="display: flex; justify-content: space-between;margin-bottom: 1rem"><h4>${v.name}</h4>${addButton}</div></div><hr>`
                    );
                });
            });
        }
    });

    $(ffc_collectionRowsContainer).on('click', '.ffc_add_feature_button', function() {
        const ffc_toAppend = $(this).parent().parent();
        const ffc_paId = ffc_toAppend.data('attribute-id');
        const ffcProto = ffc_collectionContainer.data('prototype');
        const newRow = $(ffcProto.replaceAll('__PROTOTYPE_PA_ID__', ffc_paId).replaceAll('__iter__', iter));

        console.log('dennis proto', ffc_toAppend, ffc_paId);
        ffc_toAppend.append(newRow);
        iter++;
    });

    const ffc_renderFeatureValueChoices = ($featureValueSelector, idFeature) => {
        if (!idFeature) {
            $featureValueSelector.prop('disabled', true);

            return;
        }
        $.get(admin_ffc_ajax_url, {
            id_feature: idFeature,
            action: 'get_feature_values',
            ajax: true,
            controller: 'AdminFeaturesForCombinations'
        }, (feature_values) => {
            const featureValuesData = JSON.parse(feature_values);
            $featureValueSelector.prop('disabled', featureValuesData.length === 0);
            $featureValueSelector.empty();
            $.each(featureValuesData, (index, featureValue) => {
                $featureValueSelector
                    .append($('<option></option>')
                        .attr('value', featureValue.id_feature_value)
                        .text(featureValue.value));
            });
        })
    }
    // $(ffc_addFeatureValue).on('click', () => {
    //     const prototype = ffc_collectionContainer.data('prototype');
    //     const prototypeName = ffc_collectionContainer.data('prototypeName');
    //     const newIndex = $(ffc_collectionRow, ffc_collectionContainer).length;
    //
    //     const $newRow = $(prototype.replace(new RegExp(prototypeName, 'g'), newIndex));
    //     ffc_collectionRowsContainer.append($newRow);
    //     $('select[data-toggle="select2"]', $newRow).select2();
    //     ffc_onUpdateEmit();
    // });

    $(ffc_collectionContainer).on('click', ffc_deleteFeatureValue, (event) => {
        const $deleteButton = $(event.currentTarget);
        const $collectionRow = $deleteButton.closest(ffc_collectionRow);
        $collectionRow.remove();
    });

    $(ffc_collectionContainer).on('keyup change', ffc_customValueInput, (event) => {
        const $changedInput = $(event.target);
        const $collectionRow = $changedInput.closest(ffc_collectionRow);

        // Check if any custom inputs has a value
        let hasCustomValue = false;
        $(ffc_customValueInput, $collectionRow).each((index, input) => {
            const $input = $(input);

            if ($input.val() !== '') {
                hasCustomValue = true;
            }
        });

        const $featureValueSelector = $(ffc_featureValueSelect, $collectionRow).first();
        $featureValueSelector.prop('disabled', hasCustomValue);
        if (hasCustomValue) {
            $featureValueSelector.val('0');
        } else {
            const $featureInput = $(ffc_featureSelect, $collectionRow).first();
            const featureId = Number($featureInput.val());
            ffc_renderFeatureValueChoices($featureValueSelector, featureId);
        }
    });

    $(ffc_collectionContainer).on('change', ffc_featureSelect, (event) => {
        const $selector = $(event.target);
        const idFeature = Number($selector.val());
        const $collectionRow = $selector.closest(ffc_collectionRow);
        const $featureValueSelector = $(ffc_featureValueSelect, $collectionRow).first();
        const $customValueInputs = $(ffc_customValueInput, $collectionRow);
        const $customFeatureIdInput = $(ffc_customFeatureIdInput, $collectionRow);

        $(ffc_customValueInput, $collectionRow).prop('disabled', idFeature === 0);
        // Reset values
        $customValueInputs.val('');
        $featureValueSelector.val('0');
        $customFeatureIdInput.val('');

        ffc_renderFeatureValueChoices($featureValueSelector, idFeature);
    });
});
