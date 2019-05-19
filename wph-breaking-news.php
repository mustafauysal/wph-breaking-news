<?php
/*
Plugin Name: WPH Breaking News
Plugin URI: https://wphandle.com
Description: Easy way to announce "Breaking News" on your WordPress site. 
Author: Mustafa Uysal
Version: 0.2
Text Domain: wph-breaking-news
Domain Path: /languages/
Author URI: https://uysalmustafa.com
License: GPLv2 (or later)
*/

require_once( dirname( __FILE__ ) . '/libs/wp-stack-plugin.php' );


class WPH_Breaking_News extends WP_Stack_Plugin {
	public static $instance;

	/**
	 * Prefix for the announcement
	 *
	 * @var string
	 */
	protected $announce_text;


	public function __construct() {
		self::$instance = $this;

		$this->announce_text = apply_filters( 'wph_breaking_news_announce_text', __( 'Breaking News', 'wph-breaking-news' ) );
		$this->hook( 'init' );
	}

	public function init() {

		load_plugin_textdomain( 'wph-breaking-news', false, basename( dirname( __FILE__ ) ) . '/languages' );

		$this->hook( 'add_meta_boxes' );
		$this->hook( 'the_title' );
		$this->hook( 'save_post' );

	}


	public function add_meta_boxes( $post_type ) {
		add_meta_box(
			'breaking_news_meta_box', __( 'Breaking News', 'wph-breaking-news' ), array(
			$this,
			'breaking_news_meta_box'
		), 'post', 'normal', 'high'
		);
	}


	public function breaking_news_meta_box( $post ) {
		$wph_breaking_news_status = esc_html( get_post_meta( $post->ID, 'wph_breaking_news_status', true ) );
		$wph_breaking_time        = esc_html( get_post_meta( $post->ID, 'wph_breaking_time', true ) );
		?>
		<table>
			<tr>
				<td><label><?php _e( 'Mark as breaking news', 'wph-breaking-news' ); ?>:</label></td>
				<td>
					<input type="radio" name="wph_breaking_news_status" <?php checked( $wph_breaking_news_status, 'on' ); ?> value="on" /> <?php _e( 'Enabled', 'wph-breaking-news' ); ?>
					<input type="radio" name="wph_breaking_news_status" <?php checked( $wph_breaking_news_status, 'off' ); ?> value="off" /> <?php _e( 'Disabled', 'wph-breaking-news' ); ?>
				</td>
			</tr>
			<tr>
				<td><label><?php _e( 'Time', 'wph-breaking-news' ); ?>:</label></td>
				<td>
					<select name="wph_breaking_time">
						<?php
						for ( $i = 1; $i < 24; $i ++ ) {
							echo '<option value="' . $i . '" ' . selected( $wph_breaking_time, $i ) . '>  ' . str_pad( $i, 2, 0, STR_PAD_LEFT ) . '</option>';
						}
						?>
					</select>
					<?php _e( 'hour(s) will display as "breaking news" then it will be turned off, you don\'t need to update again.', 'wph-breaking-news' ); ?>
				</td>
			</tr>
		</table>
		<?php
	}

	public function save_post( $post_id ) {
		if ( isset( $_POST['wph_breaking_news_status'] ) && $_POST['wph_breaking_news_status'] != '' ) {
			update_post_meta( $post_id, 'wph_breaking_news_status', $_POST['wph_breaking_news_status'] );
		}
		if ( isset( $_POST['wph_breaking_time'] ) && $_POST['wph_breaking_time'] != '0' ) {
			update_post_meta( $post_id, 'wph_breaking_time', $_POST['wph_breaking_time'] );
		}
	}

	public function allowed_post_types() {
		return apply_filters( 'wph_breaking_news_allowed_post_types', array( 'post' ) );
	}

	public function the_title( $title ) {
		global $id, $post;

		if ( $id && $post && in_array( $post->post_type, $this->allowed_post_types() ) ) {

			$wph_breaking_news_status = get_post_meta( $post->ID, 'wph_breaking_news_status', true );
			$wph_breaking_time        = get_post_meta( $post->ID, 'wph_breaking_time', true );


			$post_time    = $post->post_date;
			$expire_time  = strtotime( '+' . $wph_breaking_time . ' hour', strtotime( $post_time ) );
			$current_time = current_time( 'timestamp' );

			if ( $wph_breaking_news_status === 'on' && ( $expire_time > $current_time ) ) {
				$title = sprintf( "[%s] %s", $this->announce_text, $title );
			}

		}

		return $title;
	}

}

new WPH_Breaking_News;