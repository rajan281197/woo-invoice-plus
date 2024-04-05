jQuery(document).ready(function(jQuery) {
    // AJAX request to generate the PDF invoice
    jQuery('.generate-invoice-btn').on('click', function() {
        var order_id = jQuery(this).data('order-id');
        var data = {
            'action': 'generate_invoice',
            'order_id': order_id
        };
        jQuery.ajax({
            url: invoice_generator_ajax.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    // Redirect to the generated PDF URL
                    location.reload();
                } else {
                    console.log('PDF generation failed');
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });
});
