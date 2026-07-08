$(document).ready(function() {
    $('#addYourOwnIpBtn').click(addYourOwnIp)
})

function addYourOwnIp() {
    $('#IP').val(currentUserIp)
}