$(document).ready(() => {
    $('input[name="receptmode"]').on('change', function (ev) {
        console.log($(this).val());
        if ($(this).val() == 0) {
            $('#gc-step-receptmode .step-action .btn').attr('disabled', false);
        } else {
            if ($('input[name="mailto"]').hasClass('valid')) {
                $('#gc-step-receptmode .step-action .btn').attr('disabled', false);
            } else {
                $('#gc-step-receptmode .step-action .btn').attr('disabled', true);
            }
        }
    });

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    $('input[name="mailto"]').on('keyup change', function () {
        let getEmail = $(this).val();
        if (isEmail(getEmail)) {
            $('#gc-step-receptmode .step-action .btn').attr('disabled', false);
        } else {
            $('#gc-step-receptmode .step-action .btn').attr('disabled', true);
        }
    });

    $(document).on('click', 'button[data-rel-gcstep]', function () {
        $(this).closest('.step').removeClass('step-current').addClass('step-complete').next('.step').removeClass('step-current step-complete').addClass('step-current');
    });

    $(document).on('click', '#formgiftcard .step .step-edit', function () {
        $('.step.step-current').removeClass('step-current');
        $(this).closest('.step').removeClass('step-complete').addClass('step-current');
    });
});