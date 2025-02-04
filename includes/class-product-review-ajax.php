<?php

/**
 * AJAX activities.
 *
 * @link       http://nazmulahsan.me
 * @since      1.0.0
 * @package    Product_Review
 * @subpackage Product_Review/includes
 * @author     Nazmul Ahsan <mail@nazmulahsan.me>
 */
class Product_Review_AJAX {

	/**
	 * The plugin instance
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public static $_instance;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_license-activator', array( $this, 'verify' ) );
		add_action( 'wp_ajax_add-on-updater', array( $this, 'update' ) );
		add_action( 'wp_ajax_survey', array( $this, 'survey' ) );
	}

	/**
	 * @since 1.1.1
	 */
	public function verify() {
		$license_key = $_POST['key'];
        if ( isset( $_REQUEST['operation'] ) ) {
            $api_params = array(
                'slm_action' => ( $_REQUEST['operation'] != 'deactivate_license' ) ? 'slm_activate' : 'slm_deactivate',
                'secret_key' => CB_SECRET_KEY,
                'license_key' => $license_key,
                'registered_domain' => $_SERVER['SERVER_NAME'],
                'item_reference' => urlencode( CB_ITEM_REFERENCE ),
            );

            $query = esc_url_raw( add_query_arg( $api_params, CB_LICENSE_SERVER_URL ) );
            $response = wp_remote_get( $query, array( 'timeout' => 20, 'sslverify' => false ) );

            if ( is_wp_error( $response ) ){
                echo "Unexpected Error! Please try again or contact us.";
            }

            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
            if( $license_data->result == 'success' ) {
                echo '<strong style="color:#07811a">' . $license_data->message . '</strong>';
                update_option( $_REQUEST['plugin'], ( $_REQUEST['operation'] == 'deactivate_license' ) ? '' : $license_key ); 
            }
            else{
                echo '<strong style="color:#C8080E">' . $license_data->message . '</strong>';
            }

            wp_die();
        }
	}

	/**
	 * Update an add-on
 	 * @since 1.2.0
	 */
	public function update() {
		$add_on = $_POST['add_on'];
		if( ! get_option( $add_on ) ) {
			wp_die( '<span style="color:#f00">&times; Please activate your license first!<span>' );
		}
		else{
			$plugin_slug = str_replace( '.php', '', $add_on );
			$remote_url = cbpr_dl( $plugin_slug );

			// if fails to get remote url
			if( $remote_url == '0' ) {
				wp_die( '<span style="color:#f00">&times; Please activate your license first!<span>' );
			}

			$plugins_path = WP_CONTENT_DIR . '/plugins/';
			// copy a zip
			$zip = $plugins_path . $plugin_slug . '.zip';
			$plugin_data = file_get_contents( $remote_url );
			file_put_contents( $zip, $plugin_data );
			// // delete existing
			$old_plugin = $plugins_path . $plugin_slug;
			// cbpr_delete_dir( $old_plugin );
			// unzip new zip file
			WP_Filesystem();
			$unzipfile = unzip_file( $zip, $plugins_path );
			unlink( $zip );
			if ( $unzipfile ) {
				wp_die( '<span style="color:#025E10">&#10003; Successfully updated!</span>' );
			} else {
				wp_die( '<span style="color:#f00">&times; There was an error!</span>' );
			}
			// delete zip
		}
	}

	/**
	 * Gather user data
	 *
	 * @since 1.2.4
	 */
	public function survey() {
		if( isset( $_POST['participate'] ) && $_POST['participate'] == 1 ) {
			$base_url = 'https://codebanyan.com';

			$params = array(
				'init'			=> 1,
				'item'			=> 'product-review',
				'siteurl'		=> explode( '://', get_option( 'siteurl' ) )[1],
				'admin_email'	=> get_option( 'admin_email' ),
			);

			echo $endpoint = add_query_arg( $params, $base_url );
			file_get_contents( $endpoint );
			update_option( 'cbpr_survey_agreed', 1 );
		}

		update_option( 'cbpr_survey', 1 );
		wp_die();
	}

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Instantiate the plugin
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}

Product_Review_AJAX::instance();