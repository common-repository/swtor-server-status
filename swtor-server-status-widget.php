<?php
/*
   Copyright 2011-present  Baraan@ForceHarvester <baraan@baraans-corner.de>

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

class SWTORServerStatusWidget extends WP_Widget{
	function SWTORServerStatusWidget() {
		$widget_ops = array('classname' => 'widget_swtor_server_status', 'description' => __('Shows the server status, like population and queue size.', 'swtor_server_status'));
	    $control_ops = array('width' => 300, 'height' => 300);
		$this->WP_Widget('swtor_server_status_guild', "SWTOR Server Status", $widget_ops, $control_ops);
	}

	function widget($args, $instance){
		extract($args, EXTR_SKIP);
		// before widget stuff
		echo $before_widget;

		echo "<div class='swtorss'>";
		
		// title
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };

		// widget content
		if(empty($instance['shard']) ||
		   empty($instance['region'])){
			_e('Please configure the widget settings in the widget screen.');
		}
		else{
			echo swtor_server_status_html($instance);
		}
		
		echo "</div>";
		
		// after widget stuff
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['shard'] = strip_tags($new_instance['shard']);
		$instance['region'] = strip_tags($new_instance['region']);
		$instance['show_last_update'] = strip_tags($new_instance['show_last_update']);

		if(!is_numeric($new_instance['cache_time']) ||
			intval($new_instance['cache_time']) < 0){
			$instance['cache_time'] = 60;
		}
		else{
			$instance['cache_time'] = intval($new_instance['cache_time']);
		}

		return $instance;
	}

	function form($instance) {
        $title = esc_attr($instance['title']);
        $shard = esc_attr($instance['shard']);
        $region = esc_attr($instance['region']);
        $cache_time = $instance['cache_time'];
		if(!is_numeric($cache_time)) $cache_time = 60;
		
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?><input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>

            <p><label for="<?php echo $this->get_field_id('shard'); ?>"><?php _e('Server:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('shard'); ?>" name="<?php echo $this->get_field_name('shard'); ?>" type="text" value="<?php echo $shard; ?>" /></label></p>
            
			<p><label for="<?php echo $this->get_field_id('region'); ?>"><?php _e('Region:'); ?>
				<select id="<?php echo $this->get_field_id('region'); ?>" name="<?php echo $this->get_field_name('region'); ?>" size="1">
					<option <?php selected('eu', $region); ?> value="eu"><?php _e('Europe') ?></option>
					<option <?php selected('us', $region); ?> value="us"><?php _e('US')?></option>
					<option <?php selected('ap', $region); ?> value="ap"><?php _e('Asia/Pacific')?></option>
				</select>
			</label></p>
			
            <p><label for="<?php echo $this->get_field_id('cache_time'); ?>"><?php _e('Cache time in seconds:', 'swtor_server_status'); ?><input class="widefat" id="<?php echo $this->get_field_id('cache_time'); ?>" name="<?php echo $this->get_field_name('cache_time'); ?>" type="text" value="<?php echo $cache_time; ?>" /></label></p>
			
			<p>
				<input type="checkbox" id="<?php echo $this->get_field_id('show_last_update'); ?>" name="<?php echo $this->get_field_name('show_last_update'); ?>" <?php if ($instance['show_last_update']) echo 'checked="checked"' ?> />
				<label for="<?php echo $this->get_field_id('show_last_update'); ?>"><?php _e('Show last updated?', 'swtor_server_status'); ?></label>
			</p>
        <?php 
	}
}


// register the widgets
add_action('widgets_init', create_function('', 'return register_widget("SWTORServerStatusWidget");'));
?>
