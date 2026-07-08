/**
 *  @author    Rekire <info@rekire.com>
 *  @copyright Rekire
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */


var ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}
ready(() => {
    /* General configuration */
    let type = document.getElementById('type');
    let categories_option = document.getElementById('category_option');
    let manufacturer_option = document.getElementById('manufacturer_option');

    if (type.value != 1) {  /* 1 = hook, 2 = shortcode */
        let hookName = document.getElementById('hook_name');
        let labelHookName = hookName.closest(".form-group").querySelector('label');
        hookName.disabled = true;
        hookName.required = false;
        labelHookName.classList.remove('required');

        document.getElementById('filter_page_visibility_off').click();
        document.getElementById('filter_page_visibility_on').disabled = true;
    }

    if (categories_option.value != 2) { /* 1 = todos , 2 = especificar categoría , 3 = categoría actual */
        let categories_tree = document.querySelector('[data-tab-id="general"] #categories-tree');
        let form_categories_tree = categories_tree.closest(".form-group");
        form_categories_tree.classList.add('hidden');
    }

    if (manufacturer_option.value != 2) { /* 1 = todos , 2 = especificar fabricante, 3 = fabricante actual */
        let manufacturer = document.getElementById('id_manufacturer');
        let form_manufacturer = manufacturer.closest(".form-group");
        form_manufacturer.classList.add('hidden');
    }

    let image_type = document.getElementById('image_type');
    let indice;
    indice = products_images.findIndex(o => o.name == image_type.value);
    image_type.nextElementSibling.textContent = products_images[indice]['width'] + ' X ' + products_images[indice]['height'];

    let hook = document.getElementById('hook_name');
    if (hook.value.indexOf('displayRkrSelledPlus') !== -1) {
        hook.nextElementSibling.textContent = hook_description_custom + "{hook h='" + hook.value + "'}";
    } else if (hook.value === 'displayProductAdditionalInfo') {
        hook.nextElementSibling.textContent = hook_description + ' ' + hook_cart_disabled;
    } else {
        hook.nextElementSibling.textContent = hook_description;
    }

    type.addEventListener("change", (e) => {
        let labelHookName = hook.closest(".form-group").querySelector('label');

        if (e.currentTarget.value != 1) { /* 1 = hook, 2 = shortcode */
            hook.disabled = true;
            hook.required = false;
            labelHookName.classList.remove('required');

            document.getElementById('filter_page_visibility_off').click();
            document.getElementById('filter_page_visibility_on').disabled = true;
        } else {
            hook.disabled = false;
            hook.required = true;
            labelHookName.classList.add('required');

            document.getElementById('filter_page_visibility_on').disabled = false;
        }
    });

    hook.addEventListener("change", (e) => {
        if (e.currentTarget.value.indexOf('displayRkrSelledPlus') !== -1) {
            e.currentTarget.nextElementSibling.textContent = hook_description_custom + " {hook h='" + hook.value + "'}";
        } else if (e.currentTarget.value === 'displayProductAdditionalInfo') {
            e.currentTarget.nextElementSibling.textContent = hook_description + ' ' + hook_cart_disabled;
        } else {
            e.currentTarget.nextElementSibling.textContent = hook_description;
        }
    });

    categories_option.addEventListener("change", (e) => {
        let categories_tree = document.querySelector('[data-tab-id="general"] #categories-tree');
        let form_categories_tree = categories_tree.closest(".form-group");
        let label_categories_tree = form_categories_tree.querySelector('label');
        if (e.currentTarget.value == 2) { /* 1 = todos , 2 = especificar categoria , 3 = categoria actual */
            label_categories_tree.classList.add('required');
            form_categories_tree.classList.remove('hidden');
        } else {
            label_categories_tree.classList.remove('required');
            form_categories_tree.classList.add('hidden');
        }
    });

    manufacturer_option.addEventListener("change", (e) => {
        let manufacturer = document.getElementById('id_manufacturer');
        let form_manufacturer = manufacturer.closest(".form-group");
        let label_manufacturer = form_manufacturer.querySelector('label');
        if (e.currentTarget.value == 2) { /* 1 = todos , 2 = especificar fabricante, 3 = fabricante actual */
            label_manufacturer.classList.add('required');
            form_manufacturer.classList.remove('hidden');
        } else {
            label_manufacturer.classList.remove('required');
            form_manufacturer.classList.add('hidden');
        }
    });

    image_type.addEventListener("change", (e) => {
        let indice;
        indice = products_images.findIndex(o => o.name == image_type.value);
        e.currentTarget.nextElementSibling.textContent = products_images[indice]['width'] + ' X ' + products_images[indice]['height'];
    });

    /* set visibility */
    let visibilities = document.querySelectorAll('[data-tab-id="visibility"]');
    let visibility_on = document.getElementById('filter_page_visibility_on');
    let visibility_off = document.getElementById('filter_page_visibility_off');
    let page_category_on = document.getElementById('page_category_on');
    let page_category_off = document.getElementById('page_category_off');
    let categories_filter_tree = document.querySelector('[data-tab-id="visibility"] #categories-tree-filter');
    let form_categories_filter_tree = categories_filter_tree.closest(".form-group");
    let label_categories_filter_tree = form_categories_filter_tree.querySelector('label');
    let page_manufacturer_on = document.getElementById('page_manufacturer_on');
    let page_manufacturer_off = document.getElementById('page_manufacturer_off');
    let manufacturer_filter = document.getElementById('filter_id_manufacturer');
    let form_manufacturer_filter = manufacturer_filter.closest(".form-group");
    let label_manufacturer_filter = form_manufacturer_filter.querySelector('label');

    if (visibility_off.checked == true) {
        visibilities.forEach(form_visibility => {
            let visibility = form_visibility.querySelector('#filter_page_visibility_on');
            if (!visibility) {
                form_visibility.classList.add('hidden');
            }
        });
    } else {
        if (page_category_off.checked == true) {
            label_categories_filter_tree.classList.remove('required');
            form_categories_filter_tree.classList.add('hidden');
        } else {
            label_categories_filter_tree.classList.add('required');
            form_categories_filter_tree.classList.remove('hidden');
        }
        if (page_manufacturer_off.checked == true) {
            label_manufacturer_filter.classList.remove('required');
            form_manufacturer_filter.classList.add('hidden');
        } else {
            label_manufacturer_filter.classList.add('required');
            form_manufacturer_filter.classList.remove('hidden');
        }
    }

    visibility_on.addEventListener('change', (event) => {
        visibilities.forEach(form_visibility => {
            let visibility = form_visibility.querySelector('#filter_page_visibility_on');
            let category_filter = form_visibility.querySelector('#filter_id_categories');
            let manufacturer_filter = form_visibility.querySelector('#filter_id_manufacturer');
            if (!visibility ||
                (category_filter != null && page_category_on.checked === true) ||
                (manufacturer_filter != null && page_manufacturer_on.checked === true)
            ) {
                form_visibility.classList.remove('hidden');
            }
        });
    });

    visibility_off.addEventListener('change', (event) => {
        visibilities.forEach(form_visibility => {
            let visibility = form_visibility.querySelector('#filter_page_visibility_on');
            if (!visibility) {
                form_visibility.classList.add('hidden');
            }
        });
    });

    page_category_on.addEventListener('change', (event) => {
        if (event.currentTarget.value == 1) {
            label_categories_filter_tree.classList.add('required');
            form_categories_filter_tree.classList.remove('hidden');
        }
    });

    page_category_off.addEventListener('change', (event) => {
        if (event.currentTarget.value == 0) {
            label_categories_filter_tree.classList.remove('required');
            form_categories_filter_tree.classList.add('hidden');
        }
    });

    page_manufacturer_on.addEventListener('change', (event) => {
        if (event.currentTarget.value == 1) {
            label_manufacturer_filter.classList.add('required');
            form_manufacturer_filter.classList.remove('hidden');
        }
    });

    page_manufacturer_off.addEventListener('change', (event) => {
        if (event.currentTarget.value == 0) {
            label_manufacturer_filter.classList.remove('required');
            form_manufacturer_filter.classList.add('hidden');
        }
    });

    /* styles  */
    let sizes = document.querySelectorAll('.rem-size');
    let default_style_on = document.getElementById('default_style_on');
    if (default_style_on.checked == true) {
        sizes.forEach(size => {
            size.disabled = true;
        });
    }

    let default_style_off = document.getElementById('default_style_off');
    if (default_style_off.checked == true) {
        sizes.forEach(size => {
            size.disabled = false;
        });
    }

    default_style_on.addEventListener('change', (event) => {
        sizes.forEach(size => {
            size.disabled = true;
        });
    });

    default_style_off.addEventListener('change', (event) => {
        sizes.forEach(size => {
            size.disabled = false;
        });
    });

    sizes.forEach(size => {
        size.addEventListener('keypress', function handleKeyPress(evt) {
            if (!onlyNumberKeyAndPoint(evt)) {
                evt.preventDefault();
            }
        });
    });

    function onlyNumberKeyAndPoint(evt) {
        let ASCIICode = (evt.which) ? evt.which : evt.keyCode;
        if (ASCIICode > 31 && (((ASCIICode < 48 && ASCIICode !== 46) || ASCIICode > 57)))
            return false;
        return true;
    }

});
