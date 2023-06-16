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
        if(get_option('is_pdf_generating') === 'enabled_pdf_generation') :

            add_action('woocommerce_thankyou', array($this, 'generate_pdf_on_order_placement'), 10, 1);
            add_action('add_meta_boxes', array($this, 'add_invoice_metabox'));

        endif;
        add_action('init', array($this, 'handle_invoice_download'));
        add_action( 'admin_menu', array( $this, 'register_wooinvoiceplus_menu' ) );

        // Enqueue styles for the option page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_option_page_styles'));

        add_action( 'wp_ajax_save_global_settings_wooinvoiceplus', array( $this, 'save_global_settings_wooinvoiceplus' ) );
        add_action( 'wp_ajax_nopriv_save_global_settings_wooinvoiceplus', array( $this, 'save_global_settings_wooinvoiceplus' ) );
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));

        register_activation_hook(__FILE__, array($this, 'woo_invoice_plugin_activation'));



    }

     // Method to handle plugin activation
     public function woo_invoice_plugin_activation()
     {
         // Check if the option is not already set
         if (!get_option('is_pdf_generating')) {
             // Set the default value for the option
             update_option('is_pdf_generating', 'enabled_pdf_generation');
         }
      
        if (!get_option('get_pdf_bg_color')) {
            update_option('get_pdf_bg_color', '#e10505');
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

     }

    public function add_settings_link($links)
    {
        // Add the settings link to the plugin actions
        $settings_link = '<a href="' . admin_url('?page=woo_invoice_general_settings') . '">Settings</a>';
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
		
    <div class="form-container">
        <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
        <form action="#" class="form-row" method="POST">
            <div class="col">
                <label for="pdf-generating-functionality">PDF Generating Functionality:</label>
                <select id="pdf-generating-functionality" name="pdf-generating-functionality">
                    <option value="enabled_pdf_generation" <?php echo (get_option('is_pdf_generating') === 'enabled_pdf_generation') ? 'selected' : ''; ?>>Enable PDF generating functionality</option>
                    <option value="disabled_pdf_generation" <?php echo (get_option('is_pdf_generating') === 'disabled_pdf_generation') ? 'selected' : ''; ?>>Disable PDF generating functionality</option>
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

            <div class="color-input-container">
                <label for="color-input">Background Color:</label>
                <input type="color" id="woo_invoice_bg_color" name="woo_invoice_bg_color" value="<?php echo get_option('get_pdf_bg_color') ? get_option('get_pdf_bg_color') : '#e10505'; ?>">
            </div>

            <div class="text-center btn-sc"> <?php submit_button(__('Save Settings', 'woo-invoice-plus'), 'primary', 'woo_invoiceplus_settings',false); ?></div>
        </form>

    </div>



		<?php
	}

    public function enqueue_option_page_styles($hook)
    {
        // Enqueue styles only on the woo_invoice_general_settings option page
        if ($hook === 'toplevel_page_woo_invoice_general_settings') {
            wp_enqueue_style('wooinvoiceplus-css', plugin_dir_url(__FILE__) . '/assets/css/wooinvoiceplus.css', array(), '1.0.0');

            wp_register_script('wooinvoiceplus-js', plugin_dir_url(__FILE__) . '/assets/js/wooinvoiceplus.js', array('jquery'), '1.0', false);

            // Localize the script with data
            $woo_invoice_obj = array(
                'wooinvoiceplus'    => admin_url('admin-ajax.php'),
                'action'            => $this->save_global_settings_wooinvoiceplus(),

            );
            wp_localize_script('wooinvoiceplus-js', 'wooinvoiceplus_ajax_object', $woo_invoice_obj);
    
            // Enqueue the script
            wp_enqueue_script('wooinvoiceplus-js');

        }
    }

    public function save_global_settings_wooinvoiceplus()
    {

            // Update options based on the received parameters
        if (isset($_POST['is_pdf_generating'])) {
            update_option('is_pdf_generating', $_POST['is_pdf_generating']);
        }

        if (isset($_POST['get_pdf_bg_color'])) {
            update_option('get_pdf_bg_color', $_POST['get_pdf_bg_color']);
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

        // if ($invoice_exists) {
        //     $download_url = $this->get_invoice_download_url($order_id);
        //     echo '<a href="' . esc_url($download_url) . '" class="button" download>' . __('Download Invoice', 'woo-invoice-plus') . '</a>';
        // } else {
            $generate_url = $this->generate_pdf_on_order_placement($order_id);
            echo '<a href="' . esc_url($generate_url) . '" class="button">' . __('Generate Invoice', 'woo-invoice-plus') . '</a>';
            echo "<br><b>If PDF not generated correctly then please try again to generate!!</b>";

        // }
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
                wp_die(__('Invoice not found.', 'woo-invoice-plus'));
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

        // Generate the PDF content
        $pdf_content = $this->generate_pdf_content($order);

        // Create a new Dompdf instance
        $dompdf = new Dompdf\Dompdf();

        // Load the PDF content
        $dompdf->loadHtml($pdf_content);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Get the PDF output
        $pdf_output = $dompdf->output();

        $upload_dir = wp_upload_dir();
        $pdf_folder_path = $upload_dir['basedir'] . '/Woo Invoice PDF/';

        // Create the Woo Invoice PDF folder if it doesn't exist
        if (!file_exists($pdf_folder_path)) {
            mkdir($pdf_folder_path, 0755, true);
        }

        $pdf_file_path = $pdf_folder_path . $order_id . '.pdf';
        file_put_contents($pdf_file_path, $pdf_output);

        $upload_base_url = $upload_dir['baseurl'];
        $pdf_relative_path = str_replace($upload_dir['basedir'], '', $pdf_file_path);
        $download_url = $upload_base_url . $pdf_relative_path;


            // Output the download button
        echo '<a href="' . $download_url  . '" class="button" download>Download Order PDF</a>';
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
            <style>
            table {
            width: 100%;
            border-collapse: collapse;
            }

            table, th, td {
                border: 1px solid black;
            }

            th, td {
                padding: 5px;
                text-align: left;
            }
            
            h2, h3 {
                margin-bottom: 10px;
            }
                    </style></head>
                    <body>';

            // Get the promo code and discount amount
            $promo_code = $order->get_coupon_codes(); // Get the applied coupon codes
            $discount_amount = $order->get_discount_total(); // Get the discount amount

            $content = '<h2><u>Order Details of <span style="background: yellow;">#' . $order->get_order_number() . '</span></u></h2>';
            $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                        <tr>
                            <th style="text-align: left; padding: 8px;border: 1px solid black;">Order Number</th>
                            <td style="padding: 8px;border: 1px solid black;"><span style="background: yellow;">#' . $order->get_order_number() . '</span></td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding: 8px;border: 1px solid black;">Order Total</th>
                            <td style="padding: 8px;border: 1px solid black;">' . $order->get_formatted_order_total() . '</td>
                        </tr>';

                        if (!empty($promo_code) || !empty($discount_amount)) :
                            
                            $promo_code_formatted = array_map('strtoupper', $promo_code); // Convert promo code to uppercase

                            if(get_option('display_coupon_code') === 'enabled_coupon_code') :

                                $content .= '<tr>
                                                <th style="text-align: left; padding: 8px;border: 1px solid black;">Promo Code</th>
                                                <td style="padding: 8px;border: 1px solid black;">' . implode(', ', $promo_code_formatted) . '</td>
                                            </tr>';

                            endif;

                            if(get_option('display_discount_amount') === 'enabled_discount_amount') :

                                $content .= '<tr>
                                                <th style="text-align: left; padding: 8px;border: 1px solid black;">Discount Amount</th>
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

            $content .= '<h3><u>Order Items</u></h3>';

            // Add order items
            $order_items = $order->get_items();

            $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                        <tr>
                            <th style="text-align: left; padding: 8px;border: 1px solid black;">Product</th>
                            <th style="text-align: left; padding: 8px;border: 1px solid black;">Quantity</th>
                            <th style="text-align: left; padding: 8px;border: 1px solid black;">Total</th>
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
                                <th style="text-align: left; padding: 8px;border: 1px solid black;">Payment Method</th>
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
                                <th style="text-align: left; padding: 8px;border: 1px solid black;">Order Created Date</th>
                                <td style="padding: 8px;border: 1px solid black;">' . $formatted_date . '</td>
                            </tr>
                        </table>';

            endif;

            if(get_option('display_order_customer_note') === 'enabled_order_customer_note') :

                $order_notes = $order->get_customer_note();

                if(!empty($order_notes)) :

                $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                <tr>
                                    <th style="text-align: left; padding: 8px;border: 1px solid black;">Order Note</th>
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

                    $content .= '<h3><u>Shipping Details</u></h3>';
                    $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                <tr>
                                    <th style="text-align: left; padding: 8px;border: 1px solid black;">Shipping Address</th>
                                    <td style="padding: 8px;border: 1px solid black;">' . $shipping_address . '</td>
                                </tr>
                                <tr>
                                    <th style="text-align: left; padding: 8px;border: 1px solid black;">Shipping Method</th>
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

                    $content .= '<h3><u>Billing Details</u></h3>';
                    $content .= '<table style="width: 100%; border-collapse: collapse;border: 1px solid black;">
                                    <tr>
                                        <th style="text-align: left; padding: 8px;border: 1px solid black;">Billing Address</th>
                                        <td style="padding: 8px;border: 1px solid black;">' . $billing_address . '</td>
                                    </tr>;';  
                                    if(get_option('woo_invoice_phone_number') === 'enabled_phone_number') :
                                    
                                        $content .= '<tr>
                                                        <th style="text-align: left; padding: 8px;border: 1px solid black;">Phone number</th>
                                                        <td style="padding: 8px;border: 1px solid black;">' . $customer_phone . '</td>
                                                    </tr>;';
                                    endif;

                                    if(get_option('woo_invoice_email_address') === 'enabled_email_address') :
                                    
                                        $content .= '<tr>
                                                        <th style="text-align: left; padding: 8px;border: 1px solid black;">Email Address</th>
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

}

if (class_exists( 'WooInvoicePlus' )) {
        $wooinvoiceplus = new WooInvoicePlus();
}

// register_activation_hook( __FILE__, array( $wooinvoiceplus, 'wooinvoiceplus_activate' ) );

// register_deactivation_hook( __FILE__, array( $wooinvoiceplus, 'wooinvoiceplus_deactivate' ) );