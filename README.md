# ACF Gallery fix for WPML compatibility

ACF Gallery has a bit of a problem when it's used in conjunction with WPML: When you clone a post that has a gallery within, all gallery items are still on the initial language.

This comes after couple of years of not having this fixed (in the past I used some workarounds):

- https://support.advancedcustomfields.com/forums/topic/wpml-and-gallery-addon-problem/
- https://support.advancedcustomfields.com/forums/topic/acf-gallery-vs-wpml/
- https://wpml.org/forums/topic/acf-repeater-gallery-images-not-copied/
- https://wpml.org/forums/topic/problems-with-acf-gallery-and-wpml-translating/
- https://wpml.org/forums/topic/no-more-image-media-translation-module-with-acf-gallery-field/

and so on...

#### How it works?

This plugin hooks into WPML and iterates over all gallery items in order to update them with the correct IDs. Everything happens when you hit that [_translate independently_](http://img.iamntz.com/jing/2016-05-09__38_54.jpg) button, right after you clone a page.

#### Known issues
- There might be an issue when you have a crazy nested structure (like a gallery inside of a repeater that's inside of a flexible content), but since I think this is a bad idea anyway, I won't do it.
- There also might be an issue when you're using File field type, but the logic goes by this: if you upload a file to a post, you'll upload files related to _that_ language. I.e. you won't need an English PDF file to be on the Spanish version of that page.

For both of those I'm happy to accept pull requests.


#### How to fix existing posts?

If you already cloned a bunch of posts and need to update them automatically, you could use the following snippet (PHP 5.4 is required; if you use a lower version, you will need to update arrays their longer form):

```php
<?php
add_action('init', function () {
	if (ICL_LANGUAGE_CODE != 'en' && class_exists('acf_gallery_wpml_compat')) {

    if (!get_option('acf_gallery_wpml_fixed_' . ICL_LANGUAGE_CODE)) {
    	set_time_limit(0);
    	$postTypes = get_post_types([
    		'public' => true,
    	]);
    	unset($postTypes['attachment']);
    
    	foreach ($postTypes as $postType) {
    		$loop_name = new WP_Query();
    		$loop_name->query(array(
    			"post_type" => $postType,
    			"posts_per_page" => -1,
    		));
    		echo "<br><h3>Converting: {$postType}</h3>";
    		echo str_repeat("\n", 2048);
    		while ($loop_name->have_posts()) {
    			$loop_name->the_post();
    			echo "<br>Converting Post: " . get_the_ID() . " - ( " . get_the_title() . " )";
    			echo str_repeat("\n", 2048);
    			$migration = new acf_gallery_wpml_compat(get_the_ID());
    			$migration->parse_post_fields();
    		}
    	}
    
    	update_option('acf_gallery_wpml_fixed_' . ICL_LANGUAGE_CODE, true);
    
    	echo 'Migration complete!';
    	die();
    }

  }
});
```
Once you add this, the first time you visit a page that's in a secondary language, it will run through all post types. (please update the first condition `if (ICL_LANGUAGE_CODE != 'en'` to your primary language). You can safetly delete this snipped after migration.


#### License & co
GPLv3
