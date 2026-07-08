// extraordercolumns_back.js

$( document ).ready(function() {
    $('#order_grid_table tr').each(function () {
        let actionTypeTd = $(this).find('td.action-type.column-actions .btn-group-action .btn-group');
        let idOrder = parseInt($(this).find('.identifier-type.column-id_order').data('identifier'), 10);
        if (actionTypeTd.length) {
            let newLink = $('<a>', {
                class: 'btn tooltip-link js-link-row-action dropdown-item tracking-number-action',
                href: 'javascript:void(0);',
                onclick: 'extraOrderColumnsPopup('+idOrder+');',
            });

            $('<i>', { class: 'material-icons', text: 'rv_hookup' }).appendTo(newLink);

            actionTypeTd.append(newLink);
        }
    });

    $('body').on('submit', '.extraOrderColumnsPopup', function(event) {
        event.preventDefault();
        $('.clogElement').show();
        $.ajax({
            url: extraordercolumns_ajax_link,
            data: {
                ajax: true,
                action: 'create',
                idOrder: $('[name="order_original_id_order"]').val(),
                trackingNumber: $('[name="tracking_number"]').val(),
            },
            success : function(result){
                console.log(result);
                // location.reload(true);
                // swal.close();
            }
        });
    });

});

function extraOrderColumnsPopup(idOrder)
{
    $.ajax({
        url: extraordercolumns_ajax_link,
        data: {
            ajax: true,
            action: 'popup',
            idOrder: idOrder
        },
        success : function(result){
            Swal.fire({
                html: result,
                showConfirmButton: false,
                width: 600,
            });
        }
    });
}
