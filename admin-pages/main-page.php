<?php

/**
 * SITE_STATS_MainAdminPage
 * 
 * This class controls the admin page "Sites Stats".
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('SITE_STATS_MainAdminPage') ):
class SITE_STATS_MainAdminPage extends APL_AdminPage
{
	
	private $model = null;	
	private $list_table = null;
	
	private $filter_types;
	private $filter;
	private $search;
	private $orderby;
	
	
	/**
	 * Creates an TT_ThemeListAdminPage object.
	 */
	public function __construct( 
		$name = 'sites-stats',
		$menu_title = 'Sites Stats',
		$page_title = 'Sites Stats',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
		$this->model = SITES_STATS_Model::get_instance();
	}
	
	
	/**
	 * Enqueues all the scripts or styles needed for the admin page. 
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_style( 'sites-stats', SITE_STATS_PLUGIN_URL.'/admin-pages/style.css' );
	}
	
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'sites' )
		{
			$this->print_site_list(); return;
		}
		
		$this->print_stats();
	}
	
	
	private function print_site_list()
	{
		$back_url = $this->get_page_url();
		
		?>
		<br/>
		<a href="<?php echo $back_url; ?>"><< Back</a>
		<?php
		
		if( !isset($_REQUEST['type']) )
		{
			echo 'Type is required when listing sites.';
			return;
		}
		$type = $_REQUEST['type'];
		
		if( !isset($_REQUEST['name']) )
		{
			echo 'Name is required when listing sites.';
			return;
		}
		$name = $_REQUEST['name'];
		
		
		if( $type != 'theme' && $type != 'plugin' )
		{
			echo 'Invalid Type: '.$type;
			return;
		}
		
		
		$stats = $this->model->get_site_stats();
		
		$title = $name;
		switch( $type )
		{
			case 'plugin':
				$data = @get_plugin_data( WP_PLUGIN_DIR.'/'.$name, false );
				if( !$data['Name'] ) $data = null;
				else $title = $data['Title'];
				break;
			case 'theme':
				$data = wp_get_theme( $name, false );
				if( !$data->exists() ) $data = null;
				else $title = $data->Title;
				break;
		}
		
		?>
		<h3><?php echo ucfirst( $type ); ?>: <?php echo $title; ?></h3>
		<?php
		
		if( !$data )
		{
			echo 'Not found.';
			return;
		}
		
		if( !array_key_exists($name, $stats[$type]) || (count($stats[$type][$name]['sites']) == 0 && count($stats[$type][$name]['sites_archived']) == 0))
		{
			echo ucfirst( $type ).' not activated on any sites.';
			return;
		}
		
		if(count($stats[$type][$name]['sites']) > 0){
			echo '<div><h4>Active Sites</h4>';
			foreach( $stats[$type][$name]['sites'] as $site )
			{
				$this->print_site( $site, $type );
			}
			echo '</div><br>';
		}
		if(count($stats[$type][$name]['sites_archived']) > 0){
			echo '<div><h4>Archived Sites</h4>';
			foreach( $stats[$type][$name]['sites_archived'] as $site )
			{
				$this->print_site( $site, $type );
			}
			echo '</div>';
		}
	}
	
	
	
	
	private function print_stats()
	{
		$stats = $this->model->get_site_stats();
		?>
		
		<h3>Themes</h3>
		<?php echo count($stats['theme']).' themes installed'; ?>
		<table>
		<thead>
		<tr><th>Theme Name</th><th>Active Sites</th><th>Archived Sites</th></tr>
		</thead>
		<tbody>
		<?php
		foreach( $stats['theme'] as $theme => $theme_info )
		{
			$page_url = $this->get_page_url(
				array(
					'action' 	=> 'sites',
					'type' 		=> 'theme',
					'name'		=> $theme,
				)
			);
			
			echo '<tr>
			<td><a href="'.$page_url.'">'.$theme_info['data']->Title.'</a> v.'.$theme_info['data']->Version .'</td>
			<td class="count">'.count($theme_info['sites']).'</td>
			<td class="count">'.count($theme_info['sites_archived']).'</td>
			</tr>';
// 			foreach( $sites as $site )
// 			{
// 				$this->print_site( $site, 'theme' );
// 			}
		}
		?>
		</tbody>
		</table>
		<h3>Plugins</h3>
		<?php echo count($stats['plugin']).' plugins installed'; ?>
		<table>
		<thead>
		<tr><th>Plugin Name</th><th>Active Sites</th><th>Archived Sites</th></tr>
		</thead>
		<tbody>
		<?php
		foreach( $stats['plugin'] as $plugin => $plugin_info )
		{
			$page_url = $this->get_page_url(
				array(
					'action' 	=> 'sites',
					'type' 		=> 'plugin',
					'name'		=> $plugin,
				)
			);
			echo '<tr>
			<td><a href="'.$page_url.'">'.$plugin_info['data']['Title'].'</a> v.'.$plugin_info['data']['Version'] .'</td>
			<td class="count">'.count($plugin_info['sites']).'</td>
			<td class="count">'.count($plugin_info['sites_archived']).'</td>
			</tr>';
// 			foreach( $sites as $site )
// 			{
// 				$this->print_site( $site, 'plugin' );
// 			}
		}
		?>
		</tbody>
		</table>
		<?php
	}
	
	
	
	
	private function print_site( &$site, $type )
	{
		$admin_url = $site['url'].'/wp-admin';
// 		switch( $type )
// 		{
// 			case 'theme':
// 				$admin_url .= '/themes.php';
// 				break;
// 			
// 			case 'plugin':
// 				$admin_url .= '/plugins.php';
// 				break;
// 		}
		?>
		
		<div class="site-info">
		
			<span class="title"><a href="<?php echo $site['url']; ?>" target="_blank"><?php echo $site['title'] ?: '<i>--No Title--</i>'; ?></a></span>
			<span class="admin-links">
			
			<a href="<?php echo 'site-info.php?id='.$site['id'] ?> " target="_blank">Network Admin</a>&nbsp;&nbsp;
				<a href="<?php echo $admin_url; ?>" target="_blank">Admin</a>&nbsp;&nbsp;
				<a href="<?php echo $admin_url.'/themes.php'; ?>" target="_blank">Themes</a>&nbsp;&nbsp;
				<a href="<?php echo $admin_url.'/plugins.php'; ?>" target="_blank">Plugins</a>&nbsp;&nbsp;
			</span>
		
		</div>
		
		<?php
	}
	
} // class TT_ThemeListAdminPage extends APL_AdminPage
endif; // if( !class_exists('TT_ThemeListAdminPage') )

