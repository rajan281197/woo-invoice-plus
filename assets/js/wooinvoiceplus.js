jQuery( document ).ready( function( $ ) {

    console.log("admin enqueue");

    jQuery('#woo_invoiceplus_settings').click( function( event ) {

        console.log("clicked button");

        event.preventDefault(); // Prevent form submission

        var is_pdf_generating               = jQuery('#pdf-generating-functionality').val() ? jQuery('#pdf-generating-functionality').val() : 'enabled_pdf_generation';
        var get_pdf_bg_color                = jQuery('#woo_invoice_bg_color').val() ? jQuery('#woo_invoice_bg_color').val() : '#ec0808';
        var woo_invoice_billing             = jQuery('#woo_invoice_billing-address').val() ? jQuery('#woo_invoice_billing-address').val() : 'enabled_billing_address';
        var woo_invoice_shipping            = jQuery('#woo_invoice_shipping-address').val() ? jQuery('#woo_invoice_shipping-address').val() : 'enabled_shipping_address';
        var display_paymethod               = jQuery('#woo_invoice_display_paymethod').val() ? jQuery('#woo_invoice_display_paymethod').val() : 'enabled_paymethod';
        var display_orderdate               = jQuery('#woo_invoice-order-date').val() ? jQuery('#woo_invoice-order-date').val() : 'enabled_order_date';
        var display_emailaddress            = jQuery('#woo_invoice_email_address').val() ? jQuery('#woo_invoice_email_address').val() : 'enabled_email_address';
        var display_phonenumber             = jQuery('#woo_invoice_phone_number').val() ? jQuery('#woo_invoice_phone_number').val() : 'enabled_phone_number';
        var display_coupon_code             = jQuery('#woo_invoice-coupon-code').val() ? jQuery('#woo_invoice-coupon-code').val() : 'enabled_coupon_code';  
        var display_discount_amount         = jQuery('#woo_invoice-discount-amount').val() ? jQuery('#woo_invoice-discount-amount').val() : 'enabled_discount_amount';  
        var display_order_customer_note     = jQuery('#display_order_customer_note').val() ? jQuery('#display_order_customer_note').val() : 'enabled_order_customer_note';  
        // console.log(is_pdf_generating);
        // console.log(get_pdf_bg_color);
        // console.log(woo_invoice_billing);
        // console.log(woo_invoice_shipping);
        // console.log(display_paymethod);
        // console.log(display_orderdate);
        // console.log(display_emailaddress);
        // console.log(display_phonenumber);

        jQuery.ajax({
            type    : "post",
            url     : wooinvoiceplus_ajax_object.wooinvoiceplus, // Use the appropriate AJAX URL
            data: {
                action                  :   'save_global_settings_wooinvoiceplus',
                is_pdf_generating       :   is_pdf_generating,
                get_pdf_bg_color        :   get_pdf_bg_color,
                woo_invoice_billing     :   woo_invoice_billing,
                woo_invoice_shipping    :   woo_invoice_shipping,
                display_paymethod       :   display_paymethod,
                display_orderdate       :   display_orderdate,
                display_emailaddress    :   display_emailaddress,
                display_phonenumber     :   display_phonenumber,   
                display_coupon_code     :   display_coupon_code,  
                display_discount_amount :   display_discount_amount,  
                display_order_customer_note : display_order_customer_note,
            },
            beforeSend: function() {
                jQuery("#woo_invoiceplus_settings").prop("disabled", true);
            },
            success : function(response) {
                // Handle success response
                console.log(response);
                if (response.success) {
                    // Handle success response
                    console.log(response.data);
                } else {
                    // Handle error response
                    console.log(response.data);
                }
            },
            error   : function(xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            },
            complete: function() {
                // Perform any tasks after the AJAX request is complete
                jQuery("#woo_invoiceplus_settings").prop("disabled", false);
            }
        });

    });

});
