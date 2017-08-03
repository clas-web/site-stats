<?php
/**
 * SITES_STATS_Model
 * 
 * The main model for the Sites Stats plugin.
 * 
 * @package    sites-stats
 * @subpackage classes
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('SITES_STATS_Model') ):
class SITES_STATS_Model
{

	private static $instance = null;	// The only instance of this class.
	
	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 * Creates an OrgHub_SitesModel object.
	 */
	protected function __construct()
	{
		global $wpdb;
	}


	/**
	 * Get the only instance of this class.
	 * @return  OrgHub_SitesModel  A singleton instance of the sites model class.
	 */
	public static function get_instance()
	{
		if( self::$instance	=== null )
		{
			self::$instance = new SITES_STATS_Model();
		}
		return self::$instance;
	}


	/**
	 * 
	 */
	public function get_sites()
	{
		$sites = wp_get_sites( array( 'limit' => 99999 ) );
		
		foreach( $sites as &$site )
		{
			switch_to_blog( $site['blog_id'] );
			
			$site['url'] = get_bloginfo( 'url' );
			$site['title'] = get_bloginfo( 'name' );
			
			$site['theme'] = get_option('stylesheet');
			$site['plugins'] = get_option('active_plugins');
			
			restore_current_blog();
		}
		
		return $sites;
	}
	
	
	/**
	 *
	 */
	public function get_site_stats()
	{
		$sites = $this->get_sites();
		
		$stats = array(
			'theme'		=> array(),
			'plugin'	=> array(),
		);
		
		$themes = wp_get_themes();
		foreach( $themes as $theme )
		{
			$s = $theme->get_stylesheet();
			$stats['theme'][$s] = array();
			$stats['theme'][$s]['data'] = $theme;
			$stats['theme'][$s]['sites'] = array();
		}
		
		$plugins = get_plugins();
		foreach( $plugins as $p => $plugin )
		{
			$stats['plugin'][$p] = array();
			$stats['plugin'][$p]['data'] = $plugin;
			$stats['plugin'][$p]['sites'] = array();
		}
		
		
		foreach( $sites as $site )
		{
			
			$theme = $site['theme'];
			if( !$theme ) continue;
			
			if( !array_key_exists($theme, $stats['theme']) )
			{
				$stats['theme'][$theme] = array();
				$stats['theme'][$theme]['data'] = wp_get_theme( $theme, false );
				$stats['theme'][$theme]['sites'] = array();
			}
			$stats['theme'][$theme]['sites'][] = $site;
			
			foreach( $site['plugins'] as $plugin )
			{
				if( !array_key_exists($plugin, $stats['plugin']) )
				{
					$stats['plugin'][$plugin] = array();
					$stats['plugin'][$plugin]['data'] = @get_plugin_data( WP_PLUGIN_DIR.'/'.$plugin, false );
					$stats['plugin'][$plugin]['sites'] = array();
				}
				$stats['plugin'][$plugin]['sites'][] = $site;
			}

			
		}
		
		uasort( $stats['theme'], array($this, 'sort_themes') );
		uasort( $stats['plugin'], array($this, 'sort_plugins') );
		
		return $stats;
	}
	
	
	public function sort_themes( $a, $b )
	{
		return $a['data']->Title > $b['data']->Title;
	}
	
	public function sort_plugins( $a, $b )
	{
		return $a['data']['Title'] > $b['data']['Title'];
	}
	
}
endif;

	