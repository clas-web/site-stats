<?php
/*
Plugin Name: Site Stats
Plugin URI: 
Description: 
Version: 1.2.3
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
Network: True
GitHub Plugin URI: https://github.com/clas-web/site-stats
*/


if( !defined('SITE_STATS') ):

define( 'SITE_STATS', 'Site Stats' );

define( 'SITE_STATS_DEBUG', true );

define( 'SITE_STATS_PLUGIN_PATH', dirname(__FILE__) );
define( 'SITE_STATS_PLUGIN_URL', plugins_url('', __FILE__) );

define( 'SITE_STATS_VERSION', '0.0.1' );

endif;


if( is_admin() ):

add_action( 'wp_loaded', array('SITE_STATS_Main', 'load') );

endif;


if( !class_exists('SITE_STATS_Main') ):
class SITE_STATS_Main
{
	
	public static function load()
	{
		require_once( dirname(__FILE__).'/admin-pages/require.php' );
		
		$pages = new APL_Handler( true );
		$pages->add_page( new SITE_STATS_MainAdminPage );
		$pages->setup();
	}

}
endif;

