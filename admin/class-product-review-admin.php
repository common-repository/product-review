<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://nazmulahsan.me
 * @since      1.0.0
 * @package    Product_Review
 * @subpackage Product_Review/admin
 * @author     Nazmul Ahsan <mail@nazmulahsan.me>
 */
class Product_Review_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->load_dependencies();

	}

	private function load_dependencies() {
		require_once plugin_dir_path( CB_PRODUCT_REVIEW ) . 'includes/meta-box.php';
		require_once plugin_dir_path( CB_PRODUCT_REVIEW ) . 'includes/class-product-review-options.php';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if( cbpr_load_scripts() ) :
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/product-review-admin.css', array(), $this->version, 'all' );
		endif;

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if( cbpr_load_scripts() ) :
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/product-review-admin.js', array( 'jquery' ), $this->version, false );
		endif;

	}

	/**
	 * Redirect to option page after activation. First time only.
	 */
	public function activated_redirect() {
	    if ( get_option( 'cbpr_activated', false ) ) {
	        update_option( 'cbpr_activated', false );
	        wp_redirect( admin_url( 'admin.php?page=product-review' ) );
	    }
	}

	/**
	 * Adds a column in posts management screen to show average rating
	 *
	 */
	public function add_posts_columns() {
		$cbpr_post_types = cbpr_post_types();
		foreach ( $cbpr_post_types as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns" , array( $this, 'average_rating_column' ) );
			add_action( "manage_{$post_type}_posts_custom_column" , array( $this, 'average_rating_column_content' ), 10, 2 );
			add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'average_rating_column' ) );
		}
	}

	/**
	 * Callback function for average rating column header
	 *
	 * @param array $columns
	 * @return array $columns
	 */
	public function average_rating_column( $columns ) {
		$columns['average_rating'] = __( 'Average rating', 'product-review' );
	    return $columns;
	}

	/**
	 * Callback function for average rating column content
	 *
	 * @param string $column column id
	 * @param int $post_id post ID
	 */
	public function average_rating_column_content( $column, $post_id ) {
		if ( $column == 'average_rating' ){
			if( 'on' != cbpr_meta( 'cbpr_enable_rating', $post_id ) ){
				_e( 'Not rated', 'product-review' );
			}
			else{
				echo round( cbpr_average_rating( $post_id ), 2 );
			}
		}
	}

	/**
	 * Show admin notices
	 *
	 * @since 1.2.4
	 */
	public function admin_notices() {
		if( get_option( 'cbpr_survey' ) != 1 ) :
		?>
		<div class="notice notice-success is-dismissible survey-notice">
			<p>
				<strong><?php _e( 'Help us improve your experience: ', 'eschool' ); ?></strong>
				<span><?php _e( 'We want to know what type of sites use our plugin. So that we can improve it accordingly. Help us with your site URL and few basic information. It doesn\'t include your password or any kind of sercret data. Would you like to help us?', 'eschool' ); ?></span>
			</p>
			<p>
				<button class="button button-primary cbpr-survey" data-participate="1"><?php _e( 'Okay, don\'t bother me again!', 'eschool' ); ?></button>
			</p>
		</div>
		<script>
			$ = new jQuery.noConflict()
			$(document).ready(function(){
			    $(document).on('click', '.is-dismissible.survey-notice .notice-dismiss, .cbpr-survey', function(e){
			    	$(this).prop('disabled', true)
			        $.ajax({
			            url: ajaxurl,
			            data: { 'action' : 'survey', 'participate' : $(this).data('participate') },
			            type: 'POST',
			            success: function(ret) {
			                $('.survey-notice').slideToggle(500)
			            }
			        })
			    })
			})
		</script>
		<?php
		endif;
	}
	
	/**
	 * Register a widget that shows product reviews
	 *
	 * @uses register_widget()
	 */
	public function widget() {
		register_widget( 'Product_Review_Widget' );
	}
}
