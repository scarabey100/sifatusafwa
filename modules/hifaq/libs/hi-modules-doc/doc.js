$(function() {
    $(document)
        .on('click', '.hi-module-whats-this a', function(e) {
            e.preventDefault();

            let doc = $(this).attr('data-doc');

            $('#hmd-modal .hmd-item').hide();
            $('#hmd-modal .hmd-item[data-doc="'+doc+'"]').show();
            $('#hmd-modal').addClass('hmd-sidebar-open');
        })
        .on('click', '.hmd-dismiss-modal', function(e) {
            e.preventDefault();

            $('#hmd-modal').removeClass('hmd-sidebar-open');
        })
        .on('click', window, function() {
            $('#hmd-modal').removeClass('hmd-sidebar-open');
        })
        .on('click', '#hmd-modal, .hi-module-whats-this a, .mfp-container', function(e) {
            e.stopPropagation();
        });
    
    $('.hmd-image-item').magnificPopup({
        type:'image',
    });
});