<?php

/**
 * TGM Init Class
 */
include_once ('class-tgm-plugin-activation.php');

function contests_register_required_plugins() {

	$plugins = array(
		array(
			'name' 	   => 'PayPal IPN',
			'slug' 	   => 'paypal-ipn',
			'required' => true,
		),
	);

	$config = array(
		'domain'       				=> 'contestd',
		'default_path' 				=> '',
    'parent_slug'         => 'plugins.php',
    'capability'          => 'manage_options',
		'menu'         				=> 'install-required-plugins',
		'has_notices'      		=> true,
		'is_automatic'    		=> true,
	);

	tgmpa( $plugins, $config );

}
add_action( 'tgmpa_register', 'contests_register_required_plugins' );