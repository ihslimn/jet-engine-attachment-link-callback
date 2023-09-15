<?php
/**
 * Plugin Name: JetEngine - Get attachment file link by ID
 * Plugin URI: #
 * Description: Adds new callback to Dynamic Field widget, which allows to convert attachment file ID into attchment file link.
 * Version:     1.1.3
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

add_filter( 'jet-engine/listings/allowed-callbacks', 'jet_engine_add_attachment_link_callback', 10, 2 );
add_filter( 'jet-engine/listing/dynamic-field/callback-args', 'jet_engine_add_attachment_link_callback_args', 10, 3 );
add_filter( 'jet-engine/listings/allowed-callbacks-args', 'jet_engine_add_attachment_link_callback_controls' );

function jet_engine_add_attachment_link_callback( $callbacks ) {
	$callbacks['jet_engine_get_attachment_file_link'] = 'Get attachment file link by ID';
	return $callbacks;
}

function jet_engine_get_attachment_file_link( $attachment_id, $display_name = 'file_name', $label = '', $is_external = '' ) {

	if ( is_scalar( $attachment_id ) && false !== strpos( $attachment_id, ',' ) ) {
		$attachment_id = explode( ',', $attachment_id );	
	} elseif ( ! empty( $attachment_id['id'] ) ) {
		$attachment_id = array( $attachment_id );
	}

	$target      = '';
	$is_external = filter_var( $is_external, FILTER_VALIDATE_BOOLEAN );

	if ( $is_external ) {
		$target = ' target="_blank"';
	}

	$links = array();

	foreach ( $attachment_id as $key => $value ) {

		$file_data = \Jet_Engine_Tools::get_attachment_image_data_array( $value, 'all' );

		$url[$key] = $file_data['url'];
		$id = $file_data['id'];

		switch ( $display_name ) {
			case 'post_title':
				$name = get_the_title( $id );
				break;

			case 'current_post_title':
				$name = get_the_title( get_the_ID() );
				break;

			case 'parent_post_title':
				$parent_id = wp_get_post_parent_id( $id );

				if ( ! $parent_id ) {
					$parent_id = get_the_ID();
				}

				$name = get_the_title( $parent_id );
				break;

			case 'custom':
				$name = $label;
				break;

			default:
				$name = basename( $url[ $key ] );
				break;
		}

		$links[] = sprintf( '<a href="%1$s"%3$s>%2$s</a>', $url[ $key ], $name, $target );

	}

	return implode( '<br>', $links );

}

function jet_engine_add_attachment_link_callback_args( $args, $callback, $settings = array() ) {

	if ( 'jet_engine_get_attachment_file_link' === $callback ) {
		$args[] = isset( $settings['jet_attachment_name'] ) ? $settings['jet_attachment_name'] : 'file_name';
		$args[] = isset( $settings['jet_attachment_label'] ) ? $settings['jet_attachment_label'] : '';
		$args[] = isset( $settings['jet_attachment_is_external'] ) ? $settings['jet_attachment_is_external'] : '';
	}

	return $args;

}

function jet_engine_add_attachment_link_callback_controls( $args = array() ) {

	$args['jet_attachment_name'] = array(
		'label'       => esc_html__( 'Display name', 'jet-engine' ),
		'type'        => 'select',
		'label_block' => true,
		'description' => esc_html__( 'Select attachment name format to display', 'jet-engine' ),
		'default'     => 'file_name',
		'options'     => array(
			'file_name'          => 'File name',
			'post_title'         => 'Attachment post title',
			'current_post_title' => 'Current post title',
			'parent_post_title'  => 'Parent post title',
			'custom'             => 'Custom',
		),
		'condition'   => array(
			'dynamic_field_filter' => 'yes',
			'filter_callback'      => array( 'jet_engine_get_attachment_file_link' ),
		),
	);

	$args['jet_attachment_label'] = array(
		'label'       => esc_html__( 'Custom label', 'jet-engine' ),
		'type'        => 'text',
		'label_block' => true,
		'description' => esc_html__( 'Set custom text for the attachment link', 'jet-engine' ),
		'default'     => '',
		'condition'   => array(
			'jet_attachment_name'  => 'custom',
			'dynamic_field_filter' => 'yes',
			'filter_callback'      => array( 'jet_engine_get_attachment_file_link' ),
		),
	);

	$args['jet_attachment_is_external'] = array(
		'label'       => esc_html__( 'Open in new window', 'jet-engine' ),
		'type'        => 'switcher',
		'default'     => '',
		'condition'   => array(
			'dynamic_field_filter' => 'yes',
			'filter_callback'      => array( 'jet_engine_get_attachment_file_link' ),
		),
	);

	return $args;
}

add_action( 'init', function() {

	if ( ! function_exists( 'jet_engine' ) ) {
		return;
	}

	$plugin   = plugin_basename( __FILE__ );
	$pathinfo = pathinfo( $plugin );

	jet_engine()->modules->updater->register_plugin( array(
		'slug'    => $pathinfo['filename'],
		'file'    => $plugin,
		'version' => '1.1.3'
	) );

}, 12 );
