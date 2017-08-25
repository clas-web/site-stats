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
	public function get_sites($archived)
	{
		$sites = get_sites( array( 'number' => 99999, 'archived' => $archived) );
		$allsites = [];
		
		foreach( $sites as &$site )
		{
			$single_site = [];
			switch_to_blog( $site->blog_id );
			
			$single_site['id'] = get_current_blog_id();
			$single_site ['url'] = get_bloginfo( 'url' );
			$single_site ['title'] = get_bloginfo( 'name' );
			
			$single_site ['theme'] = get_option('stylesheet');
			$single_site ['plugins'] = get_option('active_plugins');
			$allsites[$site->blog_id] = $single_site ;
			restore_current_blog();
		}
		
		return $allsites;
	}
	
	
	/**
	 *
	 */
	public function get_site_stats()
	{
		$sites = $this->get_sites(0);
		$sites_archived = $this->get_sites(1);
		$stats = array(
			'theme'		=> array(),
			'theme_archived' => array (),
			'plugin'	=> array(),
			'plugin_archived'	=> array()
		);
		
		$themes = wp_get_themes();
		foreach( $themes as $theme )
		{
			$s = $theme->get_stylesheet();
			$stats['theme'][$s] = array();
			$stats['theme'][$s]['data'] = $theme;
			$stats['theme'][$s]['sites'] = array();
			$stats['theme'][$s]['sites_archived'] = array();
		}
		
		$plugins = get_plugins();
		foreach( $plugins as $p => $plugin )
		{
			$stats['plugin'][$p] = array();
			$stats['plugin'][$p]['data'] = $plugin;
			$stats['plugin'][$p]['sites'] = array();
			$stats['plugin'][$p]['sites_archived'] = array();
		}
		
		
		foreach( $sites as $site )
		{
			
			$theme = $site['theme'];
			if( !$theme ) continue;
			
			if( !array_key_exists($theme, $stats['theme']) )
			{
				$stats['theme'][$theme] = array();
				$stats['theme'][$theme]['data'] = wp_get_theme($theme);
				$stats['theme'][$theme]['sites'] = array();
			}
			$stats['theme'][$theme]['sites'][] = $site;
			
			//Echo out the plugins that show as active in the db, but don't exist
			echo '<div style="display:none">';
			foreach( $site['plugins'] as $plugin )
			{
				if( !array_key_exists($plugin, $stats['plugin']) )
				{
					$stats['plugin'][$plugin] = array();
					
					echo $site['url'];
					echo '<br>'.$plugin.'<br><br>';
					$stats['plugin'][$plugin]['data'] = @get_plugin_data( WP_PLUGIN_DIR.'/'.$plugin, false );
					$stats['plugin'][$plugin]['data']['Title'] = $stats['plugin'][$plugin]['data']['Name'];
					$stats['plugin'][$plugin]['sites'] = array();
				}
				$stats['plugin'][$plugin]['sites'][] = $site;
			}
			echo '</div>';

			
		}
		
		foreach( $sites_archived as $site_archived )
		{
			
			$theme = $site_archived['theme'];
			if( !$theme ) continue;
			
			if( !array_key_exists($theme, $stats['theme']) )
			{
				$stats['theme'][$theme] = array();
				$stats['theme'][$theme]['data'] = wp_get_theme($theme);
				$stats['theme'][$theme]['sites_archived'] = array();
			}
			$stats['theme'][$theme]['sites_archived'][] = $site_archived;
			
			foreach( $site_archived['plugins'] as $plugin )
			{
				if( !array_key_exists($plugin, $stats['plugin']) )
				{
					$stats['plugin'][$plugin] = array();
					$stats['plugin'][$plugin]['data'] = @get_plugin_data( WP_PLUGIN_DIR.'/'.$plugin, false );
					$stats['plugin'][$plugin]['sites_archived'] = array();
				}
				$stats['plugin'][$plugin]['sites_archived'][] = $site_archived;
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

	
