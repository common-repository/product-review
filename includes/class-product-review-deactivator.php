<?php

/**
 * Fired during plugin deactivation.
 *
 * @link       http://nazmulahsan.me
 * @since      1.0.0
 * @package    Product_Review
 * @subpackage Product_Review/includes
 * @author     Nazmul Ahsan <mail@nazmulahsan.me>
 */
class Product_Review_Deactivator {

	/**
	 * plugin is just deactivated
	 */
	public static function deactivate() {
		do_action( 'cbpr_deactivated' );

		if( get_option( 'cbpr_survey_agreed' ) == 1 ) :
		
		$base_url = 'https://codebanyan.com';

		$params = array(
			'item'			=> 'product-review',
			'siteurl'		=> explode( '://', get_option( 'siteurl' ) )[1],
			'is_active'		=> 0,
		);

		$endpoint = add_query_arg( $params, $base_url );
		@file_get_contents( $endpoint );
		
		endif;
	}

}
