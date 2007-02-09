<?php
/*
Plugin Name: Miniblog Widget
Plugin URI: http://blog.fileville.net/?page_id=121
Description: Adds a Sidebar Widget To Display Miniblog. <br /> Special thanks to <a href="http://www.lesterchan.net">GaMerZ</a> for creating the Widget.
Version: 1.0
Author: Joe
Author URI: http://blog.fileville.net
*/

/* Copyright 2006 Joe (ttech5593@gmail.com)
 
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


### Function: Init Mini Blog Widget
function widget_miniblog_init() {
	if (!function_exists('register_sidebar_widget')) {
		return;
	}

	### Function: Mini Blog Widget
	function widget_miniblog($args) {
		extract($args);
		$options = get_settings('widget_miniblog');
		$title = $options['title'];	
		$name = $options['name'];
		$date = $options['date'];
		$limit = intval($options['limit']);
		$site_name = get_settings('blogname');
		$site_des = get_settings('blogdescription');
		echo $before_widget.$before_title.$title.$after_title;
		// People who don't mind experementing you can edit "'<li>', '<br />', '</li>'"
		// First <li> = Before Post  <br />  Last (Closing <li>) </li> = Ending of post
		miniblog_list_entries('<li>', '<br />', '</li>', '', $options['limit']);
		echo $after_widget;	
		echo '<div class="miniblog">'."\n";
		//echo '<h4>'.$title.'</h4>'."\n";
	    $miniblog_array = miniblog_return_entries($limit, 0, $name, '_date');
		if($miniblog_array) {
			foreach($miniblog_array as $miniblog) {
				echo '<div class="post">'."\n";
				echo ' <b> <a href="'.__($miniblog->url).'">'.__($miniblog->title).'</a></b><br />'."\n";
				echo '<div class="meta">'."\n";
				echo 'Posted on '.__(date($date, strtotime($miniblog->date)))."\n";
				echo '</div>'."\n";
				echo '<div class="content">'."\n";
				_e($miniblog->text);
				echo '</div>'."\n";
				echo '</div>'."\n";
				echo '<hr />'."\n";
			}
		}
		echo '<table width="176" border="0">'."\n";
		echo '<tr>'."\n";
		echo '<th width="82" scope="col"><a href="'.__(miniblog_create_rss_url($limit, 0, $name, '_date', "$site_name's TITLE", "$site_name ($site_des) has asides. These are them.")).'">Miniblog RSS'."\n";
		echo '</a></th>'."\n";
		echo '<th width="80" scope="col"><a href="'.__(miniblog_create_archive_url($limit, 0, $name, '_date', $title.' Archives', '<li>', '<br />', '</li>')).'">Archives</a>'."\n";
		echo '</th>'."\n";
		echo '</tr>'."\n";
		echo '</table>'."\n";
		echo '</div> '."\n";
	}

	### Function: Mini Blog Widget Options
	function widget_miniblog_options() {
		global $wpdb;
		$options = get_settings('widget_miniblog');
		if (!is_array($options)) {
			$options = array('title' => 'Miniblog', 'name' => 'default', 'limit' => 10, 'date' => 'm/j/y');
		}
		if ($_POST['miniblog-submit']) {
			$options['title'] = trim($_POST['miniblog_title']);
			$options['name'] = trim($_POST['miniblog_name']);
			$options['date'] = trim($_POST['miniblog_date']);
			$options['limit'] = intval($_POST['miniblog_limit']);
			update_option('widget_miniblog', $options);
		}
		echo '<p style="text-align: left;"><label for="miniblog_title">Mini Blog Title:</label>&nbsp;&nbsp;&nbsp;<input type="text" id="miniblog_title" name="miniblog_title" value="'.$options['title'].'" size="30" /></p>'."\n";
		echo '<p style="text-align: left;"><label for="miniblog_name">Mini Blog Name:</label>&nbsp;&nbsp;&nbsp;<input type="text" id="miniblog_name" name="miniblog_name" value="'.$options['name'].'" size="30" /></p>'."\n";
		echo '<p style="text-align: left;"><label for="miniblog_date">Mini Blog Date Format:</label>&nbsp;&nbsp;&nbsp;<input type="text" id="miniblog_date" name="miniblog_date" value="'.$options['date'].'" size="2" maxlength="10" /></p>'."\n";
		echo '<p style="text-align: left;"><label for="miniblog_limit">Mini Blog Limit:</label>&nbsp;&nbsp;&nbsp;<input type="text" id="miniblog_limit" name="miniblog_limit" value="'.$options['limit'].'" size="2" maxlength="10" /></p>'."\n";
		echo '<input type="hidden" id="miniblog-submit" name="miniblog-submit" value="1" />'."\n";
		echo '<br /><br />'."\n";
	}

	// Register Widgets
	register_sidebar_widget('Mini Blog', 'widget_miniblog');
	register_widget_control('Mini Blog', 'widget_miniblog_options', 400, 200);
}


### Function: Load The Mini Blog Widget
add_action('plugins_loaded', 'widget_miniblog_init');
?>