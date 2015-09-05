<?php
/**
* Plugin Name: Wordpress Contest Entries
* Plugin URI: http://interactive-design.gr
* Description: A contest entries plugin for wordpress
* Version: 1.0
* Author: George Nikolopoulos
* Author URI: http://interactive-design.gr
**/

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

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
      echo json_encode($duplicate);
      exit;
    }

    if( $post != -1 ) {
      add_post_meta($post, "field_entry_unique", "WTH-".$post);
      add_post_meta($post, "field_first_name", $fname);
      add_post_meta($post, "field_last_name", $lname);
      add_post_meta($post, "field_email", $email);
      add_post_meta($post, "field_address", $addr);
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
  $html = '<div class="form-entry">
            <form class="form-inline contest-entry" role="form" enctype="multipart/form-data">
              <div class="form-group">
                <input type="text" class="form-control" name="first_name" id="first-name" placeholder="First name">
                <input type="text" class="form-control" name="last_name" id="last-name" placeholder="Last name">
              </div>

              <div class="form-group wide">
                <input type="email" class="form-control" name="email" id="email" placeholder="Email">
              </div>

              <div class="form-group">
                <input type="text" class="form-control" name="address" id="address" placeholder="Address">
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
              <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
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
}

add_action('paypal_ipn_for_wordpress_payment_status_reversed', 'handle_ipn_delete');
function handle_ipn_delete( $posted ) {
  $item_number = isset($posted['item_number']) ? $posted['item_number'] : '';
  wp_delete_post( $item_number );
}

?>