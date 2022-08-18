$(document).ready(function () {

    const $form = $('#createAddressForm');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })

    $('.address-fields', $form).on('change', '#addressTypeCode', function (event) {

        $('.mb-3', $form).hide();
        $('.mb-3 input', $form).prop('disabled', true);

        const $fields = $('.address-type-' + $('option:selected', $(this)).data('id'), $form);
        $fields.show();
        $('input', $fields).prop('disabled', false)
    })
    $('#addressTypeCode', $form).trigger('change')


    $('#addressId', $form).on('change', function (event) {
        if ($(this).val()) {
            $.ajax({
                type: 'post',
                url: 'get-address-fields',
                data: {addressId: $(this).val()},
                dataType: 'html',
                success: function (html) {
                    $('.address-fields', $form).html(html);
                    $('#addressTypeCode', $form).trigger('change')
                }
            })
        }
    })

    $('form').on('submit', function (event) {
        event.preventDefault();
        const $field = $(this).data('field');
        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'html',
            success: function (html) {
                console.log(html);
                $('#' + $field).html(html);
            }
        })
    })
});
