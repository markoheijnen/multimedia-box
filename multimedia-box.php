<?php
/**
 * @package Multimedia Box
 * @version 0.2
 */
/*
Plugin Name: Multimedia Box
Plugin URI:  http://markoheijnen.com
Description: This plugin allows you to add multiple media items to a post without adding it to the content of your post
Version: 0.2
Author: Marko Heijnen
Author URI: http://markoheijnen.com
*/

require_once 'multimedia-box-object.php';

class Multimedia_Box {

	public function __construct() {
		add_action( 'init', array( $this, 'register_styles_scripts' ), 1 );
		add_action( 'admin_init', array( $this, 'add_meta_boxes' ) );

		add_action( 'wp_ajax_multimedia_get_code', array( $this, 'ajax_get_code' ) );
		add_action( 'wp_ajax_nopriv_multimedia_get_code', array( $this, 'ajax_get_code' ) );
	}

	public function register_styles_scripts() {
		wp_register_style( 'multimedia-box', plugins_url( 'css/multimedia.css', __FILE__ ), array( 'wp-jquery-ui-dialog', 'thickbox' ), '1.0' );
		wp_register_script( 'multimedia-box', plugins_url( 'js/multimedia.js', __FILE__ ), array( 'jquery-ui-dialog', 'thickbox' ), '1.0' );
	}


	public function add_meta_boxes() {
		/**
		 * Fires after all built-in meta boxes have been added.
		 *
		 * @since 3.0.0
		 *
		 * @param string  $post_type Post type.
		 * @param WP_Post $post      Post object.
		 */
		do_action( 'multimedia_box_register' );
	}


	public function register( $args = array() ) {
		// Need these args to be set at a minimum
		if ( ! isset( $args['id'] ) || ! isset( $args['label'] ) ) {
			if ( defined( 'WP_DEBUG' ) &&  WP_DEBUG ) {
				trigger_error( sprintf( "The 'label' and 'id' values of the 'args' parameter of '%s::%s()' are required" , __CLASS__ , __FUNCTION__ ) );
			}

			return false;
		}

		new Multimedia_Box_Object( $args );

		return true;
	}

	public function getMedia( $id, $post_id = null ) {
		if ( $post_id == null ) {
			$post_id = get_the_ID();
		}

		$media = get_post_meta( $post_id, '_multimedia_box_' . $id, true );

		if ( empty( $media ) ) {
			$media = array();
		}

		return $media;
	}

	public function getYoutubeHTML( $code, $args ) {
		$defaults = array(
			'height'        => 315,
			'width'         => 560,
			'show_hd'       => false,
			'propose_video' => false,
			'autoplay'      => false,
			'showinfo'      => true,
			'border'        => true,
			'theme'         => 'dark' //dark or light
		);

		$args = wp_parse_args( $args, $defaults );

		$protocol = is_ssl() ? 'https://' : 'http://';
		$url = $protocol . 'www.youtube.com/embed/' . $code;

		$query = array();

		if ( (bool)$args['propose_video'] == false ) { $query['rel'] = 0; }
		if ( (bool)$args['show_hd'] == true ) { $query['hd'] = 1; }
		if ( (bool)$args['autoplay'] == true ) { $query['autoplay'] = 1; }
		if ( (bool)$args['showinfo'] == false ) { $query['showinfo'] = 0; }
		if ( (bool)$args['border'] == true ) { $query['border'] = 1; }
		if ( $args['theme'] == 'light' ) { $query['theme'] = 'light'; }

		$url .= '?' . http_build_query( $query, '', '&amp;' );

		return '<iframe width="' . intval( $args['width'] ) . '" height="' . intval( $args['height'] ) . '" src="' . $url . '" frameborder="0" webkitallowfullscreen allowfullscreen></iframe>';
	}

	public function getVimeoHTML( $code, $args ) {
		$defaults = array(
			'height'       => 315,
			'width'        => 560,
			'autoplay'     => false,
			'show_potrait' => true,
			'show_title'   => true,
			'show_byline'  => true,
			'color'        => null
		);

		$args  = wp_parse_args( $args, $defaults );
		$url   = 'http://player.vimeo.com/video/' . $code;
		$query = array();

		if ( (bool)$args['autoplay'] == true ) { $query['autoplay'] = 1; }
		if ( (bool)$args['show_potrait'] == false ) { $query['potreit'] = 0; }
		if ( (bool)$args['show_title'] == false ) { $query['title'] = 0; }
		if ( (bool)$args['show_byline'] == false ) { $query['byline'] = 0; }
		if ( ! empty( $args['color'] ) ) { $query['color'] = $args['color']; }

		$url .= '?' . http_build_query( $query, '', '&amp;' );	

		return '<iframe width="' . intval( $args['width'] ) . '" height="'. intval( $args['height'] ) . '" src="' . $url . '" frameborder="0" webkitAllowFullScreen allowFullScreen></iframe>';
	}


	function ajax_get_code() {
		header('Content-type: application/json');

		if ( isset( $_POST['type'], $_POST['code'] ) ) {
			if ( 'youtube' == $_POST['type'] ) {
				$response = wp_remote_get( 'http://gdata.youtube.com/feeds/api/videos/' . esc_attr( $_POST['code'] ) . '?alt=json' );

				if ( ! is_wp_error( $response ) ) {
					$response = json_decode( wp_remote_retrieve_body( $response ), true );
					$image = $response['entry']['media$group']['media$thumbnail'][0]['url'];

					echo json_encode( array( 'success' => true, 'image' => $image ) );
					die();
				}
			}
		}

		echo json_encode( array( 'success' => false ) );
		die();
	}
}

$GLOBALS['multimedia_box'] = new Multimedia_Box();

function multimedia_box_register( $args = array() ) {
	return $GLOBALS['multimedia_box']->register( $args );
}

/*
add_action( 'multimedia_box_register', 'register_slide_box' );
function register_slide_box() {
	multimedia_box_register( array( 'id' => 'example', 'label' => 'Example', 'post_type' => 'slide' ) );
}
*/
