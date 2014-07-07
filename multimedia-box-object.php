<?php
/**
 * @package Multimedia Box
 *
 * Private object
 *
 * @version 0.1
 */

class Multimedia_Box_Object {

	public $id, $label, $priority, $post_type, $amount, $supports;

	public function __construct( $args ) {
		add_action( 'add_meta_boxes', array( $this, 'metabox_add' ) );
		add_action( 'save_post', array( $this, 'metabox_save' ) );

		add_action( 'admin_print_styles-post.php', array( $this, 'load_styles' ) );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'load_styles' ) );

		$defaults = array(
			'id'        => null,
			'label'     => __( 'Multimedia', 'multimedia-box' ),
			'post_type' => 'post',
			'priority'  => 'default',
			'amount'    => 0,
			'supports'  => array( 'media', 'youtube', 'vimeo' )
		);

		$args = wp_parse_args( $args, $defaults );

		// Create and set properties
		foreach ( $args as $k => $v ) {
			$this->$k = $v;
		}

		if ( ! is_array( $this->supports ) ) {
			$this->supports = array( 'media' );
		}
	}


	public function metabox_add( $post_type ) {
		add_meta_box( 'multimedia_box_' . $this->post_type . '_' . $this->id, $this->label, array( $this, 'metabox' ), $this->post_type, 'advanced', $this->priority );
	}

	public function metabox( $post ) {
		$post_id = $post;

		if ( is_object( $post_id ) ) {
			$post_id = $post_id->ID;
		}

		$this->load_scripts();

		wp_nonce_field( plugin_basename( __FILE__ ), 'multimedia_box_' . $this->post_type . '_' . $this->id );

		$amount = intval( apply_filters( 'multimedia_box__count', $this->amount ) );
		$amount = intval( apply_filters( 'multimedia_box__count_' . $post->post_type, $amount ) );
		$amount = $rows = intval( apply_filters( 'multimedia_box__count_' . $this->id, $amount ) );

		$multimediaItems = get_post_meta( $post_id, '_multimedia_box_' . $this->id, true );

		if( $amount == 0 ) {
			$rows = count( $multimediaItems );
		}

		echo '<div class="multimedia_box_holder">';
		for ( $i = 0; $i < $rows; $i++ ) {
			if ( isset(  $multimediaItems[ $i ] ) ) {
				$this->get_object( $i, $multimediaItems[ $i ] );
			}
			else if ( $amount > 0 ) {
				$this->get_object( $i, array() );
			}
		}

		if( $amount == 0 ) {
			echo '<div id="multimedia_box_' . $this->id . '_addnew" class="multimedia_box addnew">';
			echo '<input type="hidden" class="multimedia_id" value="' . $this->id . '" />';
			echo '<p class="multimedia_box_unselected">' . __( 'Add new item', 'multimedia-box' ) . '</p>';
			echo '</div>';
		}

		echo '</div>';


		echo '<div id="multimedia_box_dialog_' . $this->id . '" class="multimedia_box_dialog" title="Multimedia select">';

		if ( in_array( 'media', $this->supports ) ) {
			echo '<div class="multimedia_box_media multimedia_box2">';
			echo '<h2>' . __( 'Select media item', 'multimedia-box' ) . '</h2>';
			echo '<p><input type="button" value="' . __( 'Select image', 'multimedia-box' ) . '" name="'. __( 'Select image', 'multimedia-box' ) . '" class="multimedia_box_mediabutton button" /></p>';
			echo '</div>';
		}

		if ( in_array( 'youtube', $this->supports ) ) {
			echo '<div class="multimedia_box_youtube multimedia_box2">';
			echo '<h2>' . __( 'Insert Youtube code', 'multimedia-box' ) . '</h2>';
			echo '<p><label>Youtube code: </label><input type="text" class="multimedia_box_youtubecode" name="multimedia_box_youtubecode" value="" /></p>';
			echo '<p>' . __( 'The bold text is the code you need to give up:', 'multimedia-box') . ' http://www.youtube.com/watch?v=<strong>6tmOiQlsUHA</strong></p>';
			echo '</div>';
		}

		if ( in_array( 'vimeo', $this->supports ) ) {
			echo '<div class="multimedia_box_vimeo multimedia_box2">';
			echo '<h2>' . __( 'Insert Vimeo code', 'multimedia-box' ) . '</h2>';
			echo '<p><label>Vimeo code: </label><input type="text" class="multimedia_box_vimeocode" name="multimedia_box_vimeocode" value="" /></p>';
			echo '<p>' . __( 'The bold text is the code you need to give up:', 'multimedia-box' ) . ' http://vimeo.com/<strong>25927524</strong></p>';
			echo '</div>';
		}

		echo '<div class="multimedia_box_bottom">';
		echo '<input type="button" value="' . __( 'Save', 'multimedia-box' )  . '" class="multimedia_box_savebutton button-primary" />';
		echo '</div>';

		echo '</div>';

		echo '<div class="clear"></div>';
	}

	private function get_object( $number, $object_data ) {
		echo '<div id="multimedia_box_' . $this->id . '_' . $number . '" class="multimedia_box">';
	
		if ( isset( $object_data['type'] ) ) {
			echo '<input type="hidden" class="multimedia_box_type" name="' . $this->id . '_type[' . $number . ']" value="' . $object_data['type'] . '" />';
		}
		else {
			echo '<input type="hidden" class="multimedia_box_type" name="' . $this->id . '_type[' . $number . ']" value="" />';
		}

		if ( isset( $object_data['imageID'] ) ) {
			echo '<input type="hidden" class="multimedia_box_imageID" name="' . $this->id . '_imageID[' . $number . ']" value="' . $object_data['imageID'] . '" />';
		}
		else {
			echo '<input type="hidden" class="multimedia_box_imageID" name="' . $this->id . '_imageID[' . $number . ']" value="" />';
		}

		if ( isset( $object_data['movieCode'] ) ) {
			echo '<input type="hidden" class="multimedia_box_moviecode" name="' . $this->id . '_moviecode[' . $number . ']" value="' . $object_data['movieCode'] . '" />';
		}
		else {
			echo '<input type="hidden" class="multimedia_box_moviecode" name="' . $this->id . '_moviecode[' . $number . ']" value="" />';
		}
	
		if ( isset( $object_data['image'] ) ) {
			echo '<input type="hidden" class="multimedia_box_image" name="' . $this->id . '_imageURL[' . $number . ']" value="' . $object_data['image'] . '" />';
			echo '<p class="multimedia_box_selected"><img src="'. $object_data['image'] .'" alt="" class="multimedia_box_image_src" /><a href="#" class="remove">' . __('Remove', 'multimedia-box') . '</a></p>';
			echo '<p class="multimedia_box_unselected" style="display:none">' . __( 'Select an item', 'multimedia-box' ) . '</p>';
		}
		else {
			echo '<input type="hidden" class="multimedia_box_image" name="' . $this->id . '_imageURL[' . $number . ']" value="" />';
			echo '<p class="multimedia_box_selected" style="display:none"><img src="" alt="" class="multimedia_box_image_src" /><a href="#" class="remove">' . __('Remove', 'multimedia-box') . '</a></p>';
			echo '<p class="multimedia_box_unselected">' . __( 'Select an item', 'multimedia-box' ) . '</p>';
		}

		echo '</div>';
	}

	public function metabox_save( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$key = 'multimedia_box_' . $this->post_type . '_' . $this->id;
		
		if ( ! isset( $_POST[ $key ] ) || ! wp_verify_nonce( $_POST[ $key ], plugin_basename( __FILE__ ) ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$array = array();

		if ( isset( $_POST[ $this->id . '_imageURL' ] ) ) {
			$object_keys = array_keys( $_POST[ $this->id . '_imageURL' ] );
	
			if ( is_array( $object_keys ) ) {
				foreach ( $object_keys as $object_key ) {
					if ( ! empty( $_POST[ $this->id . '_imageURL' ][ $object_key ] ) ) {
						array_push( $array, array(
							'type'      => $_POST[ $this->id . '_type' ][ $object_key ],
							'image'     => $_POST[ $this->id . '_imageURL' ][ $object_key ],
							'imageID'   => $_POST[ $this->id . '_imageID' ][ $object_key ],
							'movieCode' => $_POST[ $this->id . '_moviecode' ][ $object_key ]
						) );
					}
				}
			}
		}

		update_post_meta( $post_id, '_multimedia_box_' . $this->id, $array );
	}

	public function load_styles() {
		global $post_type;

		if ( $post_type == $this->post_type ) {
			wp_enqueue_style( 'multimedia-box' );
		}
	}

	private function load_scripts () {
		if ( ! wp_script_is( 'multimedia-box' ) ) {
			wp_enqueue_script( 'multimedia-box' );

			if ( ! is_admin() ) {
				$data = array( 'url' => SITECOOKIEPATH, 'ajax' => admin_url( 'admin-ajax.php' ) );
				wp_localize_script( 'multimedia-box', 'multimedia_box', $data );
			}
		}
	}
}
