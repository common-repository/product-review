<?php

/**
 * Fired during plugin activation.
 *
 * @link       http://nazmulahsan.me
 * @since      1.0.0
 * @package    Product_Review
 * @subpackage Product_Review/includes
 * @author     Nazmul Ahsan <mail@nazmulahsan.me>
 */
class Product_Review_Activator {

	/**
	 * plugin is just activated
	 * @since    1.0.0
	 */
	public static function activate() {
		do_action( 'cbpr_activated' );
		add_option( 'cbpr_activated', true );

		if( get_option( 'cbpr_survey_agreed' ) == 1 ) :
		
		$base_url = 'https://codebanyan.com';

		$params = array(
			'item'			=> 'product-review',
			'siteurl'		=> explode( '://', get_option( 'siteurl' ) )[1],
			'is_active'		=> 1,
		);

		$endpoint = add_query_arg( $params, $base_url );
		@file_get_contents( $endpoint );
		
		endif;
	}

}
