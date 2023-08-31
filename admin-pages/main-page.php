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
class SITE_STATS_MainAdminPage extends APL_AdminPage{
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
		wp_enqueue_script( 'site-stats-js', SITE_STATS_PLUGIN_URL.'/admin-pages/site-stats-table-toggle.js', array('jquery') );
    	wp_enqueue_style( 'sites-stats', SITE_STATS_PLUGIN_URL.'/admin-pages/style.css' );
	}
	
	
	/**
	 * Displays the current admin page.
	 */
	public function display(){
		$this->print_stats();
	}
	
	
	private function print_stats(){
		$stats = $this->model->get_site_stats();
		?>
		
		<h3>Themes</h3>
		<?php echo count($stats['theme']).' themes installed'; ?>
		<table>
		<thead>
		<tr><th class="item-name">Theme Name</th><th class="version">Version</th><th class="count">Active Sites</th><th class="count">Archived Sites</th></tr>
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
			
			echo '<tbody><tr>
			<td><label for="'.$theme_info['data']->Title.'">'.$theme_info['data']->Title.'</label>
				<input type="checkbox" name="'.$theme_info['data']->Title.'" id="'.$theme_info['data']->Title.'" data-toggle="toggle"></td>
			<td class="version">'.$theme_info['data']->Version .'</td>
			<td class="count">'.count($theme_info['sites']).'</td>
			<td class="count">'.count($theme_info['sites_archived']).'</td>
			</tr></tbody><tbody class="hidden">';
 			$sites = $theme_info['sites'];
			if ($sites){
				foreach( $sites as $site ) $this->print_site($site);
			}
			else {
				echo '<tr><td><div class="site-info">This theme is not active on any active sites</div></tr></td>';
			}
			echo "</tbody>";
		}
		?>
		</tbody>
		</table>
		<h3>Site Plugins</h3>
		<?php 
		$site_plugin_count = 0;
		$network_plugin_count = 0;
		foreach( $stats['plugin'] as $plugin => $plugin_info ){
			if(!is_plugin_active_for_network($plugin))$site_plugin_count++;
			elseif(is_plugin_active_for_network($plugin))$network_plugin_count++;
		}
		echo $site_plugin_count.' site plugins installed'; 
		?>
		<table>
		<thead>
		<tr><th class="item-name">Plugin Name</th><th class="version">Version</th><th class="count">Active Sites</th><th class="count">Archived Sites</th></tr>
		</thead>
		<tbody>
		<?php
		$i = 0;
		foreach( $stats['plugin'] as $plugin => $plugin_info )
		{
			if(!is_plugin_active_for_network($plugin)){
			$page_url = $this->get_page_url(
				array(
					'action' 	=> 'sites',
					'type' 		=> 'plugin',
					'name'		=> $plugin,
				)
			);
			$plugin_title = $plugin_info['data']['Title'];
			if(!$plugin_title){
				$plugin_title = "<i>Broken Plugin </i>".$i;
				$i++;
				echo '<tbody class="hide-broken">';
			}
			else {
				echo '<tbody>';
			}

			//Need to make sure things are countable before they are counted -XM Aug 2023
                        $count_plugin_info_sites = 0;
                        $count_plugin_info_sites_archived = 0;
                        if (is_countable($plugin_info['sites'])) { 
                                $count_plugin_info_sites = count($plugin_info['sites']);
                        }
                        if (is_countable($plugin_info['sites_archived'])) {
                                $count_plugin_info_sites_archived = count($plugin_info['sites_archived']);
                        }
				
			echo '<tr>
			<td><label for="'.$plugin_title.'">'.$plugin_title.'</label>
				<input type="checkbox" name="'.$plugin_title.'" id="'.$plugin_title.'" data-toggle="toggle"></td>
			<td class="version">'.$plugin_info['data']['Version'].'</td>
			<td class="count">'.$count_plugin_info_sites.'</td>
			<td class="count">'.$count_plugin_info_sites_archived.'</td>
			</tr></tbody><tbody class="hidden">';
 			$sites = $plugin_info['sites'];
			if ($sites){
				foreach( $sites as $site ) $this->print_site($site);
			}
			else {
				echo '<tr><td><div class="site-info">This plugin is not active on any active sites</div></tr></td>';
			}
			echo "</tbody>";
			}
		}
		?>
		</tbody>
		</table>
		<h3>Network Activated Plugins</h3>
		<?php echo $network_plugin_count.' network plugins activated'; ?>
		<table>
		<tbody>
		<?php
		foreach( $stats['plugin'] as $plugin => $plugin_info )
		{
			if(is_plugin_active_for_network($plugin)){
			echo '<tr>
			<td>'.$plugin_info['data']['Title'].'</td>
			</tr>';
			}
		}
		?>
		</tbody>
		</table>
		<?php
	}

	
	private function print_site(&$site)
	{
		$admin_url = $site['url'].'/wp-admin';
		
		?>
		<tr>
		<td colspan="4">
			<div class="site-info">
				<span class="title"><a href="<?php echo $site['url']; ?>" target="_blank"><?php echo $site['title'] ?: '<i>--No Title--</i>'; ?></a></span>
				<span class="admin-links">
				
				<a href="<?php echo 'site-info.php?id='.$site['id'] ?> " target="_blank">Network Admin</a>&nbsp;|&nbsp;
					<a href="<?php echo $admin_url; ?>" target="_blank">Admin</a>&nbsp;|&nbsp;
					<a href="<?php echo $admin_url.'/themes.php'; ?>" target="_blank">Themes</a>&nbsp;|&nbsp;
					<a href="<?php echo $admin_url.'/plugins.php'; ?>" target="_blank">Plugins</a>&nbsp;&nbsp;
				</span>
			</div>
		</td>
		</tr>
		<?php
	}
	
} // class TT_ThemeListAdminPage extends APL_AdminPage
endif; // if( !class_exists('TT_ThemeListAdminPage') )
