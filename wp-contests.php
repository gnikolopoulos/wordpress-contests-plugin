<?php
/**
* Plugin Name: Wordpress Contest Entries
* Plugin URI: http://interactive-design.gr
* Description: A contest entries plugin for wordpress
* Version: 1.2
* Author: George Nikolopoulos
* Author URI: http://interactive-design.gr
* Text Domain: contests
**/

function contests_load_plugin_textdomain() {
  load_plugin_textdomain( 'contests', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'contests_load_plugin_textdomain' );

// Definitions
define( 'PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'RWMB_DIR', PLUGIN_DIR . 'inc/meta-box/' );
define( 'RWMB_URL', PLUGIN_DIR_URL . 'inc/meta-box/' );

// Include the Redux files
if ( !class_exists( 'ReduxFramework' ) && file_exists( dirname( __FILE__ ) . '/admin/framework.php' ) ) {
    require_once( dirname( __FILE__ ) . '/admin/framework.php' );
}
require_once (dirname(__FILE__) . '/admin/admin-config.php');

// Meta boxes
require_once RWMB_DIR . 'meta-box.php';
require_once RWMB_DIR . 'fields.php';

// TGM activation class
require_once PLUGIN_DIR . 'inc/tgm/tgm-init.php';

// Add custom post type
add_action( 'init', 'add_post_type' );
function add_post_type() {
  // Contestants Post Type
  $labels_cont = array(
    'name'               => _x( 'Contestants', 'contestants' ),
    'singular_name'      => _x( 'Contestant', 'contestant' ),
    'add_new'            => _x( 'Add New', 'contestant' ),
    'add_new_item'       => __( 'Add New Contestant', 'contests' ),
    'edit_item'          => __( 'Edit Contestant', 'contests' ),
    'new_item'           => __( 'New Contestant', 'contests' ),
    'all_items'          => __( 'All Contestants', 'contests' ),
    'view_item'          => __( 'View Contestant', 'contests' ),
    'search_items'       => __( 'Search Contestants', 'contests' ),
    'not_found'          => __( 'No contestans found', 'contests' ),
    'not_found_in_trash' => __( 'No contestant found in the Trash', 'contests' ),
    'parent_item_colon'  => '',
    'menu_name'          => __('Contestant', 'contests')
  );
  $args_cont = array(
    'labels'        => $labels_cont,
    'description'   => __('Holds our Contestants specific data', 'contests'),
    'public'        => true,
    'supports'      => array( 'title' ),
    'has_archive'   => true,
    'menu_icon'     => 'dashicons-lightbulb',
  );
  register_post_type( 'contestants', $args_cont );
}

//Register and add scripts
function add_scripts() {
  // Styles
  wp_enqueue_style( 'style', PLUGIN_DIR_URL . 'inc/css/style.css' );
  wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' );

  // Scripts
  wp_enqueue_script( 'validate', PLUGIN_DIR_URL . 'inc/js/jquery.validate.min.js', array('jquery'), '1.14.0', true );
  wp_enqueue_script( 'methods', PLUGIN_DIR_URL . 'inc/js/additional-methods.min.js', array('jquery','validate'), '1.14.0', true );
  wp_enqueue_script( 'jquery-form', array('jquery'), false, true );
  wp_enqueue_script( 'main', PLUGIN_DIR_URL . 'inc/js/main.js', array('jquery','jquery-form', 'validate'), '1.0.8', true );
  wp_localize_script( 'main', 'ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'add_scripts' );

// Add columns to custom post type edit screen
add_filter('manage_edit-contestants_columns', 'contestants_columns');
function contestants_columns($columns) {
  $columns['number'] = 'Number';
  $columns['fname'] = 'First Name';
  $columns['lname'] = 'Last Name';
  $columns['email'] = 'Email';
  $columns['status']  = 'Status';
  unset($columns['date']);
  return $columns;
}

add_action("manage_posts_custom_column",  "contestants_custom_columns");
function contestants_custom_columns($column){
  global $post;
  switch ($column) {
    case "number":
      echo rwmb_meta('field_entry_unique', $post->ID);
      break;
    case "fname":
      echo rwmb_meta('field_first_name', $post->ID);
      break;
    case "lname":
      echo rwmb_meta('field_last_name', $post->ID);
      break;
    case "email":
      echo rwmb_meta('field_email', $post->ID);
      break;
    case "status":
      echo rwmb_meta('field_status', $post->ID);
      break;
  }
}

// Handle ajax add entry
add_action('wp_ajax_entry_add', 'ajax_entry');
add_action('wp_ajax_nopriv_entry_add', 'ajax_entry');
function ajax_entry() {
  check_ajax_referer( 'add-entry-nonce', 'nonce' );

  $email = urldecode($_POST['email']);
  $fname = wp_strip_all_tags($_POST['fname']);
  $lname = wp_strip_all_tags($_POST['lname']);
  $addr  = wp_strip_all_tags($_POST['address']);
  $city  = wp_strip_all_tags($_POST['city']);
  $state  = wp_strip_all_tags($_POST['state']);
  $zip  = wp_strip_all_tags($_POST['zip']);
  $phone = wp_strip_all_tags($_POST['phone']);
  $file = $_POST['file'];

  $success["text"] = "<i class='fa fa-check'></i> Thanks for joining!<br />You will be reditected to step 2 in a few seconds";
  $error["text"] = "<i class='fa fa-exclamation-triangle'></i> Looks like something went wrong! Try again maybe?";
  $duplicate["text"] = "<i class='fa fa-exclamation-triangle'></i> Looks like you have already joined!";

  if( !empty($email) && !empty($fname) && !empty($lname) && !empty($addr) ) {
    $postData = array(
      'post_title'  => $fname . ' ' . $lname,
      'post_type'   => 'contestants',
      'post_status' => 'publish'
    );

    if( !check_contestant($email) ) {
      $post = wp_insert_post( $postData );
    } else {
      wp_delete_attachment($file);
      echo json_encode($duplicate);
      exit;
    }

    if( $post != -1 ) {
      add_post_meta($post, "field_entry_unique", "WTH-".$post);
      add_post_meta($post, "field_first_name", $fname);
      add_post_meta($post, "field_last_name", $lname);
      add_post_meta($post, "field_email", $email);
      add_post_meta($post, "field_address", $addr);
      add_post_meta($post, "field_address_city", $city);
      add_post_meta($post, "field_address_state", $state);
      add_post_meta($post, "field_address_zip", $zip);
      add_post_meta($post, "field_status", "pending");
      add_post_meta($post, "field_phone", $phone);
      add_post_meta($post, "field_file", $file);

      $success["id"] = $post;
      echo json_encode($success);
    } else {
      echo json_encode($error);
    }
  } else {
    echo json_encode($error);
  }
  exit;
}

// Check is contestant already exists
function check_contestant( $email ) {
  $email_list = array();
  $args = array(
    'post_type'=> 'contestants',
    'posts_per_page' => -1,
    );

  $the_query = new WP_Query( $args );
  if($the_query->have_posts() ){
    while ( $the_query->have_posts() ) {
      $the_query->the_post();
      $email_list[] = rwmb_meta("field_email");
    }
  }
  wp_reset_query();

  if( in_array($email, $email_list) ) {
    return true;
  } else {
    return false;
  }
}

// Add table view shortcode
add_shortcode('contestants', 'contestants_view');
function contestants_view( $atts ) {
  extract(shortcode_atts(array(
      'name'    => "yes",
   ), $atts));

  $html  = '<table>';
  $html .= '<thead>';
  $html .= '<th>#</th>';
  if( $name == "yes" ) {
    $html .= '<th>Name</th>';
  }
  $html .= '<th>File</th>';
  $html .= '</thead>';
  $html .= '<tbody>';


  $args = array(
    'post_type'=> 'contestants',
    'posts_per_page' => -1,
    );

  $the_query = new WP_Query( $args );
  if($the_query->have_posts() ){
    while ( $the_query->have_posts() ) {
      $the_query->the_post();
      if( rwmb_meta("field_status") ==  "valid" ) {
        $file_url = rwmb_meta("field_file", "type=file_advanced");
        foreach($file_url as $file) {
          $url = $file["url"];
          $name = $file["title"];
        }
        $html .= '<tr>';
        $html .= '<td>' . rwmb_meta("field_entry_unique") . '</td>';
        if( $name == "yes" ) {
          $html .= '<td>' . rwmb_meta("field_first_name") . ' ' . rwmb_meta("field_last_name") . '</td>';
        }
        $html .= '<td><a href="' . $url . '">' . $name . '</a></td>';
        $html .= '</tr>';
      }
    }
  }
  wp_reset_query();
  $html .= '</tbody>
            </table>';
  return $html;
}

// Add Form Shortcode
add_shortcode('contest-entry-form', 'entry_form');
function entry_form() {
  global $contest;
  if( $contest['paypal_mode'] ) {
    $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
  } else {
    $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
  }
  $html = '<div class="form-entry">
            <form class="form-inline contest-entry" role="form" enctype="multipart/form-data">
              <div class="form-group">
                <input type="text" class="form-control" name="first_name" id="first-name" placeholder="First name">
                <input type="text" class="form-control" name="last_name" id="last-name" placeholder="Last name">
              </div>

              <div class="form-group wide">
                <input type="email" class="form-control" name="email" id="email" placeholder="Email">
              </div>

              <div class="form-group wide">
                <input type="text" class="form-control" name="address" id="address" placeholder="Address">
              </div>

              <div class="form-group narrow">
                <input type="text" class="form-control" name="address_city" id="address_city" placeholder="City">
                <input type="text" class="form-control" name="address_state" id="address_state" placeholder="State">
                <input type="text" class="form-control" name="address_zip" id="address_zip" placeholder="Zip">
              </div>

              <div class="form-group wide">
                <input type="text" class="form-control" name="phone" id="phone" placeholder="Phone Number">
              </div>

              <div class="form-group wide">
                <input type="file" name="entryfile" class="form-control" id="entryfile" >
                <input type="hidden" name="upload_nonce" id="upload_nonce" value="' . wp_create_nonce( "media-form" ) .'" />
              </div>

              <input type="hidden" name="security" id="security" value="' . wp_create_nonce( "add-entry-nonce" ) .'" />
              <input type="hidden" name="url" id="url" value="' . get_bloginfo("url") . '" />

              <div class="check-group">
                <input type="checkbox" name="rules" id="rules" value="rules" />I have read and understood the rules of this contest<br />
                <input type="checkbox" name="age" id="age" value="age" />I am over 18 years old<br />
                <input type="checkbox" name="citizen" id="citizen" value="citizen" />I am US citizen or have permanent resident status (Green Card)
              </div>

              <span style="display: none;" id="validation"></span>

              <button type="submit" class="btn btn-submit">Proceed to Step 2</button>
            </form>
            <div class="message" style="display: none;">
              <h2></h2>
            </div>
            <div id="paypal" style="display: none;">
              <form action="' . $paypal_url . '" method="post">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="business" value="' . $contest['paypal_email'] . '">
                <input type="hidden" name="item_name" value="Contest entry">
                <input type="hidden" id="item_code" name="item_number" value="1">
                <input type="hidden" name="amount" value="' . $contest['entry_price'] . '">
                <input type="hidden" name="no_shipping" value="1">
                <input type="hidden" name="no_note" value="1">
                <input type="hidden" name="currency_code" value="' . $contest['currency_code'] . '">
                <input type="hidden" name="lc" value="US">
                <input type="hidden" name="bn" value="Win This Home">
                <input type="hidden" name="notify_url" value="' . get_bloginfo("url") . '/?AngellEYE_Paypal_Ipn_For_Wordpress&action=ipn_handler">
                <input type="hidden" name="return" value="' . get_permalink( $contest['return_page'] ) . '">
              </form>
            </div>
          </div>';
  return $html;
}

// Set paypal fields as read-only
add_filter( 'rwmb_field_transaction_html', 'field_input_readonly' );
add_filter( 'rwmb_field_payer_email_html', 'field_input_readonly' );
add_filter( 'rwmb_field_pay_amount_html', 'field_input_readonly' );
function field_input_readonly( $html )
{
    return str_replace( '<input', '<input readonly', $html );
}

// Handle IPN results
add_action('paypal_ipn_for_wordpress_payment_status_completed', 'handle_ipn_update');
function handle_ipn_update( $posted ) {

  $status = isset($posted['payment_status']) ? $posted['payment_status'] : '';
  $item_number = isset($posted['item_number']) ? $posted['item_number'] : '';
  $trans_id = isset($posted['txn_id']) ? $posted['txn_id'] : '';
  $payer = isset($posted['payer_email']) ? $posted['payer_email'] : '';
  $total = isset($posted['payment_gross']) ? $posted['payment_gross'] : '';

  update_post_meta($item_number, "field_status", "valid");
  add_post_meta($item_number, "field_transaction", $trans_id);
  add_post_meta($item_number, "field_payer_email", $payer);
  add_post_meta($item_number, "field_pay_amount", $total);

  notify_admin( $posted );
}

add_action('paypal_ipn_for_wordpress_payment_status_reversed', 'handle_ipn_delete');
function handle_ipn_delete( $posted ) {
  $item_number = isset($posted['item_number']) ? $posted['item_number'] : '';
  wp_delete_post( $item_number );
}

// Send email to admin
function notify_admin( $posted ) {
  global $contest;
  add_filter( 'wp_mail_content_type', 'set_html_content_type' );

  $item_number = $posted['item_number'];
  $files = rwmb_meta("field_file", "type=file_advanced", $item_number);
  foreach($files as $file) {
    $attachments = $file['path'];
    $attachments_url = $file['url'];
  }
  $headers = 'From: ' . get_bloginfo("name") . ' <' . $contest['email_from'] . '>' . "\r\n";
  $mailTo = $contest['email_to'];
  $mailSubject = $contest['email_subject'];
  $mailMessage = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                  <html xmlns="http://www.w3.org/1999/xhtml">

                  <head>
                    <title></title>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <meta name="robots" content="noindex,nofollow" />
                    <meta property="og:title" content="My First Campaign" />
                  </head>

                  <body style="margin: 0;mso-line-height-rule: exactly;padding: 0;min-width: 100%;background-color: #fbfbfb">
                    <center class="wrapper" style="display: table;table-layout: fixed;width: 100%;min-width: 620px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: #fbfbfb">
                      <table class="gmail" style="border-collapse: collapse;border-spacing: 0;width: 650px;min-width: 650px">
                        <tbody>
                          <tr>
                            <td style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px">&nbsp;</td>
                          </tr>
                        </tbody>
                      </table>

                      <table class="border" style="border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto" width="602">
                        <tbody>
                          <tr>
                            <td style="padding: 0;vertical-align: top">&#8203;</td>
                          </tr>
                        </tbody>
                      </table>

                      <table class="centered" style="border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto">
                        <tbody>
                          <tr>
                            <td class="border" style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px">&#8203;</td>
                            <td style="padding: 0;vertical-align: top">
                              <table class="one-col" style="border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 600px;background-color: #ffffff;font-size: 14px;table-layout: fixed" emb-background-style>
                                <tbody>
                                  <tr>
                                    <td class="column" style="padding: 0;vertical-align: top;text-align: left">
                                      <div>
                                        <div class="column-top" style="font-size: 32px;line-height: 32px">&nbsp;</div>
                                      </div>
                                      <table class="contents" style="border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%">
                                        <tbody>
                                          <tr>
                                            <td class="padded" style="padding: 0;vertical-align: top;padding-left: 32px;padding-right: 32px;word-break: break-word;word-wrap: break-word">
                                              <h1 style="Margin-top: 0;color: #565656;font-weight: 700;font-size: 36px;Margin-bottom: 18px;font-family: sans-serif;line-height: 42px">' . $contest['email_subject'] . '</h1>
                                              <p style="Margin-top: 0;color: #565656;font-family: Georgia,serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px">Hey there!</p>
                                              <p style="Margin-top: 0;color: #565656;font-family: Georgia,serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px">There is a new contest entry for you. Here are the specifics:</p>
                                              <ul style="Margin-top: 0;padding-left: 0;color: #565656;font-family: Georgia,serif;font-size: 16px;line-height: 25px;Margin-left: 18px;Margin-bottom: 25px">
                                                <li style="Margin-top: 0;padding-left: 0">Entry ID: WTH-' . $item_number . '</li>
                                                <li style="Margin-top: 0;padding-left: 0">First Name: ' . get_post_meta($item_number, "field_first_name", true) . '</li>
                                                <li style="Margin-top: 0;padding-left: 0">Last Name: ' . get_post_meta($item_number, "field_last_name", true) . '</li>
                                                <li style="Margin-top: 0;padding-left: 0">Address: ' . get_post_meta($item_number, "field_address", true) . '</li>
                                                <li style="Margin-top: 0;padding-left: 0">City / State / Zip: ' . get_post_meta($item_number, "field_address_city", true) . ', ' . get_post_meta($item_number, "field_address_state", true) . ', ' . get_post_meta($item_number, "field_address_zip", true) . '</li>
                                                <li style="Margin-top: 0;padding-left: 0">Email: ' . get_post_meta($item_number, "field_email", true) . '</li>
                                                <li style="Margin-top: 0;padding-left: 0">Phone: ' . get_post_meta($item_number, "field_phone", true) . '</li>
                                              </ul>
                                              <p style="Margin-top: 0;color: #565656;font-family: Georgia,serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px">You can download the file by clicking the button below. The file is also available as an attachment.</p>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                      <table class="contents" style="border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%">
                                        <tbody>
                                          <tr>
                                            <td class="padded" style="padding: 0;vertical-align: top;padding-left: 32px;padding-right: 32px;word-break: break-word;word-wrap: break-word">
                                              <div class="btn" style="Margin-bottom: 24px;text-align: center">
                                                <a style="border-radius: 3px;display: inline-block;font-size: 14px;font-weight: 700;line-height: 24px;padding: 13px 35px 12px 35px;text-align: center;text-decoration: none !important;transition: opacity 0.2s ease-in;color: #fff;font-family: Georgia,serif;background-color: #41637e" href="' . $attachments_url . '">View submission</a>
                                              </div>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>

                                      <div class="column-bottom" style="font-size: 8px;line-height: 8px">&nbsp;</div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                            <td class="border" style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px">&#8203;</td>
                          </tr>
                        </tbody>
                      </table>

                      <table class="border" style="border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto" width="602">
                        <tbody>
                          <tr>
                             <td style="padding: 0;vertical-align: top">&#8203;</td>
                          </tr>
                        </tbody>
                      </table>

                      <div class="spacer" style="font-size: 1px;line-height: 32px;width: 100%">&nbsp;</div>
                    </center>
                  </body>
                  </html>';
  wp_mail( $mailTo, $mailSubject, $mailMessage, $headers, $attachments );

  remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
}

function set_html_content_type() {
  return 'text/html';
}

?>