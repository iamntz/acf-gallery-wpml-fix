<?php


/*
Plugin Name: ACF Gallery WPML Compatibility Fix
Description: Adds compatibility between ACF Galelry field and WPML on certain cases (i.e. cloning a post won't update the attachments witht their translated IDs)
Author: Ionuț Staicu
Version: 0.0.1
Author URI: http://ionutstaicu.com
*/


require_once( 'inc/acf_gallery_wpml_compat.php' );

new acf_gallery_wpml_compat(isset($_REQUEST['post_id']) ? absint($_REQUEST['post_id']) : 0);
