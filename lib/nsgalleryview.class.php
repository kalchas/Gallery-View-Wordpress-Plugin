<?php

class NSGalleryView {
	
	private static $instance;
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	private function __construct () {
		
		add_filter('post_gallery', array($this, 'gallery'), 10, 2);
		
		if (! is_admin()) {
		
			add_action('init', array($this, 'add_scripts'));
			add_action('init', array($this, 'add_styles'));
			
		}
		
	}
	
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public static function get_instance () {
		
		if (empty(self::$instance)) {
			
			$classname = __CLASS__;
			self::$instance = new $classname;
			
		}
		
		return self::$instance;
		
	}
	
	public function add_scripts () {
		
		wp_enqueue_script('jquery-easing-plugin', NSGV_DIR_URL . 'js/jquery.easing.1.3.js', array('jquery'), false);
		wp_enqueue_script('jquery-timers', NSGV_DIR_URL . 'js/jquery.timers-1.2.js', array('jquery'), false );
		wp_enqueue_script('jquery-galleryview', NSGV_DIR_URL . 'js/jquery.galleryview-2.1.js', array('jquery-timers', 'jquery-easing-plugin'), false );
		
	}
	
	public function add_styles () {
		
		wp_enqueue_style('jquery-galleryview-style', NSGV_DIR_URL . 'css/galleryview.css');
		wp_enqueue_style('jquery-galleryview-suppl-style', NSGV_DIR_URL . 'css/supplemental.css');
		
	}
	
	public function gallery ($attr) {
		
		global $post, $wp_locale;
		
		if ( is_feed() ) {
			
			return '';
			
		}

		static $instance = 0;
		
		$instance++;

		// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
		if ( isset( $attr['orderby'] ) ) {
			
			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
			
			if ( !$attr['orderby'] )
			
				unset( $attr['orderby'] );
		
		}

		extract(shortcode_atts(array(
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'id'         => $post->ID,
			'size'       => 'thumbnail',
			'include'    => '',
			'exclude'    => ''
		), $attr));

		$id = intval($id);
		if ( 'RAND' == $order )
			$orderby = 'none';

		if ( !empty($include) ) {
			$include = preg_replace( '/[^0-9,]+/', '', $include );
			$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		} elseif ( !empty($exclude) ) {
			$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
			$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
		} else {
			$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
		}

		if ( empty($attachments) )
			return '';

		$selector = "gallery-{$instance}";

		$output = '<div><ul id="' . $selector . '" class="filmstrip">';

		$i = 0;
		
		foreach ( $attachments as $id => $attachment ) {
			 
			$output .= "<li>" . wp_get_attachment_image($id, 'large', false, false) . "</li>";
			
		}

		$output .= "</ul></div><script>jQuery(function ($) {

			$('#$selector').galleryView({
				
				panel_width: 630,
				panel_height: 300,
				frame_width: 100,
				frame_height: 100,
				paused : true
			
			});
			
		}); </script>";

		return $output;
	}
	
}

?>