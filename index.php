<?php
/*
Plugin Name: ACF Gallery WPML Compatibility Fix
Description: Adds compatibility between ACF Galelry field and WPML on certain cases (i.e. cloning a post won't update the attachments witht their translated IDs)
Author: Ionuț Staicu
Version: 0.0.3
Author URI: http://ionutstaicu.com
 */

if (!class_exists('acf_gallery_wpml_compat')) {
	require_once 'inc/acf_gallery_wpml_compat.php';
}

add_action('plugins_loaded', function () {

  if(!empty($_REQUEST['post_id'])) {
	 $postID = absint($_REQUEST['post_id']);
  } else if (WP_DEBUG && !empty($_REQUEST['post'])) {
    $postID = absint($_REQUEST['post']);
  } else {
    return;
  }

	if ($postID == 0 || !function_exists('have_rows') || !function_exists('icl_object_id')) {
		return;
	}

	$compatibilityHook = new acf_gallery_wpml_compat($postID);
}, 50);
