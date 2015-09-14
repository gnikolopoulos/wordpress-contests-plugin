<?php
if (!defined('ABSPATH')) die('No direct access allowed');

add_filter( 'rwmb_meta_boxes', 'theme_register_meta_boxes' );

function theme_register_meta_boxes( $meta_boxes ) {
    $prefix = 'field_';

    // Options meta box
    $meta_boxes[] = array(
        'id' => 'entry-options', // Meta box id, UNIQUE per meta box. Optional since 4.1.5
        'title' => __( 'Contest Entry Info', 'contests' ), // Meta box title - Will appear at the drag and drop handle bar. Required.
        'pages' => array( 'contestants' ), // Post types, accept custom post types as well - DEFAULT is array('post'). Optional
        'context' => 'normal', // Where the meta box appear: normal (default), advanced, side. Optional.
        'priority' => 'high', // Order of meta box: high (default), low. Optional.
        'autosave' => true, // Auto save: true, false (default). Optional.
        'fields' => array(

            // ID
            array(
                'name' => __( 'Entry ID', 'contests' ),
                'id' => "{$prefix}entry_unique",
                'type' => 'text',
                'std' => __( '', 'contests' ),
                'clone' => false,
            ),

            // First name
            array(
                'name' => __( 'First Name', 'contests' ),
                'id' => "{$prefix}first_name",
                'type' => 'text',
                'std' => __( 'John', 'contests' ),
                'clone' => false,
            ),

            // Last name
            array(
                'name' => __( 'Last Name', 'contests' ),
                'id' => "{$prefix}last_name",
                'type' => 'text',
                'std' => __( 'Doe', 'contests' ),
                'clone' => false,
            ),

            // Address
            array(
                'name' => __( 'Address', 'contests' ),
                'id' => "{$prefix}address",
                'type' => 'text',
                'std' => __( 'Some Street 12', 'contests' ),
                'clone' => false,
            ),

            // City
            array(
                'name' => __( 'City', 'contests' ),
                'id' => "{$prefix}address_city",
                'type' => 'text',
                'std' => __( 'Miami', 'contests' ),
                'clone' => false,
            ),

            // State
            array(
                'name' => __( 'State', 'contests' ),
                'id' => "{$prefix}address_state",
                'type' => 'text',
                'std' => __( 'FL', 'contests' ),
                'clone' => false,
            ),

            // Zip
            array(
                'name' => __( 'Zip', 'contests' ),
                'id' => "{$prefix}address_zip",
                'type' => 'text',
                'std' => __( '12345', 'contests' ),
                'clone' => false,
            ),

            // Email
            array(
                'name' => __( 'Email', 'contests' ),
                'id'   => "{$prefix}email",
                'type' => 'email',
                'std'  => 'name@email.com',
            ),

            // Phone
            array(
                'name' => __( 'Phone Number', 'contests' ),
                'id' => "{$prefix}phone",
                'type' => 'text',
                'std' => __( '(800) 123 4567', 'contests' ),
                'clone' => false,
            ),

            // Title
            array(
                'name' => __( 'Item Title', 'contests' ),
                'id' => "{$prefix}title",
                'type' => 'text',
                'std' => __( 'Lorem Ipsum', 'contests' ),
                'clone' => false,
            ),

            // Title
            array(
                'name' => __( 'Item Description', 'contests' ),
                'id' => "{$prefix}description",
                'type' => 'textarea',
                'std' => __( 'Lorem Ipsum', 'contests' ),
                'clone' => false,
            ),

            // File
            array(
                'name'             => __( 'File', 'contests' ),
                'id'               => "{$prefix}file",
                'type'             => 'file_advanced',
                'max_file_uploads' => 1,
                'mime_type'        => '', // Leave blank for all file types
            ),

            // Status
            array(
                'name'    => __( 'Status', 'contests' ),
                'id'      => "{$prefix}status",
                'type'    => 'radio',
                'options' => array(
                    'pending' => __( 'Pending', 'contests' ),
                    'valid' => __( 'Valid', 'contests' ),
                ),
            )

        ),
    );

    $meta_boxes[] = array(
        'id' => 'paypal_data', // Meta box id, UNIQUE per meta box. Optional since 4.1.5
        'title' => __( 'PayPal Transaction Info', 'contests' ), // Meta box title - Will appear at the drag and drop handle bar. Required.
        'pages' => array( 'contestants' ), // Post types, accept custom post types as well - DEFAULT is array('post'). Optional
        'context' => 'normal', // Where the meta box appear: normal (default), advanced, side. Optional.
        'priority' => 'high', // Order of meta box: high (default), low. Optional.
        'autosave' => true, // Auto save: true, false (default). Optional.
        'fields' => array(

            // ID
            array(
                'name' => __( 'Transaction ID', 'contests' ),
                'id' => "{$prefix}transaction",
                'type' => 'text',
                'std' => __( '', 'contests' ),
                'clone' => false,
            ),

            // Email
            array(
                'name' => __( 'Payer Email', 'contests' ),
                'id' => "{$prefix}payer_email",
                'type' => 'text',
                'std' => __( '', 'contests' ),
                'clone' => false,
            ),

            // Amount
            array(
                'name' => __( 'Amount', 'contests' ),
                'id' => "{$prefix}pay_amount",
                'type' => 'text',
                'std' => __( '', 'contests' ),
                'clone' => false,
            ),

        ),
    );

    return $meta_boxes;
}

?>