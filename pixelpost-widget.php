<?php    
/*  
    Plugin Name: Pixelpost Widget  
    Plugin URI: http://wordpress.org/extend/plugins/pixelpost-widget  
    Description: Plugin for displaying random thumbnails from a pixelpost database  
    Author: Daniel Freedman
    Version: 0.4
    Author URI: http://www.danielfreedman.co.uk  
*/  

/*  Copyright 2009  Daniel Freedman  (email : daniel.freedman@gmail.com)

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
class Pixelpost_Widget extends WP_Widget {
	function Pixelpost_Widget() {
		// widget actual processes
		parent::WP_Widget(false, $name = 'Pixelpost_Widget');
	}

	function widget($args, $instance) {
		extract($args);
		require "includes/pixelpost.php";

		// connect to the PixelPost database
		$ppdb = new wpdb($pixelpost_db_user,$pixelpost_db_pass, $pixelpost_db_pixelpost, $pixelpost_db_host);

		//Get random image(s)
		$numthum = empty($instance['pixelpost_widget_numthum']) ? 9 : $instance['pixelpost_widget_numthum'];
		
		$random = $ppdb->get_results("SELECT id, image, datetime FROM " . $pixelpost_db_prefix . "pixelpost where datetime <now() ORDER BY RAND( ) LIMIT " . $numthum );
		
		$meta = $ppdb->get_row("Select siteurl, thumbnailpath from " . $pixelpost_db_prefix . "config");


		echo $before_widget;
		if ($numthum > 1 && $instance['pixelpost_widget_multititle']) { 
			$title = $instance['pixelpost_widget_multititle']; 
		} else {
			$title = $instance['pixelpost_widget_singletitle'];
		}
		// show the title
		echo $before_title . $title . $after_title;
		// and for each image, show a thumbnail
		foreach ($random as $oneimage) {
			// make sure the thumbnail is a clickable link back to the photoblog
			echo '<a href="'. $meta->siteurl . 'index.php?showimage=' . $oneimage->id . '"><img src="' . $meta->siteurl . 'admin/' . $meta->thumbnailpath . 'thumb_' . $oneimage->image . '" /></a>&nbsp;';
			}
		echo '<br />';
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		// processes widget options to be saved
		$instance = $old_instance;
		$instance['pixelpost_widget_numthum'] = strip_tags(stripslashes($new_instance['pixelpost_widget_numthum']));
		$instance['pixelpost_widget_singletitle'] = strip_tags(stripslashes($new_instance['pixelpost_widget_singletitle']));
		$instance['pixelpost_widget_multititle'] = strip_tags(stripslashes($new_instance['pixelpost_widget_multititle']));
		return $instance;
	}

	function form($instance) {
		// outputs the options form on admin
		$instance = wp_parse_args( (array) $instance, array('pixelpost_widget_numthum'=>9, 'pixelpost_widget_singletitle'=>'One of my photos', 'pixelpost_widget_multititle'=>'Some of my photos') );
		
		$pixelpost_widget_numthum = htmlspecialchars($instance['pixelpost_widget_numthum']);
		$pixelpost_widget_singletitle = htmlspecialchars($instance['pixelpost_widget_singletitle']);
		$pixelpost_widget_multititle = htmlspecialchars($instance['pixelpost_widget_multititle']);


		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('pixelpost_widget_numthum') . '">' . __('Number of thumbnails to show:') . ' <input style="width: 50px;" id="' . $this->get_field_id('pixelpost_widget_numthum') . '" name="' . $this->get_field_name('pixelpost_widget_numthum') . '" type="text" value="' . $pixelpost_widget_numthum . '" /></label></p>';
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('pixelpost_widget_singletitle') . '">' . __('Heading if single image displayed:') . ' <input style="width: 150px;" id="' . $this->get_field_id('pixelpost_widget_singletitle') . '" name="' . $this->get_field_name('pixelpost_widget_singletitle') . '" type="text" value="' . $pixelpost_widget_singletitle . '" /></label></p>';
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('pixelpost_widget_multititle') . '">' . __('Heading if multiple thumbnails displayed:') . ' <input style="width: 150px;" id="' . $this->get_field_id('pixelpost_widget_multititle') . '" name="' . $this->get_field_name('pixelpost_widget_multititle') . '" type="text" value="' . $pixelpost_widget_multititle . '" /></label></p>';
	}
}

function Pixelpost_Widget_init() {
register_widget('Pixelpost_Widget');
}
add_action('widgets_init', 'Pixelpost_Widget_init');
	
?>