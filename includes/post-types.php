<?php
/**
 * Register post types.
 * @var [type]
 */
add_action( 'init', 'wpdrift_worker_types' );
function wpdrift_worker_types() {
	$labels = array(
		'name'               => _x( 'Client', 'post type general name', 'wpdrift-worker' ),
		'singular_name'      => _x( 'Client', 'post type singular name', 'wpdrift-worker' ),
		'menu_name'          => _x( 'Clients', 'admin menu', 'wpdrift-worker' ),
		'name_admin_bar'     => _x( 'Client', 'add new on admin bar', 'wpdrift-worker' ),
		'add_new'            => _x( 'Add New', 'Client', 'wpdrift-worker' ),
		'add_new_item'       => __( 'Add New BoClientok', 'wpdrift-worker' ),
		'new_item'           => __( 'New Client', 'wpdrift-worker' ),
		'edit_item'          => __( 'Edit Client', 'wpdrift-worker' ),
		'view_item'          => __( 'View Client', 'wpdrift-worker' ),
		'all_items'          => __( 'All Clients', 'wpdrift-worker' ),
		'search_items'       => __( 'Search Clients', 'wpdrift-worker' ),
		'parent_item_colon'  => __( 'Parent Clients:', 'wpdrift-worker' ),
		'not_found'          => __( 'No clients found.', 'wpdrift-worker' ),
		'not_found_in_trash' => __( 'No clients found in Trash.', 'wpdrift-worker' ),
	);

	$args = array(
		'labels'              => $labels,
		'description'         => __( 'Description.', 'wpdrift-worker' ),
		'public'              => true,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'query_var'           => true,
		'rewrite'             => array( 'slug' => 'oauth_client' ),
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'menu_position'       => null,
		'supports'            => array( 'title' ),
		'exclude_from_search' => true,
	);

	register_post_type( 'oauth_client', $args );
}
