jQuery(document).ready(function($) {

    console.log("admin enqueue");

    jQuery('#woo_invoiceplus_settings').click(function(event) {

        console.log("clicked button");

        event.preventDefault(); // Prevent form submission
        // var fileInput = jQuery('#pdf_logo_upload')[0].files[0];
        
        var formData = new FormData();
        formData.append('action', 'save_global_settings_wooinvoiceplus');
        formData.append('nonce', wooinvoiceplus_ajax_object.nonce);

        var attachmentId = jQuery('#header_logo').val();
        formData.append('header_logo_attachment_id', attachmentId);
        formData.append('is_pdf_generating', jQuery('#pdf-generating-functionality').val() || 'enabled_pdf_generation');
        formData.append('is_pdf_backend_preview', jQuery('#pdf-generating-functionality-backend-preview-status').val() || 'backend_enabled_pdf_preview');
        formData.append('is_pdf_papersize', jQuery('#pdf-generating-papersize').val() || 'a4');
        formData.append('is_pdf_fontfamily', jQuery('#pdf-generating-fontfamily').val() || 'times-roman');
        jQuery('.checkbox-wrapper input[type="checkbox"]').each(function() {
            if (jQuery(this).is(':checked')) {
                formData.append('pdf_attach_to_order_status[]', jQuery(this).val());
            } else {
                // Append an empty value or any other indicator for unchecked checkboxes
                formData.append('pdf_attach_to_order_status[]', '');
            }
        });
        
        
        formData.append('is_pdf_password_protected', jQuery('#pdf-password-protection-status').val() || 'no_password');
        formData.append('is_pdf_orientation', jQuery('#pdf-generating-orientation').val() || 'portrait');
        formData.append('is_pdf_generating_backend', jQuery('#pdf-generating-functionality-backend-order-detail').val() || 'backend_enabled_pdf_generation');
        formData.append('is_pdf_generating_myaccount', jQuery('#pdf-generating-functionality-myaccount-order-detail').val() || 'myaccount_enabled_pdf_generation');
        formData.append('get_pdf_bg_color', jQuery('#woo_invoice_bg_color').val() || '#ec0808');
        formData.append('woo_invoice_billing', jQuery('#woo_invoice_billing-address').val() || 'enabled_billing_address');
        formData.append('woo_invoice_shipping', jQuery('#woo_invoice_shipping-address').val() || 'enabled_shipping_address');
        formData.append('display_paymethod', jQuery('#woo_invoice_display_paymethod').val() || 'enabled_paymethod');
        formData.append('display_orderdate', jQuery('#woo_invoice-order-date').val() || 'enabled_order_date');
        formData.append('display_emailaddress', jQuery('#woo_invoice_email_address').val() || 'enabled_email_address');
        formData.append('display_phonenumber', jQuery('#woo_invoice_phone_number').val() || 'enabled_phone_number');
        formData.append('display_coupon_code', jQuery('#woo_invoice-coupon-code').val() || 'enabled_coupon_code');
        formData.append('display_discount_amount', jQuery('#woo_invoice-discount-amount').val() || 'enabled_discount_amount');
        formData.append('display_order_customer_note', jQuery('#display_order_customer_note').val() || 'enabled_order_customer_note');
        formData.append('get_pdf_subheading_color', jQuery('#woo_invoice_subheading_color').val() || '#000000');

        jQuery.ajax({
            type: "post",
            url: wooinvoiceplus_ajax_object.wooinvoiceplus, // Use the appropriate AJAX URL
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                jQuery("#woo_invoiceplus_settings").prop("disabled", true);
            },
            success: function(response) {
                // Handle success response
                console.log(response);
                if (response.success) {
                    // Handle success response
                    console.log(response.data);
                    location.reload();

                } else {
                    // Handle error response
                    console.log(response.data);
                }
                location.reload();

            },
            error: function(xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            },
            complete: function() {
                // Perform any tasks after the AJAX request is complete
                jQuery("#woo_invoiceplus_settings").prop("disabled", false);
            }
        });

    });

    jQuery('#woo_invoiceplus_reset_settings').click(function(event) {

        console.log("clicked button");

        event.preventDefault(); // Prevent form submission
        // var fileInput = jQuery('#pdf_logo_upload')[0].files[0];
        
        var formData = new FormData();
        formData.append('nonce', wooinvoiceplus_ajax_object.nonce);
        formData.append('action', 'reset_plugin_settings');
        formData.append('pdf_logo_upload', jQuery('#pdf_logo_upload').val() || '');
        formData.append('is_pdf_generating', jQuery('#pdf-generating-functionality').val() || 'enabled_pdf_generation');
        formData.append('is_pdf_backend_preview', jQuery('#pdf-generating-functionality-backend-preview-status').val() || 'backend_enabled_pdf_preview');
        formData.append('is_pdf_papersize', jQuery('#pdf-generating-papersize').val() || 'a4');
        formData.append('is_pdf_fontfamily', jQuery('#pdf-generating-fontfamily').val() || 'times-roman');
        jQuery('.checkbox-wrapper input[type="checkbox"]').each(function() {
            if (jQuery(this).is(':checked')) {
                formData.append('pdf_attach_to_order_status[]', jQuery(this).val());
            } else {
                // Append an empty value or any other indicator for unchecked checkboxes
                formData.append('pdf_attach_to_order_status[]', '');
            }
        });
        
        formData.append('is_pdf_password_protected', jQuery('#pdf-password-protection-status').val() || 'no_password');
        formData.append('is_pdf_orientation', jQuery('#pdf-generating-orientation').val() || 'portrait');
        formData.append('is_pdf_generating_backend', jQuery('#pdf-generating-functionality-backend-order-detail').val() || 'backend_enabled_pdf_generation');
        formData.append('is_pdf_generating_myaccount', jQuery('#pdf-generating-functionality-myaccount-order-detail').val() || 'myaccount_enabled_pdf_generation');
        formData.append('get_pdf_bg_color', jQuery('#woo_invoice_bg_color').val() || '#ec0808');
        formData.append('woo_invoice_billing', jQuery('#woo_invoice_billing-address').val() || 'enabled_billing_address');
        formData.append('woo_invoice_shipping', jQuery('#woo_invoice_shipping-address').val() || 'enabled_shipping_address');
        formData.append('display_paymethod', jQuery('#woo_invoice_display_paymethod').val() || 'enabled_paymethod');
        formData.append('display_orderdate', jQuery('#woo_invoice-order-date').val() || 'enabled_order_date');
        formData.append('display_emailaddress', jQuery('#woo_invoice_email_address').val() || 'enabled_email_address');
        formData.append('display_phonenumber', jQuery('#woo_invoice_phone_number').val() || 'enabled_phone_number');
        formData.append('display_coupon_code', jQuery('#woo_invoice-coupon-code').val() || 'enabled_coupon_code');
        formData.append('display_discount_amount', jQuery('#woo_invoice-discount-amount').val() || 'enabled_discount_amount');
        formData.append('display_order_customer_note', jQuery('#display_order_customer_note').val() || 'enabled_order_customer_note');
        formData.append('get_pdf_subheading_color', jQuery('#woo_invoice_subheading_color').val() || '#000000');

        jQuery.ajax({
            type: "post",
            url: wooinvoiceplus_ajax_object.wooinvoiceplus, // Use the appropriate AJAX URL
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                jQuery("#woo_invoiceplus_reset_settings").prop("disabled", true);
            },
            success: function(response) {
                // Handle success response
                console.log(response);
                if (response.success) {
                    // Handle success response
                    console.log(response.data);
                    location.reload();

                } else {
                    // Handle error response
                    console.log(response.data);
                }
                location.reload();

            },
            error: function(xhr, status, error) {
                // Handle error response
                console.log(xhr.responseText);
            },
            complete: function() {
                // Perform any tasks after the AJAX request is complete
                jQuery("#woo_invoiceplus_reset_settings").prop("disabled", false);
            }
        });

    });

     // Function to handle media uploader button click
     $('.wpo_upload_image_button').click(function(event) {
        event.preventDefault();

        var $button = $(this);
        var fileFrame = wp.media.frames.fileFrame = wp.media({
            title: $button.data('uploader_title'),
            button: {
                text: $button.data('uploader_button_text')
            },
            multiple: false
        });

        fileFrame.on('select', function() {
            
            var attachment = fileFrame.state().get('selection').first().toJSON();

            // Set the image preview
            $('#img-header_logo').attr('src', attachment.url).show();

            // Set the attachment ID in the hidden input field
            $('#header_logo').val(attachment.id);

            // Show the remove image button
            $('.wpo_remove_image_button').show();
        });

        fileFrame.on('open', function() {
            // Get the attachment to preselect
            var attachmentId = jQuery('#header_logo').val();

            var attachmentIdToSelect = attachmentId; // Replace 522 with the ID of your desired image
            var selection = fileFrame.state().get('selection');
            var attachment = wp.media.attachment(attachmentIdToSelect);
    
            // Preselect the desired image
            attachment.fetch();
            selection.add(attachment);
        });

        
        fileFrame.open();
    });

    // When the "Remove image" button is clicked
     // Function to handle remove image button click
     $('.wpo_remove_image_button').click(function(event) {
        event.preventDefault();

        // Clear the image preview
        $('#img-header_logo').attr('src', '').hide();

        // Clear the attachment ID in the hidden input field
        $('#header_logo').val('');

        // Hide the remove image button
        $(this).hide();
    });
    
    $('#pdf-preview-button').on('click', function() {
        var pdfUrl = wooinvoiceplus_ajax_object.default_pdf;
        checkPdfPassword(pdfUrl);
    });

    // Function to check if PDF is password-protected
    function checkPdfPassword(pdfUrl) {
        // Use a HEAD request to check if PDF is password-protected
        $.ajax({
            type: 'HEAD',
            url: pdfUrl,
            success: function(data, textStatus, xhr) {
                // Check if the PDF has a Content-Disposition header indicating it's an attachment (password-protected)
                var contentDisposition = xhr.getResponseHeader('Content-Disposition');
                if (contentDisposition && contentDisposition.indexOf('attachment') !== -1) {
                    // Prompt user for password
                    var password = prompt('Enter PDF password:');
                    if (password !== null && password !== '') {
                        // Open the PDF in FancyBox with the provided password
                        $.fancybox.open({
                            src: pdfUrl,
                            type: 'iframe',
                            opts: {
                                iframe: {
                                    preload: false,
                                    attr: {
                                        scrolling: 'auto'
                                    }
                                },
                                pdf: {
                                    password: password
                                }
                            }
                        });
                    }
                } else {
                    // Open the PDF directly in FancyBox
                    $.fancybox.open({
                        src: pdfUrl,
                        type: 'iframe',
                        iframe: {
                            preload: false,
                            attr: {
                                scrolling: 'auto'
                            }
                        }
                    });
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error('Error checking PDF password:', errorThrown);
            }
        });
    }

    


});
