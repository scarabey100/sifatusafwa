/**
 * 2024 (c) Egio digital
 *
 * MODULE EgHomeHadithProducts
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

$(document).ready(function () {
 
 
    // Function to add a new row
    function addNewRow(id, produit,check = false) {
        // Get existing IDs as an array from #productIds_new input
        var existingIds = $('#productIds_new').val().split(',').map(id => id.trim()); // Trim whitespace from IDs

        // Check if the ID already exists in the #productIds_new input
        if (!existingIds.includes(id.toString()) || check) {
            if (!check) {
                // If the ID does not exist, add it to the existing IDs
                existingIds.push(id); // Add the new ID 
                // Update the input with the new list of IDs, removing leading commas
                $('#productIds_new').val(existingIds.join(',').replace(/^,+/, '')); // Remove leading commas
            }
            // Create the new row HTML
            var newRow = `
                <tr id="${id}" class="odd">
                    <td class="pointer">
                        <div class="position-drag-gallery">
                            <i class="material-icons">drag_indicator</i>
                        </div>
                    </td>
                    <td class="pointer">${id}</td>
                    <td class="pointer">${produit}</td>
                    <td class="pointer">
                        <div class="btn-group-action">
                            <div class="btn-group">
                                <a href="#" data-egid="${id}" class="btn tooltip-link product-edit link-icon-delete delete-new" data-toggle="pstooltip" title="" data-placement="right" data-original-title="Supprimer définitivement cette item.">
                                    <i class="material-icons">delete</i>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            `;

            // Append the new row to the tbody
            $('.selected_products_row_position').append(newRow);
        } 
    }


    // Get the chosen products
    var chosenProducts = $('#productIds_new').val();
    
    if (chosenProducts) {
        var chosenProductsArray = chosenProducts.split(',');
       
        chosenProductsArray.forEach(function(id) {
            var option = $('#productIds_choose').find('option[value="' + id + '"]');
           
            if (option.length) {
                var produit = option.text();
                addNewRow(id, produit,true);
            }
        });
    } 

    // Add product on button click
    $(document).on('click', '#add_product_new', function (e) {
        var id = $("#productIds_choose").val();
        var produit = $("#productIds_choose").find("option[value='"+id+"']").text();
        addNewRow(id, produit);
    });

    // Remove product on delete button click
    $(document).on('click', '.delete-new', function (e) {
        e.preventDefault(); // Prevent default action
        var rowId = $(this).data('egid'); // Get the ID of the product to remove

        // Remove the row from the table
        $('#' + rowId).remove(); 

        // Get the current value of #productIds_new
        var existingIds = $('#productIds_new').val().split(','); // Split the input value into an array

        var updatedIds = []; // Create a new array for updated IDs

        // Iterate over existing IDs and add non-matching IDs to updatedIds
        existingIds.forEach(function(id) {
            if (parseInt(id) !== parseInt(rowId)) {
                updatedIds.push(id); // Add only if it does not match rowId
            }
        }); 
        // Update the #productIds_new input with the new list of IDs
        $('#productIds_new').val(updatedIds.join(',')); // Join the array back into a comma-separated string
    });

});
