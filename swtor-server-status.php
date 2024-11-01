<?php
  /*
   Plugin Name: SWTOR Server Status
   Plugin URI: http://www.baraans-corner.de/wordpress-plugins/swtor-server-status/
   Description: Shows the server status, population and type (PvP, PvE, RP) of a server from the MMORPG SWTOR (Star Wars - The Old Republic).
   Version: 0.3.1
   Author: Baraan@ForceHarvester <baraan@baraans-corner.de>
   Author URI: http://www.baraans-corner.de/
   Text Domain: swtor_server_status
   Domain Path: i18n/

   Copyright 2011-present Baraan@ForceHarvester <baraan@baraans-corner.de>

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
  */

// to parse HTML
require_once('simple_html_dom.php');
// The widget code
require_once('swtor-server-status-widget.php');

/**
 * Returns html server status.
 */
function swtor_server_status_html($instance){
	// check the cache dir
	$cache_dir = ABSPATH . "wp-content/plugins/swtor-server-status/cache";
	if(!is_writable($cache_dir)){
		return __("Cache directory not writable. Please make sure wordpress can write the cache directory and the files within.", 'swtor_server_status');
	}

	
	// some settings
	$shard = strtolower($instance['shard']);
	$cache_time = $instance['cache_time'];
	$show_last_update = $instance['show_last_update'];
	$region = strtolower($instance['region']);
	$cache_file = "$cache_dir/server-status.html";
	$url = "http://www.swtor.com/server-status";
	$last_cache_update = time()-filemtime($cache_file);
	$data = new swtor_server_status_simple_html_dom();

	// load the data, either directly or from cache
	if( !(file_exists($cache_file) && filesize($cache_file) > 20000 && $last_cache_update < $cache_time) ){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		$str = curl_exec($curl);
		curl_close($curl);

		if(strlen($str) > 20000){
			$data->load($str);
			$data->save($cache_file);
			$last_cache_update = 0;
		}
		else{
			// string too short, most probably not the data we want.
			// try the cache, even though it might be older.
			if(file_exists($cache_file) && filesize($cache_file) > 20000){
				$data->load_file($cache_file);
			}
			else{
				return "Armory not reachable or guild not found. Tried to fetch <a href='$url'>this Link</a>. Armory might be blocking, please wait and try to increase cache time.";
			}
		}
	}
	else{
		// cache hit
		$data->load_file($cache_file);
	}
	$html .= "<!-- SWTOR Server Status <http://www.baraans-corner.de/wordpress-plugins/swtor-server-status/> -->";
        if (get_option('swtor_server_status_morecss') == true) {
		$html .= "<div class='swtorss_content swtorss_morecss'>";
	}
	else{
		$html .= "<div class='swtorss_content'>";
	}
	
	if($region == "us"){
//		$server_data = $data->getElementsById("serverList", 0);
		$server_data = $data->find("div[class=serverList]", 0);
	}
	else if($region == "eu"){
//		$server_data = $data->getElementsById("serverList", 1);
		$server_data = $data->find("div[class=serverList]", 1);
	}
	else if($region == "ap"){
		$server_data = $data->find("div[class=serverList]", 2);
	}
	else{
		// TODO
		die("Error, couldn't parse the data");
	}
	// <div class="serverBody row even" data-status="UP" data-name="hidden beks" data-population="2" data-type="PvE" data-language="English">
	$status = $server_data->find("div[data-name=$shard]", 0);
	
	if($status){
		$name = $instance['shard'];
		$lang = $status->attr['data-language'];
		$pop = $status->attr['data-population'];
		$pop_text = $status->find("div[class*=population]", 0)->innertext;
		$online = $status->attr['data-status'] == "UP" ? true : false;
		$pvep = stripos($status->attr['data-type'], "PvP") === false ? false : true;
		$rp = stripos($status->attr['data-type'], "RP") === false ? false : true;
		
		$name = "<span class='swtorss_name swtorss_server_". ($online?__("online", 'swtor_server_status'):__("offline", 'swtor_server_status')) ."'>".$name."</span>";
		$online_text = ($online?"<span class='swtorss_online'>". __("online", 'swtor_server_status'):"<span class='swtorss_offline'>". __("offline", 'swtor_server_status'))."</span>";
		$pvep_text = ($pvep?"<span class='swtorss_pvp'>". __("PvP", 'swtor_server_status'):"<span class='swtorss_pve'>PvE")."</span>";
		$rp_text = $rp?"/<span class='swtorss_rp'>RP</span>":"";
		$lang_text = "<span class='swtorss_lang'>".$lang."</span>";
		/* translators: population as we get it from SWTOR can be Light, Standard, Heavy, Very Heavy or Full */
		$population = " ". __("Population is", 'swtor_server_status') ." <span class='swtorss_server_pop_". $pop ."'>". __(trim($pop_text), 'swtor_server_status')."</span>.";

		if($region == "eu" || $region == "ap"){
			/* translators: i.e.: Force Harvester (PvE, German) is online */
			$html .= "<div class='swtorss_main'>". sprintf(__('%1$s is %2$s.', 'swtor_server_status'), "$name ($pvep_text$rp_text, $lang_text)", $online_text);
		}
		else{
			/* translators: i.e.: iHanharr (PvP/RP) is online */
			$html .= "<div class='swtorss_main'>". sprintf(__('%1$s is %2$s.', 'swtor_server_status'), "$name ($pvep_text$rp_text)", $online_text);
		}
		if ($online){$html .= " " . $population;}
		$html .= "</div>";

		if($show_last_update){
			$html .= "<span class='swtorss_last_updated'>";
			if($last_cache_update < 60){
				$html .= "(". __('Just updated', 'swtor_server_status') .")";
			}
			else if($last_cache_update < 120){
				$html .= "(". __('Last updated about a minute ago', 'swtor_server_status') .")";
			}
			else{
				//$html .= "(". __('Last updated about ". floor($last_cache_update/60) ." minutes ago)";
				$html .= "(". sprintf(__('Last updated about %1$s minutes ago', 'swtor_server_status'), floor($last_cache_update/60)) .")";
			}
			$html .= "</span>";
		}
	}
	else{
		//$html .= sprintf(__('server "%1$s" couldn\'t be found or the server wasn\'t reachable.', 'swtor_server_status'), $server);
	}
		
	$html .= "</div>";
	return $html;
}

/**
 * If the user has the (default) setting of using the SWTOR Server Status CSS, load it.
 */
function swtor_server_status_css() {
	if (get_option('swtor_server_status_css') == true) {
		wp_enqueue_style('swtor_server_status_css', WP_CONTENT_URL.'/plugins/swtor-server-status/swtor-server-status.css');
	}
}
add_action('wp_print_styles', 'swtor_server_status_css');

/**
 * Set the default settings on activation on the plugin.
 */
function swtor_server_status_activation_hook() {
	return swtor_server_status_restore_config(false);
}
register_activation_hook(__FILE__, 'swtor_server_status_activation_hook');


/**
 * Add the SWTOR Server Status menu to the Settings menu
 */
function swtor_server_status_restore_config($force=false) {
	if($force || (get_option('swtor_server_status_css', "NOTSET") == "NOTSET")){
		update_option('swtor_server_status_css', true);
		update_option('swtor_server_status_morecss', true);
	}
}

/**
 * Add the SWTOR Server Status menu to the Settings menu
 */
function swtor_server_status_admin_menu() {
	add_options_page('SWTOR Server Status', 'SWTOR Server Status', 8, 'swtor_server_status', 'swtor_server_status_submenu');
}
add_action('admin_menu', 'swtor_server_status_admin_menu');

/**
 * Displays the SWTOR Server Status admin menu
 */
function swtor_server_status_submenu() {
	// check if the cache dir is writable and complain if not.
	$cache_dir = ABSPATH . "wp-content/plugins/swtor-server-status/cache";
	if(!is_writable($cache_dir)){
		swtor_server_status_message(sprintf(__('Cache dir (%1$s) not writable. Please make sure wordpress can write into the cache directory for the plugin to work.', 'swtor_server_status'), $cache_dir));
	}

	// restore the default config
	if (isset($_REQUEST['restore']) && $_REQUEST['restore']) {
		check_admin_referer('swtor_server_status_config');
		swtor_server_status_restore_config(true);
		swtor_server_status_message(__("Restored all settings to defaults.", 'swtor_server_status') ."<a href=''>". __("Back", 'swtor_server_status') ."</a>");
	}
	// saves the settings from the page
	else if (isset($_REQUEST['save']) && $_REQUEST['save']) {
		check_admin_referer('swtor_server_status_config');
		$error = "";

		// save the different settings
		// boolean values
		foreach ( array('css', 'morecss') as $val ) {
			if ( isset($_POST[$val]) && $_POST[$val] )
				update_option('swtor_server_status_'.$val,true);
			else
				update_option('swtor_server_status_'.$val,false);
		}

		// done saving
		if($error){
			$error = __("Some settings couldn't be saved. More details in the error message below:", 'swtor_server_status') ."<br />". $error;
			swtor_server_status_message($error);
		}
		else{
			swtor_server_status_message(__("Changes saved.", 'swtor_server_status') ."<a href=''>". __("Back", 'swtor_server_status') ."</a>");
		}
	}
	else {
	/**
	 * Display options.
	 */
	?>
	<form action="<?php echo attribute_escape( $_SERVER['REQUEST_URI'] ); ?>" method="post">
	<?php
		if ( function_exists('wp_nonce_field') )
			 wp_nonce_field('swtor_server_status_config');
	?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e("SWTOR Server Status Options", 'swtor_server_status'); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row" valign="top">
						<?php _e("Include general CSS", "swtor_server_status"); ?>
					</th>
					<td>
						<?php _e("If checked the CSS included with the addon will be used. In case you want to modify the design deactivate this option and copy the contents of swtor-server-status.css into your own stylesheet to prevent them from being overwritten by updates of the plugin.", 'swtor_server_status'); ?><br/>
						<input type="checkbox" name="css" <?php checked( get_option('swtor_server_status_css'), true ) ; ?> />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<?php _e("Activate more CSS", "swtor_server_status"); ?>
					</th>
					<td>
						<?php _e("If checked a nicer theme will be applied, but it might look out of place in some themes.", 'swtor_server_status'); ?><br/>
						<input type="checkbox" name="morecss" <?php checked( get_option('swtor_server_status_morecss'), true ) ; ?> />
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<span class="submit"><input name="save" value="<?php _e("Save Changes", 'swtor_server_status'); ?>" type="submit" /></span>
						<span class="submit"><input name="restore" value="<?php _e("Restore Built-in Defaults", 'swtor_server_status'); ?>" type="submit"/></span>
					</td>
				</tr>
			</table>
		</div>
	</form>
<?php
	}
}


/**
 * Add a settings link to the plugins page, so people can go straight from the plugin page to the
 * settings page.
 */
function swtor_server_status_filter_plugin_actions( $links, $file ){
	// Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=swtor_server_status">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}
add_filter( 'plugin_action_links', 'swtor_server_status_filter_plugin_actions', 10, 2 );

/**
 * Update message, used in the admin panel to show messages to users.
 */
function swtor_server_status_message($message) {
	echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>\n";
}

function swtor_server_status_init(){
//	$i18n_dir = 'swtor-server-status/i18n/';
//	load_plugin_textdomain('swtor_server_status', false, $i18n_dir);
}
add_action('init', 'swtor_server_status_init');

?>
