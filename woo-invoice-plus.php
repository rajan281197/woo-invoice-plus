<?php
/**
 * @package WooInvoicePlus
 */
/**
 * Plugin Name:          Woo Invoice Plus
 * Description:          Woo Invoice Plus is a powerful WooCommerce plugin that seamlessly integrates into your online store to provide a hassle-free solution for generating and downloading invoices in PDF format. With just a single click, your customers can access and download professional invoices directly from the order thank you page. This user-friendly plugin enhances the post-purchase experience by offering a convenient way for customers to obtain and archive their order invoices. It also enables you, as the store owner, to generate and download order invoice PDFs directly from the backend order detail page. Simply navigate to the order details in the WooCommerce admin area and find a new metabox that allows you to generate and download the invoice PDF for any order. This feature is particularly useful when you need to provide order invoices to customers or keep a record of your sales. Woo Invoice Plus simplifies the process, ensuring that you can effortlessly retrieve and store important purchase documentation with ease.
 * Version:              1.0.0
 * Author:               <a href="https://profiles.wordpress.org/rajanpanchal2028/">Rajan Panchal</a>
 * Author URI:           https://profiles.wordpress.org/rajanpanchal2028/
 * License:              GPLv2 or later
 * License URI:          https://opensource.org/licenses/gpl-license.php
 * Text Domain:          woo-invoice-plus
 * WC requires at least: 3.0
 * WC tested up to:      7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly
}

class WooInvoicePlus
{

   
    public function __construct()
    {
        // add_action('admin_init', array($this, 'check_dependencies'));
        add_action('admin_notices', array($this, 'dependency_notice'));
        // add_action('admin_notices', array($this, 'check_woo_activated_or_not'));


        if (!isset($_GET['post_type']) || $_GET['post_type'] !== 'shop_order') {

            if(get_option('is_pdf_generating') === 'enabled_pdf_generation') :

                if((is_array(get_option('pdf_attach_to_order_status')) && in_array('cancelled_order', get_option('pdf_attach_to_order_status')))):

                    add_action( 'woocommerce_order_status_cancelled', array($this, 'generate_pdf_on_order_placement'), 10, 1);
                
                endif;

                if((is_array(get_option('pdf_attach_to_order_status')) && in_array('failed_order', get_option('pdf_attach_to_order_status')))):

                    add_action( 'woocommerce_order_status_failed', array($this, 'generate_pdf_on_order_placement'), 10, 1);

                endif;

                if((is_array(get_option('pdf_attach_to_order_status')) && in_array('customer_on_hold_order', get_option('pdf_attach_to_order_status')))):

                    add_action( 'woocommerce_order_status_on-hold', array($this, 'generate_pdf_on_order_placement'), 10, 1);
                
                endif;

                // if((is_array(get_option('pdf_attach_to_order_status')) && in_array('customer_on_hold_order', get_option('pdf_attach_to_order_status')))):

                    //     add_action('woocommerce_order_status_pending', array($this, 'generate_pdf_on_order_placement'), 10, 1);

                // endif;

                if((is_array(get_option('pdf_attach_to_order_status')) && in_array('customer_processing_order', get_option('pdf_attach_to_order_status')))):

                    add_action( 'woocommerce_order_status_processing', array($this, 'generate_pdf_on_order_placement'), 10, 1);

                endif;

                if((is_array(get_option('pdf_attach_to_order_status')) && in_array('customer_completed_order', get_option('pdf_attach_to_order_status')))):

                    add_action( 'woocommerce_order_status_completed', array($this, 'generate_pdf_on_order_placement'), 10, 1);

                endif;

                if((is_array(get_option('pdf_attach_to_order_status')) && in_array('customer_refunded_order', get_option('pdf_attach_to_order_status')))):

                    add_action( 'woocommerce_order_status_refunded', array($this, 'generate_pdf_on_order_placement'), 10, 1);

                endif;

            endif;


            if(get_option('is_pdf_generating_backend') === 'backend_enabled_pdf_generation') :

                add_action('add_meta_boxes', array($this, 'add_invoice_metabox'));

            endif;

            if(get_option('is_pdf_generating_myaccount') === 'myaccount_enabled_pdf_generation') :

                add_action( 'woocommerce_order_details_after_order_table', array($this, 'add_custom_text_after_order_table'), 10, 1 );

            endif;


        }

        add_action('init', array($this, 'handle_invoice_download'));
        add_action( 'admin_menu', array( $this, 'register_wooinvoiceplus_menu' ) );

        // Enqueue styles for the option page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_option_page_styles'));

        add_action( 'wp_ajax_save_global_settings_wooinvoiceplus', array( $this, 'save_global_settings_wooinvoiceplus' ) );
        add_action( 'wp_ajax_nopriv_save_global_settings_wooinvoiceplus', array( $this, 'save_global_settings_wooinvoiceplus' ) );

        add_action( 'wp_ajax_reset_plugin_settings', array( $this, 'reset_plugin_settings' ) );
        add_action( 'wp_ajax_nopriv_reset_plugin_settings', array( $this, 'reset_plugin_settings' ) );

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));

        add_action( 'woocommerce_email_before_order_table', array( $this,'add_custom_text_to_new_order_email'), 20, 4 );
        add_filter( 'woocommerce_email_attachments', array( $this,'send_attach_pdf_to_emails'), 10, 4 );

        if(get_option('is_pdf_generating') === 'enabled_pdf_generation') :

            add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'woo_invoice_my_account_order_action'), 9999, 2 );

        endif;

        add_filter( 'manage_edit-shop_order_columns', array($this,'custom_shop_order_column') );
        add_filter( 'manage_shop_order_posts_custom_column', array($this,'custom_shop_order_column_content') );

        add_action( 'wp_ajax_generate_invoice', array( $this, 'generate_invoice_callback' ) );


        register_activation_hook(__FILE__, array($this, 'woo_invoice_plugin_activation'));
    }



    // public function check_dependencies()
    // {
    //     if (!class_exists('WooCommerce')) {
    //         deactivate_plugins(plugin_basename(__FILE__));
    //     }
    // }

    public function dependency_notice()
    {
        if (!class_exists('WooCommerce')) {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('WooCommerce is not installed / Activated. Please install and activate WooCommerce to use Woo Invoice Plus.', 'woo-invoice-plus');
            echo '</p></div>';
    
            deactivate_plugins(plugin_basename(__FILE__));
        }
    }

    public function woo_invoice_my_account_order_action( $actions, $order ) {
        // if ( $order->has_status( 'completed' ) ) {
            $order_id = $order->get_id();
            $invoice_exists = $this->check_invoice_exists($order_id);

            $invoice_exists_meta = get_post_meta($order_id, '_temp_pdf_path', true);

            if ($invoice_exists && $invoice_exists_meta) {

                    $download_url = $this->get_invoice_download_url($order_id);

                    $actions['order-again'] = array(
                        'url' => wp_nonce_url( esc_url($download_url), 'woo-invoice-plus' ),
                        'name' => __( 'Woo Invoice', 'woocommerce' ),
                    );
                // }
            }
            return $actions;
    }

    // Add custom column header
    public function custom_shop_order_column( $columns ) {
        $columns['invoice_download'] = __( 'Invoice Download', 'woo-invoice-plus' );
        return $columns;
    }
 
    // Add content to the custom column
    public function custom_shop_order_column_content( $column ) {
        global $post;
    
        if ( $column == 'invoice_download' ) {
            $order_id = $post->ID;
            $invoice_exists = get_post_meta( $order_id, '_temp_pdf_path', true );
            $invoice_exists_url = $this->check_invoice_exists($order_id);

    
            if ( $invoice_exists && $invoice_exists_url ) {
                $download_url = $invoice_exists; // Assuming the download URL is stored in '_temp_pdf_path' meta data
    
                printf(
                    '<a href="%s" class="button">%s</a>',
                    esc_url( $download_url ),
                    esc_html__( 'Download Invoice', 'woo-invoice-plus' )
                );
    
                $is_pdf_password_protected = get_post_meta( $order_id, '_pdf_password_protected', true );
    
                if ( $is_pdf_password_protected ) {
                    $customer_first_name = get_post_meta( $order_id, '_billing_first_name', true );
    
                    // Get the first 4 letters of the customer's first name
                    $customer_first_name_4_letters = strtoupper( substr( $customer_first_name, 0, 4 ) );
    
                    // Set the PDF password as the customer's first 4 letters of their name followed by the order ID
                    $pdf_password = $customer_first_name_4_letters . $order_id;
    
                    printf(
                        '<p class="invoice-password-info">%s %s</p>',
                        esc_html__( 'Your Password to view Invoice PDF is:', 'woo-invoice-plus' ),
                        esc_html( $pdf_password )
                    );
                }
            } else {
                echo esc_html__( 'Invoice not available', 'woo-invoice-plus' );
            }
        }
    }
    

    public function send_attach_pdf_to_emails( $attachments, $email_id, $order, $email ) {
        // echo "<pre>";
        // print_r($email_id);
        // echo "</pre>";
        // exit;
        $pdf_attach_to_order_status = maybe_unserialize(get_option('pdf_attach_to_order_status'));

        if (is_array($pdf_attach_to_order_status) && in_array($email_id, $pdf_attach_to_order_status)) {

                $order_id = $order->get_id();
                $upload_dir = wp_upload_dir();
                
                $attachments[] = $upload_dir['basedir'] . '/Woo Invoice PDF/' . $order_id . '.pdf';

            return $attachments;

        }
    }

    // Add custom text to new order email
    public function add_custom_text_to_new_order_email( $order, $sent_to_admin, $plain_text, $email ) {
        // Only add custom text to the customer email
        // echo "<pre>";
        // print_r($email);
        // print_r($email->id);
        // echo "</pre>";
        // if ( $email->id == 'customer_invoice' || $email->id == 'customer_completed_order' || $email->id == 'new_order' ) {
        // $pdf_attach_to_order_status = print_r(maybe_unserialize(get_option('pdf_attach_to_order_status')));
        $pdf_attach_to_order_status = maybe_unserialize(get_option('pdf_attach_to_order_status'));

        if (is_array($pdf_attach_to_order_status) && in_array($email->id, $pdf_attach_to_order_status)) {
            $order_id = $order->get_id();

            $is_pdf_password_protected = get_post_meta($order_id, '_pdf_password_protected', true);

            if ($is_pdf_password_protected) {

                $customer_first_name = $order->get_billing_first_name();

                // Get the first 4 letters of the customer's first name
                $customer_first_name_4_letters = strtoupper( substr( $customer_first_name, 0, 4 ) );

                // Get the order ID
                $order_id = $order->get_id();

                // Set the PDF password as the customer's first 4 letters of their name followed by the order ID
                $pdf_password = $customer_first_name_4_letters . $order_id;

                echo '<h2 class="email-upsell-title">' . esc_html__('Your Password to view Invoice PDF is: ', 'woo-invoice-plus') . esc_html($pdf_password) . '</h2>';

            }
        }
    }

    

    // Define the function to reset settings
    public function reset_plugin_settings() {
        // Check if the option is not already set
        // if (get_option('pdf_logo_path')) {
             // Verify nonce
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'save_global_settings_nonce' ) ) {
                wp_send_json_error( 'Invalid nonce' );
            }

            delete_option('pdf_logo_path');
        // }
            update_option('is_pdf_backend_preview', 'backend_enabled_pdf_preview');

            update_option('is_pdf_generating', 'enabled_pdf_generation');

            update_option('is_pdf_papersize', 'a4');

            update_option('is_pdf_fontfamily', 'times-roman');

            update_option('is_pdf_password_protected', 'no_password');

            update_option('is_pdf_orientation', 'portrait');
      
            update_option('is_pdf_generating_backend', 'backend_enabled_pdf_generation');

            update_option('is_pdf_generating_myaccount', 'myaccount_enabled_pdf_generation');
        
            update_option('get_pdf_bg_color', '#e10505');

            update_option('get_pdf_subheading_color', '#000000');

            update_option('woo_invoice_billing', 'enabled_billing_address');

            update_option('woo_invoice_shipping', 'enabled_shipping_address');

            update_option('display_paymethod', 'enabled_paymethod');

            update_option('display_orderdate', 'enabled_order_date');

            update_option('woo_invoice_email_address', 'enabled_email_address');

            update_option('woo_invoice_phone_number', 'enabled_phone_number');

            update_option('display_coupon_code', 'enabled_coupon_code');

            update_option('display_discount_amount', 'enabled_discount_amount');

            update_option('display_order_customer_note', 'enabled_order_customer_note');

            // Your array a containing allowed values
            $reset_order_status = array(
                0 => "new_order",
                4 => "customer_processing_order"
            );

            update_option('pdf_attach_to_order_status', $reset_order_status);



            // Load the Dompdf library
            
            // Get the order object
            
            // Check if the order is already set to "processing" or "completed" status
            // if ($order->has_status(array('processing', 'completed'))) {
                //     return;
                // }
                
                // // Update the order status to "processing"
                // $order->update_status('processing');
                
                // Generate the PDF content
        if (get_option('is_pdf_backend_preview') === 'backend_enabled_pdf_preview') {
            require 'vendor/autoload.php';
        
                // Generate the PDF content using the order data
                // You can customize the content based on your requirements

                $content = '<html>
                <head>
                <meta charset="UTF-8">

                <style>
                    body {
                        font-family: ' . (get_option('is_pdf_fontfamily') ? get_option('is_pdf_fontfamily') : 'times-roman') . '; /* Replace Arial with your desired font family */
                    }
                    table {
                    width: 100%;
                    border-collapse: collapse;
                    }

                    #header,
                    #footer {
                    position: fixed;
                    left: 0;
                        right: 0;
                        color: #aaa;
                        font-size: 0.9em;
                    }

                    #header,
                    #footer {
                    position: fixed;
                    left: 0;
                        right: 0;
                        color: #aaa;
                        font-size: 0.9em;
                    }

                    #header {
                    top: 0;
                        border-bottom: 0.1pt solid #aaa;
                    }

                    #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                    }

                    #header table,
                    #footer table {
                        width: 100%;
                        border-collapse: collapse;
                        border: none;
                    }

                    #header td,
                    #footer td {
                    padding: 0;
                        width: 50%;
                    }

                    .page-number {
                    text-align: center;
                    }

                    .page-number:before {
                    content: "Page " counter(page);
                    }

                    hr {
                    page-break-after: always;
                    border: 0;
                    }

                </style>
                </head>
                        <body>';

                // Get the promo code and discount amount
                $get_pdf_heading_color      = get_option('get_pdf_bg_color') ? get_option('get_pdf_bg_color') : '#000000';
                $get_pdf_subheading_color   = get_option('get_pdf_subheading_color') ? get_option('get_pdf_subheading_color') : '#000000';
                $get_pdf_logo               = get_option('pdf_logo_path') ? esc_url(wp_get_attachment_url(get_option('pdf_logo_path'))) : '';

                $content .= '<div id="header">
                                <table>
                                    <tr><
                                    td></td>
                                    <td style="text-align: right;">';

                if (!empty($get_pdf_logo)) {
                    $response = wp_remote_get($get_pdf_logo);
                
                    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                        $imgtest = wp_remote_retrieve_body($response);
                        $img = base64_encode($imgtest);
                
                        if (!empty($img)) {
                            $content .= '<a href="' . esc_url(get_site_url()) . '"><img src="data:image;base64,' . $img . '"></a>';
                        }
                    }
                }
                
                
                $content .= '</td></tr></table>
                                </div>
                                
                                <div id="footer">
                                    <div class="page-number"></div>
                                </div>';
                $content .= '<h2>
                            <u style="color:' . $get_pdf_heading_color .'">Order Details of <span style="background: yellow;">#' . 1234567890 . '</span></u>
                            </h2>';
                $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;color:' . $get_pdf_subheading_color .'"">Order Number</th>
                                <td style="padding: 8px;border: 1px solid black;"><span style="background: yellow;">#' . 1234567890 . '</span></td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Order Total</th>
                                <td style="padding: 8px;border: 1px solid black;">' . ' $33.00 ' . '</td>
                            </tr>';

                                

                                if(get_option('display_coupon_code') === 'enabled_coupon_code') :

                                    $content .= '<tr>
                                                    <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Promo Code</th>
                                                    <td style="padding: 8px;border: 1px solid black;">' . 'BOGO777' . '</td>
                                                </tr>';

                                endif;

                                if(get_option('display_discount_amount') === 'enabled_discount_amount') :

                                    $content .= '<tr>
                                                    <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Discount Amount</th>
                                                    <td style="padding: 8px;border: 1px solid black;">' . wc_price(50) . '</td>
                                                </tr>';

                                endif;

                $content .= '</table>';

                // Get customer information

                // Get customer name and email
               

                $content .= '<h3><u>Customer Information</u></h3>';
                $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;">Customer Name</th>
                                <td style="padding: 8px;border: 1px solid black;">' . 'Loren Epsum' . '</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;">Customer Email</th>
                                <td style="padding: 8px;border: 1px solid black;">' . 'lorenepsum777@gmail.com' . '</td>
                            </tr>
                        </table>';


                $content .= '<h3><u style="color:' . $get_pdf_heading_color .'">Order Items</u></h3>';

                // Add order items
                $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Product</th>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Quantity</th>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Total</th>
                            </tr>';

          
                    $content .= '<tr>
                                <td style="padding: 8px;border: 1px solid black;">' . 'Album' . '</td>
                                <td style="padding: 8px;border: 1px solid black;">' . '1' . '</td>
                                <td style="padding: 8px;border: 1px solid black;">' . wc_price(15) . '</td>
                            </tr><tr>
                            <td style="padding: 8px;border: 1px solid black;">' . 'Beanie' . '</td>
                            <td style="padding: 8px;border: 1px solid black;">' . '10' . '</td>
                            <td style="padding: 8px;border: 1px solid black;">' . wc_price(120) . '</td>
                        </tr>';

                $content .= '</table>';

                // Get payment method

                if(get_option('display_paymethod') === 'enabled_paymethod') :

                    $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                <tr>
                                    <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Payment Method</th>
                                    <td style="padding: 8px;border: 1px solid black;">' . 'Cash on delivery' . '</td>
                                </tr>
                            </table>';
                
                endif;

                
                if(get_option('display_orderdate') === 'enabled_order_date') :

                    // Get order created date
                    $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                <tr>
                                    <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Order Created Date</th>
                                    <td style="padding: 8px;border: 1px solid black;">' . 'March 21, 2024' . '</td>
                                </tr>
                            </table>';

                endif;

                if(get_option('display_order_customer_note') === 'enabled_order_customer_note') :

                    $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                    <tr>
                                        <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Order Note</th>
                                        <td style="padding: 8px;border: 1px solid black;">' . 'Can you help translate this site into a foreign language ? Please email us with details if you can help.' . '</td>
                                    </tr>
                                </table>';


                endif;

                if(get_option('woo_invoice_shipping') === 'enabled_shipping_address') :

                    // Get shipping details
                        $content .= '<h3><u style="color:' . $get_pdf_heading_color .'">Shipping Details</u></h3>';
                        $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                    <tr>
                                        <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Shipping Address</th>
                                        <td style="padding: 8px;border: 1px solid black;">' . 
                                        'Verda Gleason
                                        Klein, Krajcik and Harris
                                        1499 Towne Vista
                                        626 Swift Route
                                        Chula Vista, CA 33980' 
                                        . '</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Shipping Method</th>
                                        <td style="padding: 8px;border: 1px solid black;">' . 'Free shipping' . '</td>
                                    </tr>
                                </table>';

                endif;

                if(get_option('woo_invoice_billing') === 'enabled_billing_address') :

                    // Get billing details
                        $content .= '<h3><u style="color:' . $get_pdf_heading_color .'">Billing Details</u></h3>';
                        $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                        <tr>
                                            <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Billing Address</th>
                                            <td style="padding: 8px;border: 1px solid black;">' . 
                                            'Verda Gleason
                                            Klein, Krajcik and Harris
                                            1499 Towne Vista
                                            626 Swift Route
                                            Chula Vista, CA 33980'
                                             . '</td>
                                        </tr>;';  
                                        if(get_option('woo_invoice_phone_number') === 'enabled_phone_number') :
                                        
                                            $content .= '<tr>
                                                            <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Phone number</th>
                                                            <td style="padding: 8px;border: 1px solid black;"><a href="tel:'.'157-841-7322'.'">' . '157-841-7322' . '</a></td>
                                                        </tr>;';
                                        endif;

                                        if(get_option('woo_invoice_email_address') === 'enabled_email_address') :
                                        
                                            $content .= '<tr>
                                                            <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Email Address</th>
                                                            <td style="padding: 8px;border: 1px solid black;">' . 'lorenepsum777@gmail.com' . '</td>
                                                        </tr>;';
                                        endif;


                        $content .= '</table>';

                endif;

                $content .= '</body>
                        </html>';

            // Get the WordPress filesystem
            global $wp_filesystem;

            // Initialize the WordPress filesystem if it's not already loaded
            if ( ! isset( $wp_filesystem ) ) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
            }

            if ( is_wp_error( $wp_filesystem ) ) {
                $error_message = $wp_filesystem->get_error_message();
                // Failed to initialize the WordPress filesystem, handle error here if needed
                echo 'Error: ' . esc_html( $error_message );
            } else {
                
                // Create a new Dompdf instance
                $dompdf = new Dompdf\Dompdf();

                // Load the PDF content
                $dompdf->loadHtml($content);

                // Set paper size and orientation
                $dompdf->setPaper(
                                    ( get_option('is_pdf_papersize') === 'a4') ? 'a4' : get_option('is_pdf_papersize'),
                                    ( get_option('is_pdf_orientation') === 'portrait') ? 'portrait' : get_option('is_pdf_orientation')
                                );

                // Render the PDF
                $dompdf->render();
                
                // $dompdf->stream("",array("Attachment"=> false));
                if(get_option('is_pdf_password_protected') === 'password_protected') : 
                    // Password protection
                        $cpdf = $dompdf->getCanvas()->get_cpdf();

                        // Set encryption with strong passwords:
                        // - User password (optional, for opening restrictions) - Use a complex password with at least 12 characters, including uppercase, lowercase, numbers, and symbols.
                        $userPassword = 'DEMO123';
                        // - Owner password (mandatory for full access) - Use a different, equally strong password.
                        $ownerPassword = 'DEMO123';
                        $allowedActions = ['print', 'copy']; // Adjust based on your requirements

                        $cpdf->setEncryption($userPassword, $ownerPassword, $allowedActions);

                endif;

                // Get the PDF output
                $pdf_output = $dompdf->output();


                $pdf_file_path = plugin_dir_path(__FILE__) . 'assets/pdf/woo-invoice-preview.pdf';

                // Write the PDF content to the file
                $write_result = $wp_filesystem->put_contents( $pdf_file_path, $pdf_output );

                // Check if the file write operation was successful
                if ( $write_result === false ) {
                    // Failed to write the PDF content to the file, handle error here if needed
                    echo 'Error: Failed to write PDF content to the file.';
                } else {
                    // File write successful, proceed with further actions if needed
                }
            }
        }
    }

     // Method to handle plugin activation
     public function woo_invoice_plugin_activation()
     {

        // Check if the option is not already set
        
        if (!get_option('is_pdf_generating')) {
            // Set the default value for the option
            update_option('is_pdf_generating', 'enabled_pdf_generation');
        }

        if (!get_option('is_pdf_backend_preview')) {
            // Set the default value for the option
            update_option('is_pdf_backend_preview', 'backend_enabled_pdf_preview');
        }

        if (!get_option('is_pdf_orientation')) {
            // Set the default value for the option
            update_option('is_pdf_orientation', 'portrait');
        }

        if (!get_option('is_pdf_papersize')) {
            // Set the default value for the option
            update_option('is_pdf_papersize', 'a4');
        }

        if (!get_option('is_pdf_password_protected')) {
            // Set the default value for the option
            update_option('is_pdf_password_protected', 'no_password');
        }

        if (!get_option('is_pdf_fontfamily')) {
            // Set the default value for the option
            update_option('is_pdf_fontfamily', 'times-roman');
        }


        if (!get_option('is_pdf_generating_backend')) {
            update_option('is_pdf_generating_backend', 'backend_enabled_pdf_generation');
        }

        if (!get_option('is_pdf_generating_myaccount')) {
            update_option('is_pdf_generating_myaccount', 'myaccount_enabled_pdf_generation');
        }
        
        if (!get_option('get_pdf_bg_color')) {
            update_option('get_pdf_bg_color', '#e10505');
        }

        if (!get_option('get_pdf_subheading_color')) {
            update_option('get_pdf_subheading_color', '#000000');
        }

        if (!get_option('woo_invoice_billing')) {
            update_option('woo_invoice_billing', 'enabled_billing_address');
        }

        if (!get_option('woo_invoice_shipping')) {
            update_option('woo_invoice_shipping', 'enabled_shipping_address');
        }

        if (!get_option('display_paymethod')) {
            update_option('display_paymethod', 'enabled_paymethod');
        }

        if (!get_option('display_orderdate')) {
            update_option('display_orderdate', 'enabled_order_date');
        }

        if (!get_option('woo_invoice_email_address')) {
            update_option('woo_invoice_email_address', 'enabled_email_address');
        }

        if (!get_option('woo_invoice_phone_number')) {
            update_option('woo_invoice_phone_number', 'enabled_phone_number');
        }

        if (!get_option('display_coupon_code')) {
            update_option('display_coupon_code', 'enabled_coupon_code');
        }

        if (!get_option('display_discount_amount')) {
            update_option('display_discount_amount', 'enabled_discount_amount');
        }

        if (!get_option('display_order_customer_note')) {
            update_option('display_order_customer_note', 'enabled_order_customer_note');
        }

        // Your array a containing allowed values
        if (!get_option('pdf_attach_to_order_status')) {
            $default_order_status = array(
                0 => "new_order",
                4 => "customer_processing_order"
            );
        
            update_option('pdf_attach_to_order_status', $default_order_status);
        }
        

     }

    public function add_settings_link($links)
    {
        // Add the settings link to the plugin actions
        $settings_link = '<a href="' . admin_url('?page=woo_invoice_general_settings') . '">Settings</a> | ';
        $settings_link .= '<a href="https://profiles.wordpress.org/rajanpanchal2028/#content-plugins" target="_blank">More plugins by Rajan</a>';
        array_push($links, $settings_link);
        
        return $links;
    }

    /**
	 * Registers a new settings page under Settings.
	 */
	public function register_wooinvoiceplus_menu() {
		add_menu_page(
			__( 'Woo Invoice Settings', 'woo-invoice-plus' ),
			__( 'Woo Invoice Settings', 'woo-invoice-plus' ),
			'manage_options',
			'woo_invoice_general_settings',
			array(
				$this,
				'woo_invoiceplus_page'
            ),
            'dashicons-pdf',
            4
		);
	}

    /**
	 * Settings page display callback.
	 */
	public function woo_invoiceplus_page() {
		// echo __( 'This is the page content', 'woo-invoice-plus' );
        ?>
		
    <?php 
        // print_r(implode(',',get_option('pdf_attach_to_order_status', '')));
    ?>
    <div class="form-container">

        <?php if(get_option('is_pdf_backend_preview') === 'backend_enabled_pdf_preview') : ?>
            <div class="pdf-preview-btn-container">
                <button id="pdf-preview-button" class="button button-primary button-large">
                    <?php esc_html_e('Preview PDF', 'woo-invoice-plus'); ?> 
                    <span class="dashicons dashicons-media-document"></span>
                </button>
            </div>
        <?php endif; ?>
        

        <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
        <form action="#" class="form-row" method="POST" enctype="multipart/form-data">
            <div class="col">
                <label for="pdf-generating-functionality">PDF Generating Functionality:</label>
                <select id="pdf-generating-functionality" name="pdf-generating-functionality">
                    <option value="enabled_pdf_generation" <?php echo (get_option('is_pdf_generating') === 'enabled_pdf_generation') ? 'selected' : ''; ?>>Enable PDF generating functionality</option>
                    <option value="disabled_pdf_generation" <?php echo (get_option('is_pdf_generating') === 'disabled_pdf_generation') ? 'selected' : ''; ?>>Disable PDF generating functionality</option>
                   
                </select>
            </div>

            <div class="col">
                <label for="pdf-generating-papersize">PDF Paper Size:</label>
                <select id="pdf-generating-papersize" name="pdf-generating-papersize">
                    <option value="a0" <?php echo (get_option('is_pdf_papersize') === 'a0') ? 'selected' : ''; ?>>A0 (4767.87x6740.79)</option>
                    <option value="a1" <?php echo (get_option('is_pdf_papersize') === 'a1') ? 'selected' : ''; ?>>A1 (3380.39x4767.87)</option>
                    <option value="a2" <?php echo (get_option('is_pdf_papersize') === 'a2') ? 'selected' : ''; ?>>A2 (2383.94x3370.39)</option>
                    <option value="a3" <?php echo (get_option('is_pdf_papersize') === 'a3') ? 'selected' : ''; ?>>A3 (1683.78x2383.94)</option>
                    <option value="a4" <?php echo (get_option('is_pdf_papersize') === 'a4') ? 'selected' : ''; ?>>A4 (1190.55x1683.78)</option>
                    <option value="a5" <?php echo (get_option('is_pdf_papersize') === 'a5') ? 'selected' : ''; ?>>A5 (841.89x1190.55)</option>
                    <option value="a6" <?php echo (get_option('is_pdf_papersize') === 'a6') ? 'selected' : ''; ?>>A6 (595.28x841.89)</option>
                    <option value="a7" <?php echo (get_option('is_pdf_papersize') === 'a7') ? 'selected' : ''; ?>>A7 (419.53x595.28)</option>
                    <option value="a8" <?php echo (get_option('is_pdf_papersize') === 'a8') ? 'selected' : ''; ?>>A8 (297.64x419.53)</option>
                    <option value="a9" <?php echo (get_option('is_pdf_papersize') === 'a9') ? 'selected' : ''; ?>>A9 (209.76x297.64)</option>
                    <option value="a10" <?php echo (get_option('is_pdf_papersize') === 'a10') ? 'selected' : ''; ?>>A10 (147.40x209.76)</option>
                    <option value="b0" <?php echo (get_option('is_pdf_papersize') === 'b0') ? 'selected' : ''; ?>>B0 (2834.65x4008.19)</option>
                    <option value="b1" <?php echo (get_option('is_pdf_papersize') === 'b1') ? 'selected' : ''; ?>>B1 (2004.09x2834.65)</option>
                    <option value="b2" <?php echo (get_option('is_pdf_papersize') === 'b2') ? 'selected' : ''; ?>>B2 (1417.32x2004.09)</option>
                    <option value="b3" <?php echo (get_option('is_pdf_papersize') === 'b3') ? 'selected' : ''; ?>>B3 (1000.63x1417.32)</option>
                    <option value="b4" <?php echo (get_option('is_pdf_papersize') === 'b4') ? 'selected' : ''; ?>>B4 (708.66x1000.63)</option>
                    <option value="b5" <?php echo (get_option('is_pdf_papersize') === 'b5') ? 'selected' : ''; ?>>B5 (498.90x708.66)</option>
                    <option value="b6" <?php echo (get_option('is_pdf_papersize') === 'b6') ? 'selected' : ''; ?>>B6 (354.33x498.90)</option>
                    <option value="b7" <?php echo (get_option('is_pdf_papersize') === 'b7') ? 'selected' : ''; ?>>B7 (249.45x354.33)</option>
                    <option value="b8" <?php echo (get_option('is_pdf_papersize') === 'b8') ? 'selected' : ''; ?>>B8 (175.75x249.45)</option>
                    <option value="b9" <?php echo (get_option('is_pdf_papersize') === 'b9') ? 'selected' : ''; ?>>B9 (124.72x175.75)</option>
                    <option value="b10" <?php echo (get_option('is_pdf_papersize') === 'b10') ? 'selected' : ''; ?>>B10 (87.87x124.72)</option>
                    <option value="c0" <?php echo (get_option('is_pdf_papersize') === 'c0') ? 'selected' : ''; ?>>C0 (2599.37x3676.54)</option>
                    <option value="c1" <?php echo (get_option('is_pdf_papersize') === 'c1') ? 'selected' : ''; ?>>C1 (1836.85x2599.37)</option>
                    <option value="c2" <?php echo (get_option('is_pdf_papersize') === 'c2') ? 'selected' : ''; ?>>C2 (1298.27x1836.85)</option>
                    <option value="c3" <?php echo (get_option('is_pdf_papersize') === 'c3') ? 'selected' : ''; ?>>C3 (918.43x1298.27)</option>
                    <option value="c4" <?php echo (get_option('is_pdf_papersize') === 'c4') ? 'selected' : ''; ?>>C4 (649.13x918.43)</option>
                    <option value="c5" <?php echo (get_option('is_pdf_papersize') === 'c5') ? 'selected' : ''; ?>>C5 (459.21x649.13)</option>
                    <option value="c6" <?php echo (get_option('is_pdf_papersize') === 'c6') ? 'selected' : ''; ?>>C6 (323.15x459.21)</option>
                    <option value="c7" <?php echo (get_option('is_pdf_papersize') === 'c7') ? 'selected' : ''; ?>>C7 (229.61x323.15)</option>
                    <option value="c8" <?php echo (get_option('is_pdf_papersize') === 'c8') ? 'selected' : ''; ?>>C8 (161.57x229.61)</option>
                    <option value="c9" <?php echo (get_option('is_pdf_papersize') === 'c9') ? 'selected' : ''; ?>>C9 (113.39x161.57)</option>
                    <option value="c10" <?php echo (get_option('is_pdf_papersize') === 'c10') ? 'selected' : ''; ?>>C10 (79.37x113.39)</option>
                    <option value="ra0" <?php echo (get_option('is_pdf_papersize') === 'ra0') ? 'selected' : ''; ?>>RA0 (2437.80x3458.27)</option>
                    <option value="ra1" <?php echo (get_option('is_pdf_papersize') === 'ra1') ? 'selected' : ''; ?>>RA1 (1729.13x2437.80)</option>
                    <option value="ra2" <?php echo (get_option('is_pdf_papersize') === 'ra2') ? 'selected' : ''; ?>>RA2 (1218.90x1729.13)</option>
                    <option value="ra3" <?php echo (get_option('is_pdf_papersize') === 'ra3') ? 'selected' : ''; ?>>RA3 (864.57x1218.90)</option>
                    <option value="ra4" <?php echo (get_option('is_pdf_papersize') === 'ra4') ? 'selected' : ''; ?>>RA4 (609.45x864.57)</option>
                    <option value="sra0" <?php echo (get_option('is_pdf_papersize') === 'sra0') ? 'selected' : ''; ?>>SRA0 (2551.18x3628.35)</option>
                    <option value="sra1" <?php echo (get_option('is_pdf_papersize') === 'sra1') ? 'selected' : ''; ?>>SRA1 (1814.17x2551.18)</option>
                    <option value="sra2" <?php echo (get_option('is_pdf_papersize') === 'sra2') ? 'selected' : ''; ?>>SRA2 (1275.59x1814.17)</option>
                    <option value="sra3" <?php echo (get_option('is_pdf_papersize') === 'sra3') ? 'selected' : ''; ?>>SRA3 (907.09x1275.59)</option>
                    <option value="sra4" <?php echo (get_option('is_pdf_papersize') === 'sra4') ? 'selected' : ''; ?>>SRA4 (637.80x907.09)</option>
                    <option value="letter" <?php echo (get_option('is_pdf_papersize') === 'letter') ? 'selected' : ''; ?>>Letter (612.00x792.00)</option>
                    <option value="half-letter" <?php echo (get_option('is_pdf_papersize') === 'half-letter') ? 'selected' : ''; ?>>Half-Letter (396.00x612.00)</option>
                    <option value="legal" <?php echo (get_option('is_pdf_papersize') === 'legal') ? 'selected' : ''; ?>>Legal (612.00x1008.00)</option>
                    <option value="ledger" <?php echo (get_option('is_pdf_papersize') === 'ledger') ? 'selected' : ''; ?>>Ledger (1224.00x792.00)</option>
                    <option value="tabloid" <?php echo (get_option('is_pdf_papersize') === 'tabloid') ? 'selected' : ''; ?>>Tabloid (792.00x1224.00)</option>
                    <option value="executive" <?php echo (get_option('is_pdf_papersize') === 'executive') ? 'selected' : ''; ?>>Executive (521.86x756.00)</option>
                    <option value="folio" <?php echo (get_option('is_pdf_papersize') === 'folio') ? 'selected' : ''; ?>>Folio (612.00x936.00)</option>
                    <option value="commercial #10 envelope" <?php echo (get_option('is_pdf_papersize') === 'commercial #10 envelope') ? 'selected' : ''; ?>>Commercial #10 Envelope (684x297)</option>
                    <option value="catalog #10 1/2 envelope" <?php echo (get_option('is_pdf_papersize') === 'catalog #10 1/2 envelope') ? 'selected' : ''; ?>>Catalog #10 1/2 Envelope (648x864)</option>
                    <option value="8.5x11" <?php echo (get_option('is_pdf_papersize') === '8.5x11') ? 'selected' : ''; ?>>8.5x11 (612.00x792.00)</option>
                    <option value="8.5x14" <?php echo (get_option('is_pdf_papersize') === '8.5x14') ? 'selected' : ''; ?>>8.5x14 (612.00x1008.00)</option>
                    <option value="11x17" <?php echo (get_option('is_pdf_papersize') === '11x17') ? 'selected' : ''; ?>>11x17 (792.00x1224.00)</option>
                    <option value="4a0" <?php echo (get_option('is_pdf_papersize') === '4a0') ? 'selected' : ''; ?>>4A0 (4767.87x6740.79)</option>
                    <option value="2a0" <?php echo (get_option('is_pdf_papersize') === '2a0') ? 'selected' : ''; ?>>2A0 (3370.39x4767.87)</option>
                   
                </select>
            </div>

            <div class="col">
                <label for="pdf-generating-orientation">PDF Paper View:</label>
                <select id="pdf-generating-orientation" name="pdf-generating-orientation">
                    <option value="landscape" <?php echo (get_option('is_pdf_orientation') === 'landscape') ? 'selected' : ''; ?>>Landscape</option>
                    <option value="portrait" <?php echo (get_option('is_pdf_orientation') === 'portrait') ? 'selected' : ''; ?>>Portrait</option>
                </select>
            </div>

            <div class="col">
                <label for="pdf-generating-fontfamily">PDF Font Family:</label>
                <select id="pdf-generating-fontfamily" name="pdf-generating-fontfamily">
                    <option value="dejavu serif" <?php echo (get_option('is_pdf_fontfamily') === 'dejavu serif') ? 'selected' : ''; ?>>Dejavu Serif</option>
                    <option value="dejavu sans mono" <?php echo (get_option('is_pdf_fontfamily') === 'dejavu sans mono') ? 'selected' : ''; ?>>Dejavu sans mono</option>
                    <option value="dejavu sans" <?php echo (get_option('is_pdf_fontfamily') === 'dejavu sans') ? 'selected' : ''; ?>>Dejavu sans</option>
                    <option value="fixed" <?php echo (get_option('is_pdf_fontfamily') === 'fixed') ? 'selected' : ''; ?>>Fixed</option>
                    <option value="monospace" <?php echo (get_option('is_pdf_fontfamily') === 'monospace') ? 'selected' : ''; ?>>Monospace</option>
                    <option value="symbol" <?php echo (get_option('is_pdf_fontfamily') === 'symbol') ? 'selected' : ''; ?>>Symbol</option>
                    <option value="zapfdingbats" <?php echo (get_option('is_pdf_fontfamily') === 'zapfdingbats') ? 'selected' : ''; ?>>Zapfdingbats</option>
                    <option value="helvetica" <?php echo (get_option('is_pdf_fontfamily') === 'helvetica') ? 'selected' : ''; ?>>Helvetica</option>
                    <option value="courier" <?php echo (get_option('is_pdf_fontfamily') === 'courier') ? 'selected' : ''; ?>>Courier</option>
                    <option value="times-roman" <?php echo (get_option('is_pdf_fontfamily') === 'times-roman') ? 'selected' : ''; ?>>Times-roman</option>
                    <option value="sans-serif" <?php echo (get_option('is_pdf_fontfamily') === 'sans-serif') ? 'selected' : ''; ?>>Sans-serif</option>
                </select>
            </div>

            <div class="col">
                <label for="pdf-generating-functionality-backend-preview-status">PDF Preview Status:</label>
                <select id="pdf-generating-functionality-backend-preview-status" name="pdf-generating-functionality-backend-preview-status">
                    <option value="backend_enabled_pdf_preview" <?php echo (get_option('is_pdf_backend_preview') === 'backend_enabled_pdf_preview') ? 'selected' : ''; ?>>Enable PDF Preview</option>
                    <option value="backend_disabled_pdf_preview" <?php echo (get_option('is_pdf_backend_preview') === 'backend_disabled_pdf_preview') ? 'selected' : ''; ?>>Disable PDF Preview</option>
                </select>
            </div>

            <div class="col">
                <label for="pdf-password-protection-status">PDF Password Protection:</label>
                <select id="pdf-password-protection-status" name="pdf-password-protection-status">
                    <option value="no_password" <?php echo (get_option('is_pdf_password_protected') === 'no_password') ? 'selected' : ''; ?>>No Password Protection</option>
                    <option value="password_protected" <?php echo (get_option('is_pdf_password_protected') === 'password_protected') ? 'selected' : ''; ?>>Password Protected</option>
                </select>
                <?php if(get_option('is_pdf_backend_preview') === 'backend_enabled_pdf_preview' && get_option('is_pdf_password_protected') === 'password_protected') : ?>
                    <p class="description">Preview PDF Password : <b style="color:green;">DEMO123</b></p>
                <?php endif; ?>

            </div>

            <div class="col">
                <label for="pdf-generating-functionality-backend-order-detail">Backend Order detail page:</label>
                <select id="pdf-generating-functionality-backend-order-detail" name="pdf-generating-functionality-backend-order-detail">
                    <option value="backend_enabled_pdf_generation" <?php echo (get_option('is_pdf_generating_backend') === 'backend_enabled_pdf_generation') ? 'selected' : ''; ?>>Enable PDF generating functionality</option>
                    <option value="backend_disabled_pdf_generation" <?php echo (get_option('is_pdf_generating_backend') === 'backend_disabled_pdf_generation') ? 'selected' : ''; ?>>Disable PDF generating functionality</option>
                </select>
            </div>


            <div class="col">
                <label for="pdf-generating-functionality-myaccount-order-detail">My Account Order detail page:</label>
                <select id="pdf-generating-functionality-myaccount-order-detail" name="pdf-generating-functionality-myaccount-order-detail">
                    <option value="myaccount_enabled_pdf_generation" <?php echo (get_option('is_pdf_generating_myaccount') === 'myaccount_enabled_pdf_generation') ? 'selected' : ''; ?>>Enable PDF generating functionality</option>
                    <option value="myaccount_disabled_pdf_generation" <?php echo (get_option('is_pdf_generating_myaccount') === 'myaccount_disabled_pdf_generation') ? 'selected' : ''; ?>>Disable PDF generating functionality</option>
                </select>
            </div>

            <div class="col">
                <label for="woo_invoice_billing-address">Billing Address:</label>
                <select id="woo_invoice_billing-address" name="woo_invoice_billing-address">
                    <option value="enabled_billing_address" <?php echo (get_option('woo_invoice_billing') === 'enabled_billing_address') ? 'selected' : ''; ?>>Enable billing Address</option>
                    <option value="disabled_billing_address" <?php echo (get_option('woo_invoice_billing') === 'disabled_billing_address') ? 'selected' : ''; ?>>Disable billing Address</option>
                </select>
            </div>

            <div class="col">
                <label for="woo_invoice_email_address">Email Address:</label>
                <select id="woo_invoice_email_address" name="woo_invoice_email_address">
                    <option value="enabled_email_address" <?php echo (get_option('woo_invoice_email_address') === 'enabled_email_address') ? 'selected' : ''; ?>>Enable Email Address</option>
                    <option value="disabled_email_address" <?php echo (get_option('woo_invoice_email_address') === 'disabled_email_address') ? 'selected' : ''; ?>>Disable Email Address</option>
                </select>
            </div>

            <div class="col">
                <label for="woo_invoice_phone_number">Phone number:</label>
                <select id="woo_invoice_phone_number" name="woo_invoice_phone_number">
                    <option value="enabled_phone_number" <?php echo (get_option('woo_invoice_phone_number') === 'enabled_phone_number') ? 'selected' : ''; ?>>Enable Phone number</option>
                    <option value="disabled_phone_number" <?php echo (get_option('woo_invoice_phone_number') === 'disabled_phone_number') ? 'selected' : ''; ?>>Disable Phone number</option>
                </select>
            </div>

            <div class="col">
                <label for="woo_invoice_shipping-address">Shipping Address:</label>
                <select id="woo_invoice_shipping-address" name="woo_invoice_shipping-address">
                    <option value="enabled_shipping_address" <?php echo (get_option('woo_invoice_shipping') === 'enabled_shipping_address') ? 'selected' : ''; ?>>Enable Shipping Address</option>
                    <option value="disabled_shipping_address" <?php echo (get_option('woo_invoice_shipping') === 'disabled_shipping_address') ? 'selected' : ''; ?>>Disable Shipping Address</option>
                </select>
            </div>

            <div class="col">
                <label for="woo_invoice_display_paymethod">Payment Method:</label>
                <select id="woo_invoice_display_paymethod" name="woo_invoice_display_paymethod">
                    <option value="enabled_paymethod" <?php echo (get_option('display_paymethod') === 'enabled_paymethod') ? 'selected' : ''; ?>>Enable Payment Method</option>
                    <option value="disabled_paymethod" <?php echo (get_option('display_paymethod') === 'disabled_paymethod') ? 'selected' : ''; ?>>Disable Payment Method</option>
                </select>
            </div>

            <div class="col">
                <label for="woo_invoice-order-date">Display Order Date:</label>
                <select id="woo_invoice-order-date" name="woo_invoice_display_order_date">
                    <option value="enabled_order_date" <?php echo (get_option('display_orderdate') === 'enabled_order_date') ? 'selected' : ''; ?>>Enable Order Date</option>
                    <option value="disabled_order_date" <?php echo (get_option('display_orderdate') === 'disabled_order_date') ? 'selected' : ''; ?>>Disable Order Date</option>
                </select>
            </div>

            <div class="col">
                <label for="woo_invoice-coupon-code">Display Coupon Code:</label>
                <select id="woo_invoice-coupon-code" name="woo_invoice_display_coupon_code">
                    <option value="enabled_coupon_code" <?php echo (get_option('display_coupon_code') === 'enabled_coupon_code') ? 'selected' : ''; ?>>Enable Coupon Code</option>
                    <option value="disabled_coupon_code" <?php echo (get_option('display_coupon_code') === 'disabled_coupon_code') ? 'selected' : ''; ?>>Disable Coupon Code</option>
                </select>
            </div>

            <div class="col">
                <label for="woo_invoice-discount-amount">Display Discount Amount:</label>
                <select id="woo_invoice-discount-amount" name="woo_invoice_display_discount_amount">
                    <option value="enabled_discount_amount" <?php echo (get_option('display_discount_amount') === 'enabled_discount_amount') ? 'selected' : ''; ?>>Enable Discount Amount</option>
                    <option value="disabled_discount_amount" <?php echo (get_option('display_discount_amount') === 'disabled_discount_amount') ? 'selected' : ''; ?>>Disable Discount Amount</option>
                </select>
            </div>

            <div class="col">
                <label for="woo_invoice-customer_note">Display Order Note:</label>
                <select id="display_order_customer_note" name="display_order_customer_note">
                    <option value="enabled_order_customer_note" <?php echo (get_option('display_order_customer_note') === 'enabled_order_customer_note') ? 'selected' : ''; ?>>Enable Order Note</option>
                    <option value="disabled_order_customer_note" <?php echo (get_option('display_order_customer_note') === 'disabled_order_customer_note') ? 'selected' : ''; ?>>Disable Order Note</option>
                </select>
            </div>

            <div class="color-input-container col">
                <label for="color-input">Main-heading color:</label>
                <input type="color" id="woo_invoice_bg_color" name="woo_invoice_bg_color" value="<?php echo esc_attr(get_option('get_pdf_bg_color', '#e10505')); ?>">
            </div>

            <div class="color-input-container col">
                <label for="color-input">Sub-heading color:</label>
                <input type="color" id="woo_invoice_subheading_color" name="woo_invoice_subheading_color" value="<?php echo esc_attr( get_option('get_pdf_subheading_color', '#000000') ); ?>">
            </div>

            <div class="col">
                <label for="pdf_logo_upload">Upload PDF Logo:</label>
                <!-- <input type="file" id="pdf_logo_upload" name="pdf_logo_upload"> -->
                <input id="header_logo" name="wpo_wcpdf_settings_general[header_logo]" type="hidden" value="<?php echo esc_attr( get_option('pdf_logo_path', '') ); ?>" class="media-upload-id">
                <img src="<?php echo esc_url(wp_get_attachment_url(get_option('pdf_logo_path'))); ?>" style="<?php echo (get_option('pdf_logo_path')) ? '' : 'display:none'; ?>" id="img-header_logo" class="media-upload-preview">
                <span class="button wpo_upload_image_button header_logo" data-uploader_title="Select or upload your invoice header/logo" data-uploader_button_text="Set image" data-remove_button_text="Remove image" data-input_id="header_logo">Set image</span>
                <span class="button wpo_remove_image_button" data-input_id="header_logo" style="<?php echo (get_option('pdf_logo_path')) ? '' : 'display:none'; ?>">Remove image</span>


                <p class="description">Upload a logo image for your PDF invoices.</p>
            </div>

            <div class="col">
                <label for="woo_invoice-customer_note">PDF Attach To:</label>

                <div class="checkbox-wrapper">
                    <input type="checkbox" name="pdf_attach_to_order_status" value="new_order" <?php echo (is_array(get_option('pdf_attach_to_order_status')) && in_array('new_order', get_option('pdf_attach_to_order_status'))) ? 'checked' : ''; ?> >
                    <label for="pdf_attach_to_order_status">New order (Admin Email)</label>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" name="pdf_attach_to_order_status" value="cancelled_order" <?php echo (is_array(get_option('pdf_attach_to_order_status')) && in_array('cancelled_order', get_option('pdf_attach_to_order_status'))) ? 'checked' : ''; ?>>
                    <label for="pdf_attach_to_order_status">Cancelled order</label>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" name="pdf_attach_to_order_status" value="failed_order" <?php echo (is_array(get_option('pdf_attach_to_order_status')) && in_array('failed_order', get_option('pdf_attach_to_order_status'))) ? 'checked' : ''; ?>>
                    <label for="pdf_attach_to_order_status">Failed order</label>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" name="pdf_attach_to_order_status" value="customer_on_hold_order" <?php echo (is_array(get_option('pdf_attach_to_order_status')) && in_array('customer_on_hold_order', get_option('pdf_attach_to_order_status'))) ? 'checked' : ''; ?>>
                    <label for="pdf_attach_to_order_status">Order on-hold</label>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" name="pdf_attach_to_order_status" value="customer_processing_order" <?php echo (is_array(get_option('pdf_attach_to_order_status')) && in_array('customer_processing_order', get_option('pdf_attach_to_order_status'))) ? 'checked' : ''; ?>>
                    <label for="pdf_attach_to_order_status">Processing order</label>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" name="pdf_attach_to_order_status" value="customer_completed_order" <?php echo (is_array(get_option('pdf_attach_to_order_status')) && in_array('customer_completed_order', get_option('pdf_attach_to_order_status'))) ? 'checked' : ''; ?>>
                    <label for="pdf_attach_to_order_status">Completed order</label>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" name="pdf_attach_to_order_status" value="customer_refunded_order" <?php echo (is_array(get_option('pdf_attach_to_order_status')) && in_array('customer_refunded_order', get_option('pdf_attach_to_order_status'))) ? 'checked' : ''; ?>>
                    <label for="pdf_attach_to_order_status">Refunded order</label>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" name="pdf_attach_to_order_status" value="customer_invoice" <?php echo (is_array(get_option('pdf_attach_to_order_status')) && in_array('customer_invoice', get_option('pdf_attach_to_order_status'))) ? 'checked' : ''; ?>>
                    <label for="pdf_attach_to_order_status">Customer invoice / Order details (Manual email)</label>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" name="pdf_attach_to_order_status" value="customer_note" <?php echo (is_array(get_option('pdf_attach_to_order_status')) && in_array('customer_note', get_option('pdf_attach_to_order_status'))) ? 'checked' : ''; ?>>
                    <label for="pdf_attach_to_order_status">Customer note</label>
                </div>
            </div>


            <div class="text-center btn-sc"> 
                <?php submit_button(__('Save Settings', 'woo-invoice-plus'), 'primary', 'woo_invoiceplus_settings',false); ?>
                <?php submit_button( __( 'Reset Settings', 'woo-invoice-plus' ), 'secondary','woo_invoiceplus_reset_settings',false ); ?>
            </div>
        </form>

    </div>
		<?php
	}

    public function enqueue_option_page_styles($hook)
    {
        // Enqueue styles only on the woo_invoice_general_settings option page
        if ($hook === 'toplevel_page_woo_invoice_general_settings') {
            wp_enqueue_media();

            wp_enqueue_style('wooinvoiceplus-css', plugin_dir_url(__FILE__) . '/assets/css/wooinvoiceplus.css', array(), '1.0.0');

            wp_register_script('wooinvoiceplus-js', plugin_dir_url(__FILE__) . '/assets/js/wooinvoiceplus.js', array('jquery'), '1.0', false);

            // Enqueue FancyBox script
            wp_enqueue_script('fancybox-js', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js', array('jquery'), '3.5.7', true);

            // Enqueue FancyBox CSS
            wp_enqueue_style('fancybox-css', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css', array(), '3.5.7');

            // Localize the script with data
            $woo_invoice_obj = array(
                'wooinvoiceplus'    => admin_url('admin-ajax.php'),
                'nonce'             => wp_create_nonce('save_global_settings_nonce'),
                'action'            => $this->save_global_settings_wooinvoiceplus(),
                'default_pdf'       => plugin_dir_url(__FILE__) . 'assets/pdf/woo-invoice-preview.pdf',


            );
            wp_localize_script('wooinvoiceplus-js', 'wooinvoiceplus_ajax_object', $woo_invoice_obj);
    
            // Enqueue the script
            wp_enqueue_script('wooinvoiceplus-js');

        }

        wp_enqueue_script( 'invoice-generator-script', plugin_dir_url( __FILE__ ) . '/assets/js/invoice-generator.js', array( 'jquery' ), '1.0', true );
    wp_localize_script( 'invoice-generator-script', 'invoice_generator_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    }
    

    public function save_global_settings_wooinvoiceplus()
    {

        // Verify nonce
        // if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'save_global_settings_nonce' ) ) {
        //     wp_send_json_error( 'Invalid nonce' );
        // }

        // Handle checkbox values
        if (isset($_POST['pdf_attach_to_order_status']) && is_array($_POST['pdf_attach_to_order_status'])) {
            // Remove empty values (unchecked checkboxes) before updating the option
            $pdf_attach_to_order_status = array_filter($_POST['pdf_attach_to_order_status']);
            update_option('pdf_attach_to_order_status', $pdf_attach_to_order_status);
        }

        // Check if a file is uploaded
        if (isset($_POST['header_logo_attachment_id'])) {
            $attachment_id = absint($_POST['header_logo_attachment_id']);
            update_option('pdf_logo_path', $attachment_id);
        }

            // Update options based on the received parameters
        if (isset($_POST['is_pdf_generating'])) {
            update_option('is_pdf_generating', $_POST['is_pdf_generating']);
        }

        if (isset($_POST['is_pdf_backend_preview'])) {
            update_option('is_pdf_backend_preview', $_POST['is_pdf_backend_preview']);
        }

        if (isset($_POST['is_pdf_papersize'])) {
            // Set the default value for the option
            update_option('is_pdf_papersize', $_POST['is_pdf_papersize']);
        }

        if (isset($_POST['is_pdf_fontfamily'])) {
            // Set the default value for the option
            update_option('is_pdf_fontfamily', $_POST['is_pdf_fontfamily']);
        }

        if (isset($_POST['is_pdf_password_protected'])) {
            // Set the default value for the option
            update_option('is_pdf_password_protected', $_POST['is_pdf_password_protected']);
        }

        if (isset($_POST['is_pdf_orientation'])) {
            // Set the default value for the option
            update_option('is_pdf_orientation', $_POST['is_pdf_orientation']);
        }

        if (isset($_POST['is_pdf_generating_backend'])) {
            update_option('is_pdf_generating_backend', $_POST['is_pdf_generating_backend']);
        }

        if (isset($_POST['is_pdf_generating_myaccount'])) {
            update_option('is_pdf_generating_myaccount', $_POST['is_pdf_generating_myaccount']);
        }
     
        if (isset($_POST['get_pdf_bg_color'])) {
            update_option('get_pdf_bg_color', $_POST['get_pdf_bg_color']);
        }

        if (isset($_POST['get_pdf_subheading_color'])) {
            update_option('get_pdf_subheading_color', $_POST['get_pdf_subheading_color']);
        }

        if (isset($_POST['woo_invoice_billing'])) {
            update_option('woo_invoice_billing', $_POST['woo_invoice_billing']);
        }

        if (isset($_POST['woo_invoice_shipping'])) {
            update_option('woo_invoice_shipping', $_POST['woo_invoice_shipping']);
        }

        if (isset($_POST['display_paymethod'])) {
            update_option('display_paymethod', $_POST['display_paymethod']);
        }

        if (isset($_POST['display_orderdate'])) {
            update_option('display_orderdate', $_POST['display_orderdate']);
        }

        if (isset($_POST['display_emailaddress'])) {
            update_option('woo_invoice_email_address', $_POST['display_emailaddress']);
        }

        if (isset($_POST['display_phonenumber'])) {
            update_option('woo_invoice_phone_number', $_POST['display_phonenumber']);
        }

        if (isset($_POST['display_coupon_code'])) {
            update_option('display_coupon_code', $_POST['display_coupon_code']);
        }

        if (isset($_POST['display_discount_amount'])) {
            update_option('display_discount_amount', $_POST['display_discount_amount']);
        }

        if (isset($_POST['display_order_customer_note'])) {
            update_option('display_order_customer_note', $_POST['display_order_customer_note']);
        }

      

        // Load the Dompdf library
       

        // Get the order object

         // Check if the order is already set to "processing" or "completed" status
        // if ($order->has_status(array('processing', 'completed'))) {
        //     return;
        // }

        // // Update the order status to "processing"
        // $order->update_status('processing');

        // Generate the PDF content
        if (get_option('is_pdf_backend_preview') === 'backend_enabled_pdf_preview') {
            require 'vendor/autoload.php';
                // Generate the PDF content using the order data
                // You can customize the content based on your requirements
                // body {
                //     font-family: dejavu serif; /* Replace Arial with your desired font family */
                // }
                $content = '<html>
                <head>
                <meta charset="UTF-8">
                <style>
                    body {
                        font-family: ' . (get_option('is_pdf_fontfamily') ? get_option('is_pdf_fontfamily') : 'times-roman') . '; /* Replace Arial with your desired font family */
                    }
                    table {
                    width: 100%;
                    border-collapse: collapse;
                    }

                    #header,
                    #footer {
                    position: fixed;
                    left: 0;
                        right: 0;
                        color: #aaa;
                        font-size: 0.9em;
                    }

                    #header,
                    #footer {
                    position: fixed;
                    left: 0;
                        right: 0;
                        color: #aaa;
                        font-size: 0.9em;
                    }

                    #header {
                    top: 0;
                        border-bottom: 0.1pt solid #aaa;
                    }

                    #footer {
                    bottom: 0;
                    border-top: 0.1pt solid #aaa;
                    }

                    #header table,
                    #footer table {
                        width: 100%;
                        border-collapse: collapse;
                        border: none;
                    }

                    #header td,
                    #footer td {
                    padding: 0;
                        width: 50%;
                    }

                    .page-number {
                    text-align: center;
                    }

                    .page-number:before {
                    content: "Page " counter(page);
                    }

                    hr {
                    page-break-after: always;
                    border: 0;
                    }

                </style>
                </head>
                        <body>';

                // Get the promo code and discount amount
                $get_pdf_heading_color      = get_option('get_pdf_bg_color') ? get_option('get_pdf_bg_color') : '#000000';
                $get_pdf_subheading_color   = get_option('get_pdf_subheading_color') ? get_option('get_pdf_subheading_color') : '#000000';
                $get_pdf_logo               = get_option('pdf_logo_path') ? esc_url(wp_get_attachment_url(get_option('pdf_logo_path'))) : '';

                $content .= '<div id="header">
                                <table>
                                    <tr><
                                    td></td>
                                    <td style="text-align: right;">';

                if (!empty($get_pdf_logo)) {
                    $response = wp_remote_get($get_pdf_logo);
                
                    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                        $imgtest = wp_remote_retrieve_body($response);
                        $img = base64_encode($imgtest);
                
                        if (!empty($img)) {
                            $content .= '<a href="' . esc_url(get_site_url()) . '"><img src="data:image;base64,' . $img . '"></a>';
                        }
                    }
                }
                                    
                $content .= '</td></tr></table>
                                </div>
                                
                                <div id="footer">
                                    <div class="page-number"></div>
                                </div>';
                $content .= '<h2>
                            <u style="color:' . $get_pdf_heading_color .'">Order Details of <span style="background: yellow;">#' . 1234567890 . '</span></u>
                            </h2>';
                $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;color:' . $get_pdf_subheading_color .'"">Order Number</th>
                                <td style="padding: 8px;border: 1px solid black;"><span style="background: yellow;">#' . 1234567890 . '</span></td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Order Total</th>
                                <td style="padding: 8px;border: 1px solid black;">' . ' $33.00 ' . '</td>
                            </tr>';

                                

                                if(get_option('display_coupon_code') === 'enabled_coupon_code') :

                                    $content .= '<tr>
                                                    <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Promo Code</th>
                                                    <td style="padding: 8px;border: 1px solid black;">' . 'BOGO777' . '</td>
                                                </tr>';

                                endif;

                                if(get_option('display_discount_amount') === 'enabled_discount_amount') :

                                    $content .= '<tr>
                                                    <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Discount Amount</th>
                                                    <td style="padding: 8px;border: 1px solid black;">' . wc_price(50) . '</td>
                                                </tr>';

                                endif;

                $content .= '</table>';

                // Get customer information

                // Get customer name and email
               

                $content .= '<h3><u>Customer Information</u></h3>';
                $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;">Customer Name</th>
                                <td style="padding: 8px;border: 1px solid black;">' . 'Loren Epsum' . '</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;">Customer Email</th>
                                <td style="padding: 8px;border: 1px solid black;">' . 'lorenepsum777@gmail.com' . '</td>
                            </tr>
                        </table>';


                $content .= '<h3><u style="color:' . $get_pdf_heading_color .'">Order Items</u></h3>';

                // Add order items
                $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Product</th>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Quantity</th>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Total</th>
                            </tr>';

          
                    $content .= '<tr>
                                <td style="padding: 8px;border: 1px solid black;">' . 'Album' . '</td>
                                <td style="padding: 8px;border: 1px solid black;">' . '1' . '</td>
                                <td style="padding: 8px;border: 1px solid black;">' . wc_price(15) . '</td>
                            </tr><tr>
                            <td style="padding: 8px;border: 1px solid black;">' . 'Beanie' . '</td>
                            <td style="padding: 8px;border: 1px solid black;">' . '10' . '</td>
                            <td style="padding: 8px;border: 1px solid black;">' . wc_price(120) . '</td>
                        </tr>';

                $content .= '</table>';

                // Get payment method

                if(get_option('display_paymethod') === 'enabled_paymethod') :

                    $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                <tr>
                                    <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Payment Method</th>
                                    <td style="padding: 8px;border: 1px solid black;">' . 'Cash on delivery' . '</td>
                                </tr>
                            </table>';
                
                endif;

                
                if(get_option('display_orderdate') === 'enabled_order_date') :

                    // Get order created date
                    $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                <tr>
                                    <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Order Created Date</th>
                                    <td style="padding: 8px;border: 1px solid black;">' . 'March 21, 2024' . '</td>
                                </tr>
                            </table>';

                endif;

                if(get_option('display_order_customer_note') === 'enabled_order_customer_note') :

                    $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                    <tr>
                                        <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Order Note</th>
                                        <td style="padding: 8px;border: 1px solid black;">' . 'Can you help translate this site into a foreign language ? Please email us with details if you can help.' . '</td>
                                    </tr>
                                </table>';


                endif;

                if(get_option('woo_invoice_shipping') === 'enabled_shipping_address') :

                    // Get shipping details
                        $content .= '<h3><u style="color:' . $get_pdf_heading_color .'">Shipping Details</u></h3>';
                        $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                    <tr>
                                        <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Shipping Address</th>
                                        <td style="padding: 8px;border: 1px solid black;">' . 
                                        'Verda Gleason
                                        Klein, Krajcik and Harris
                                        1499 Towne Vista
                                        626 Swift Route
                                        Chula Vista, CA 33980' 
                                        . '</td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Shipping Method</th>
                                        <td style="padding: 8px;border: 1px solid black;">' . 'Free shipping' . '</td>
                                    </tr>
                                </table>';

                endif;

                if(get_option('woo_invoice_billing') === 'enabled_billing_address') :

                    // Get billing details
                        $content .= '<h3><u style="color:' . $get_pdf_heading_color .'">Billing Details</u></h3>';
                        $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                        <tr>
                                            <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Billing Address</th>
                                            <td style="padding: 8px;border: 1px solid black;">' . 
                                            'Verda Gleason
                                            Klein, Krajcik and Harris
                                            1499 Towne Vista
                                            626 Swift Route
                                            Chula Vista, CA 33980'
                                             . '</td>
                                        </tr>;';  
                                        if(get_option('woo_invoice_phone_number') === 'enabled_phone_number') :
                                        
                                            $content .= '<tr>
                                                            <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Phone number</th>
                                                            <td style="padding: 8px;border: 1px solid black;"><a href="tel:'.'157-841-7322'.'">' . '157-841-7322' . '</a></td>
                                                        </tr>;';
                                        endif;

                                        if(get_option('woo_invoice_email_address') === 'enabled_email_address') :
                                        
                                            $content .= '<tr>
                                                            <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Email Address</th>
                                                            <td style="padding: 8px;border: 1px solid black;">' . 'lorenepsum777@gmail.com' . '</td>
                                                        </tr>;';
                                        endif;


                        $content .= '</table>';

                endif;

                $content .= '</body>
                        </html>';

            // Get the WordPress filesystem
            global $wp_filesystem;

            // Initialize the WordPress filesystem if it's not already loaded
            if ( ! isset( $wp_filesystem ) ) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
            }

            if ( is_wp_error( $wp_filesystem ) ) {
                $error_message = $wp_filesystem->get_error_message();
                // Failed to initialize the WordPress filesystem, handle error here if needed
                echo 'Error: ' . esc_html( $error_message );
            } else {

                // Create a new Dompdf instance
                $dompdf = new Dompdf\Dompdf();

                // Load the PDF content
                $dompdf->loadHtml($content);

                // Set paper size and orientation
                $dompdf->setPaper(
                                    ( get_option('is_pdf_papersize') === 'a4') ? 'a4' : get_option('is_pdf_papersize'),
                                    ( get_option('is_pdf_orientation') === 'portrait') ? 'portrait' : get_option('is_pdf_orientation')
                                );


                // Render the PDF
                $dompdf->render();
                // $dompdf->stream("",array("Attachment"=> false));
                if(get_option('is_pdf_password_protected') === 'password_protected') : 
                // Password protection
                    $cpdf = $dompdf->getCanvas()->get_cpdf();

                    // Set encryption with strong passwords:
                    // - User password (optional, for opening restrictions) - Use a complex password with at least 12 characters, including uppercase, lowercase, numbers, and symbols.
                    $userPassword = 'DEMO123';
                    // - Owner password (mandatory for full access) - Use a different, equally strong password.
                    $ownerPassword = 'DEMO123';
                    $allowedActions = ['print', 'copy']; // Adjust based on your requirements

                    $cpdf->setEncryption($userPassword, $ownerPassword, $allowedActions);

                endif;


                // Get the PDF output
                $pdf_output = $dompdf->output();


                $pdf_file_path = plugin_dir_path(__FILE__) . 'assets/pdf/woo-invoice-preview.pdf';

                // Write the PDF content to the file
                $write_result = $wp_filesystem->put_contents( $pdf_file_path, $pdf_output );

                // Check if the file write operation was successful
                if ( $write_result === false ) {
                    // Failed to write the PDF content to the file, handle error here if needed
                    echo 'Error: Failed to write PDF content to the file.';
                } else {
                    // File write successful, proceed with further actions if needed
                }
            }
        }

    }

    public function add_invoice_metabox()
    {
        add_meta_box(
            'wooinvoiceplus_invoice_metabox',
            __('Invoice', 'woo-invoice-plus'),
            array($this, 'render_invoice_metabox'),
            'shop_order',
            'side',
            'high'
        );
    }

    public function render_invoice_metabox($post)
    {
        $order_id = $post->ID;
        $order = wc_get_order($order_id);
        $invoice_exists = $this->check_invoice_exists($order_id);

        if ($invoice_exists) {
            $download_url = $this->get_invoice_download_url($order_id);
            $is_pdf_password_protected = get_post_meta($order_id, '_pdf_password_protected', true);

            if ($is_pdf_password_protected) {
                $customer_first_name = $order->get_billing_first_name();

                // Get the first 4 letters of the customer's first name
                $customer_first_name_4_letters = strtoupper(substr($customer_first_name, 0, 4));

                // Get the order ID
                $woo_order_id = $order->get_id();

                // Set the PDF password as the customer's first 4 letters of their name followed by the order ID
                $pdf_password = $customer_first_name_4_letters . $woo_order_id;

                echo '<h5 class="email-upsell-title">Your Password to view Invoice PDF is : "' . esc_html($pdf_password) . '"</h5>';
            }
            
            echo '<a href="' . esc_url($download_url) . '" class="button indirect_function_call" download>' . esc_html__('Download Invoice', 'woo-invoice-plus') . '</a>';
        } else {
           echo '<button class="button generate-invoice-btn" data-order-id="' . $order_id . '">' . esc_html__('Generate Invoice', 'woo-invoice-plus') . '</button>';
            echo "<br><b>If PDF not generated correctly then please try again to generate!!</b>";

        }
    }

    public function handle_invoice_download()
    {
        if (isset($_GET['wooinvoiceplus_download_invoice'])) {
            $order_id = absint($_GET['wooinvoiceplus_download_invoice']);
            $order = wc_get_order($order_id);
            $invoice_exists = $this->check_invoice_exists($order_id);

            if ($invoice_exists) {
                $download_url = $this->get_invoice_download_url($order_id);
                wp_redirect($download_url);
                exit;
            } else {
                wp_die(esc_html__('Invoice not found.', 'woo-invoice-plus'));
            }
        }
    }

    private function check_invoice_exists($order_id)
    {
        $upload_dir = wp_upload_dir();
        $pdf_file_path = $upload_dir['basedir'] . '/Woo Invoice PDF/' . $order_id . '.pdf';

        return file_exists($pdf_file_path);
    }

    private function get_invoice_download_url($order_id)
    {
        $upload_dir = wp_upload_dir();
        $pdf_relative_path = '/Woo Invoice PDF/' . $order_id . '.pdf';
        return $upload_dir['baseurl'] . $pdf_relative_path;
    }

    private function get_invoice_generate_url($order_id)
    {
        return add_query_arg('wooinvoiceplus_generate_invoice', $order_id, get_admin_url(null, 'post.php'));
    }


    public function generate_pdf_on_order_placement($order_id)
    {
        // Load the Dompdf library
        require 'vendor/autoload.php';

        // Get the order object
        $order = wc_get_order($order_id);

         // Check if the order is already set to "processing" or "completed" status
        // if ($order->has_status(array('processing', 'completed'))) {
        //     return;
        // }

        // // Update the order status to "processing"
        // $order->update_status('processing');

        // Generate the PDF content
        $pdf_content = $this->generate_pdf_content($order);

        // Create a new Dompdf instance
        $dompdf = new Dompdf\Dompdf();

        // Load the PDF content
        $dompdf->loadHtml($pdf_content);

        // Set paper size and orientation
        $dompdf->setPaper(
                            ( get_option('is_pdf_papersize') === 'a4') ? 'a4' : get_option('is_pdf_papersize'),
                            ( get_option('is_pdf_orientation') === 'portrait') ? 'portrait' : get_option('is_pdf_orientation')
                        );

        // Render the PDF
        $dompdf->render();
        // $dompdf->stream("",array("Attachment"=> false));

        
        if(get_option('is_pdf_password_protected') === 'password_protected') {
                $customer_first_name = $order->get_billing_first_name();
        
                // Get the first 4 letters of the customer's first name
                $customer_first_name_4_letters = strtoupper( substr( $customer_first_name, 0, 4 ) );
        
                // Get the order ID
                $order_id = $order->get_id();
        
                // Set the PDF password as the customer's first 4 letters of their name followed by the order ID
                $pdf_password = $customer_first_name_4_letters . $order_id;

                // Password protection
                $cpdf = $dompdf->getCanvas()->get_cpdf();

                // Set encryption with strong passwords:
                // - User password (optional, for opening restrictions) - Use a complex password with at least 12 characters, including uppercase, lowercase, numbers, and symbols.
                $userPassword = $pdf_password;
                // - Owner password (mandatory for full access) - Use a different, equally strong password.
                $ownerPassword = $pdf_password;
                $allowedActions = ['print', 'copy']; // Adjust based on your requirements

                $cpdf->setEncryption($userPassword, $ownerPassword, $allowedActions);
                update_post_meta($order_id, '_pdf_password_protected', true);

        } else {
            update_post_meta($order_id, '_pdf_password_protected', false);
        }

        // Get the PDF output
        $pdf_output = $dompdf->output();

        $upload_dir = wp_upload_dir();
        $pdf_folder_path = $upload_dir['basedir'] . '/Woo Invoice PDF/';

        // Initialize WP_Filesystem
        global $wp_filesystem;
        // Initialize the WordPress filesystem if it's not already loaded
        if ( ! isset( $wp_filesystem ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // Create the Woo Invoice PDF folder if it doesn't exist
        if ( ! $wp_filesystem->is_dir($pdf_folder_path) ) {
            $wp_filesystem->mkdir($pdf_folder_path);
        }

        $pdf_file_path = $pdf_folder_path . $order_id . '.pdf';

        // Write the PDF content to the file
        $wp_filesystem->put_contents($pdf_file_path, $pdf_output, FS_CHMOD_FILE);

        $upload_base_url = $upload_dir['baseurl'];
        $pdf_relative_path = str_replace($upload_dir['basedir'], '', $pdf_file_path);
        $download_url = $upload_base_url . $pdf_relative_path;

        // Output the download button
        if(!is_admin()){
            echo '<a href="' . esc_url($download_url) . '" class="button direct_function_call" download>Download Order PDF</a>';
        }
        
        update_post_meta($order_id, '_temp_pdf_path', $download_url);
    }

    public function add_custom_text_after_order_table( $order ){
        // Check if the order is of a specific status, such as 'completed'
        if ( $order->has_status( array('completed','processing','on-hold') ) ) {
            // Output your custom text
            $order_id = $order->ID;
            $order = wc_get_order($order_id);
            $invoice_exists = $this->check_invoice_exists($order_id);
            
            if ($invoice_exists) {
                $download_url = $this->get_invoice_download_url($order_id);
                $is_pdf_password_protected = get_post_meta($order_id, '_pdf_password_protected', true);

                if ($is_pdf_password_protected) {
                    $customer_first_name = $order->get_billing_first_name();
    
                    // Get the first 4 letters of the customer's first name
                    $customer_first_name_4_letters = strtoupper(substr($customer_first_name, 0, 4));
    
                    // Get the order ID
                    $woo_order_id = $order->get_id();
    
                    // Set the PDF password as the customer's first 4 letters of their name followed by the order ID
                    $pdf_password = $customer_first_name_4_letters . $woo_order_id;
    
                    echo '<h5 class="email-upsell-title">Your Password to view Invoice PDF is : "' . esc_html($pdf_password) . '"</h5>';
                }

                echo '<a href="' . esc_url($download_url) . '" class="button" download>' . esc_html__('Download Invoice', 'woo-invoice-plus') . '</a>';
            }
            
        } else {
            // Output default custom text for other order statuses
            echo '<p>This is my custom text for other order statuses.</p>';
        }
    }

    public function generate_pdf_content($order)
    {
        // Generate the PDF content using the order data
        // You can customize the content based on your requirements
        if (!empty($order)) {
        
            // Generate the PDF content using the order data
            // You can customize the content based on your requirements

            $content = '<html>
            <head>
            <meta charset="UTF-8">

            <style>
            body {
                font-family: ' . (get_option('is_pdf_fontfamily') ? get_option('is_pdf_fontfamily') : 'times-roman') . '; /* Replace Arial with your desired font family */
            }
            table {
            width: 100%;
            border-collapse: collapse;
            }

            #header,
            #footer {
            position: fixed;
            left: 0;
                right: 0;
                color: #aaa;
                font-size: 0.9em;
            }

            #header,
            #footer {
            position: fixed;
            left: 0;
                right: 0;
                color: #aaa;
                font-size: 0.9em;
            }

            #header {
            top: 0;
                border-bottom: 0.1pt solid #aaa;
            }

            #footer {
            bottom: 0;
            border-top: 0.1pt solid #aaa;
            }

            #header table,
            #footer table {
                width: 100%;
                border-collapse: collapse;
                border: none;
            }

            #header td,
            #footer td {
            padding: 0;
                width: 50%;
            }

            .page-number {
            text-align: center;
            }

            .page-number:before {
            content: "Page " counter(page);
            }

            hr {
            page-break-after: always;
            border: 0;
            }


           
                    </style></head>
                    <body>';

            // Get the promo code and discount amount
            $promo_code                 = $order->get_coupon_codes(); // Get the applied coupon codes
            $discount_amount            = $order->get_discount_total(); // Get the discount amount
            $get_pdf_heading_color      = get_option('get_pdf_bg_color') ? get_option('get_pdf_bg_color') : '#000000';
            $get_pdf_subheading_color   = get_option('get_pdf_subheading_color') ? get_option('get_pdf_subheading_color') : '#000000';
            $get_pdf_logo               = get_option('pdf_logo_path') ? esc_url(wp_get_attachment_url(get_option('pdf_logo_path'))) : '';

            $content .= '<div id="header">
                            <table>
                                <tr><
                                td></td>
                                <td style="text-align: right;">';

            if (!empty($get_pdf_logo)) {
                $response = wp_remote_get($get_pdf_logo);
            
                if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                    $imgtest = wp_remote_retrieve_body($response);
                    $img = base64_encode($imgtest);
            
                    if (!empty($img)) {
                        $content .= '<a href="' . esc_url(get_site_url()) . '"><img src="data:image;base64,' . $img . '"></a>';
                    }
                }
            }
                                
            
            $content .= '</td></tr></table>
                            </div>
                            
                            <div id="footer">
                                <div class="page-number"></div>
                            </div>';
            $content .= '<h2>
                        <u style="color:' . $get_pdf_heading_color .'">Order Details of <span style="background: yellow;">#' . $order->get_order_number() . '</span></u>
                        </h2>';
            $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                        <tr>
                            <th style="text-align: left; padding: 8px;border: 1px solid black;color:' . $get_pdf_subheading_color .'"">Order Number</th>
                            <td style="padding: 8px;border: 1px solid black;"><span style="background: yellow;">#' . $order->get_order_number() . '</span></td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Order Total</th>
                            <td style="padding: 8px;border: 1px solid black;">' . $order->get_formatted_order_total() . '</td>
                        </tr>';

                        if (!empty($promo_code) || !empty($discount_amount)) :
                            
                            $promo_code_formatted = array_map('strtoupper', $promo_code); // Convert promo code to uppercase

                            if(get_option('display_coupon_code') === 'enabled_coupon_code') :

                                $content .= '<tr>
                                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Promo Code</th>
                                                <td style="padding: 8px;border: 1px solid black;">' . implode(', ', $promo_code_formatted) . '</td>
                                            </tr>';

                            endif;

                            if(get_option('display_discount_amount') === 'enabled_discount_amount') :

                                $content .= '<tr>
                                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Discount Amount</th>
                                                <td style="padding: 8px;border: 1px solid black;">' . wc_price($discount_amount) . '</td>
                                            </tr>';

                            endif;

                        endif;
            $content .= '</table>';

            // Get customer information
            $customer_id = $order->get_customer_id(); // Get customer ID
            $customer = $order->get_user(); // Get customer object

            // Get customer name and email
            $customer_name = $customer ? $customer->display_name : ''; // Get customer display name
            $customer_email = $customer ? $customer->user_email : ''; // Get customer email

            if(!empty($customer_name) || !empty($customer_email)) :

                $content .= '<h3><u>Customer Information</u></h3>';
                $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;">Customer Name</th>
                                <td style="padding: 8px;border: 1px solid black;">' . $customer_name . '</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;">Customer Email</th>
                                <td style="padding: 8px;border: 1px solid black;">' . $customer_email . '</td>
                            </tr>
                        </table>';

            endif;

            $content .= '<h3><u style="color:' . $get_pdf_heading_color .'">Order Items</u></h3>';

            // Add order items
            $order_items = $order->get_items();

            $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                        <tr>
                            <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Product</th>
                            <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Quantity</th>
                            <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Total</th>
                        </tr>';

            foreach ($order_items as $item_id => $item) {
                $product_name = $item->get_name(); // Get product name
                $product_quantity = $item->get_quantity(); // Get product quantity
                $product_total = $item->get_total(); // Get product total

                $content .= '<tr>
                            <td style="padding: 8px;border: 1px solid black;">' . $product_name . '</td>
                            <td style="padding: 8px;border: 1px solid black;">' . $product_quantity . '</td>
                            <td style="padding: 8px;border: 1px solid black;">' . wc_price($product_total) . '</td>
                        </tr>';
            }

            $content .= '</table>';

            // Get payment method

            if(get_option('display_paymethod') === 'enabled_paymethod') :

                $payment_method = $order->get_payment_method_title();

                $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Payment Method</th>
                                <td style="padding: 8px;border: 1px solid black;">' . $payment_method . '</td>
                            </tr>
                        </table>';
            
            endif;

            
            if(get_option('display_orderdate') === 'enabled_order_date') :

                // Get order created date
                $created_date = $order->get_date_created();
                $formatted_date = $created_date->date_i18n(get_option('date_format'));

                $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                            <tr>
                                <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Order Created Date</th>
                                <td style="padding: 8px;border: 1px solid black;">' . $formatted_date . '</td>
                            </tr>
                        </table>';

            endif;

            if(get_option('display_order_customer_note') === 'enabled_order_customer_note') :

                $order_notes = $order->get_customer_note();

                if(!empty($order_notes)) :

                $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                <tr>
                                    <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Order Note</th>
                                    <td style="padding: 8px;border: 1px solid black;">' . $order_notes . '</td>
                                </tr>
                            </table>';

                endif;

            endif;

            if(get_option('woo_invoice_shipping') === 'enabled_shipping_address') :

                // Get shipping details
                $shipping_address = $order->get_formatted_shipping_address(); // Get formatted shipping address
                $shipping_method = $order->get_shipping_method(); // Get shipping method

                if(!empty($shipping_address) || !empty($shipping_method)) :

                    $content .= '<h3><u style="color:' . $get_pdf_heading_color .'">Shipping Details</u></h3>';
                    $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                <tr>
                                    <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Shipping Address</th>
                                    <td style="padding: 8px;border: 1px solid black;">' . $shipping_address . '</td>
                                </tr>
                                <tr>
                                    <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Shipping Method</th>
                                    <td style="padding: 8px;border: 1px solid black;">' . $shipping_method . '</td>
                                </tr>
                            </table>';

                endif;

            endif;

            if(get_option('woo_invoice_billing') === 'enabled_billing_address') :

                // Get billing details
                $billing_address = $order->get_formatted_billing_address(); // Get formatted billing address
                $customer_phone = $order->get_billing_phone(); // Get customer phone number
                $billing_email  = $order->get_billing_email();


                if(!empty($billing_address) ) :

                    $content .= '<h3><u style="color:' . $get_pdf_heading_color .'">Billing Details</u></h3>';
                    $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                    <tr>
                                        <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Billing Address</th>
                                        <td style="padding: 8px;border: 1px solid black;">' . $billing_address . '</td>
                                    </tr>;';  
                                    if(get_option('woo_invoice_phone_number') === 'enabled_phone_number') :
                                    
                                        $content .= '<tr>
                                                        <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Phone number</th>
                                                        <td style="padding: 8px;border: 1px solid black;"><a href="tel:'.$customer_phone.'">' . $customer_phone . '</a></td>
                                                    </tr>;';
                                    endif;

                                    if(get_option('woo_invoice_email_address') === 'enabled_email_address') :
                                    
                                        $content .= '<tr>
                                                        <th style="text-align: left; padding: 8px;border: 1px solid black;;color:' . $get_pdf_subheading_color .'"">Email Address</th>
                                                        <td style="padding: 8px;border: 1px solid black;">' . $billing_email . '</td>
                                                    </tr>;';
                                    endif;


                    $content .= '</table>';

                endif;

            endif;

            $content .= '</body>
                    </html>';

        }
        return $content;
    }

    // AJAX handler to trigger PDF generation
    public function generate_invoice_callback() {
        $order_id = $_POST['order_id'];
        // Call your PDF generation function here
        $generate_url = $this->generate_pdf_on_order_placement($order_id);
        wp_send_json_success( $generate_url );
    }

}

if (class_exists( 'WooInvoicePlus' )) {
        $wooinvoiceplus = new WooInvoicePlus();
}

// register_activation_hook( __FILE__, array( $wooinvoiceplus, 'wooinvoiceplus_activate' ) );

// register_deactivation_hook( __FILE__, array( $wooinvoiceplus, 'wooinvoiceplus_deactivate' ) );