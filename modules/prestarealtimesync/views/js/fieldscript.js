// Get the div element by its ID
document.addEventListener('DOMContentLoaded', function() {
    var divElement = document.getElementsByName('ErpPassword');
    var el = $('input[name="ErpPassword"]');
    el.on('focus', function(){
        el.attr('type', 'password')
    });
});