<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'avada-stylesheet' ) );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

    
////////////////////////////////////////////////////////////////////////////////////////////////////


/* Load LESS */

function childtheme_scripts() {

wp_enqueue_style('less', get_stylesheet_directory_uri() .'/css/style.less');
add_filter('style_loader_tag', 'my_style_loader_tag_function');

wp_enqueue_script('less', get_stylesheet_directory_uri() .'/scripts/less.min.js', array('jquery'),'2.7.2');

}
add_action('wp_enqueue_scripts','childtheme_scripts', 150);

function my_style_loader_tag_function($tag){   
  return preg_replace("/='stylesheet' id='less-css'/", "='stylesheet/less' id='less-css'", $tag);
}


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Remove Date from Yoast SEO */

add_filter( 'wpseo_show_date_in_snippet_preview', false);


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Remove Dates from SEO on Pages */

function wpd_remove_modified_date(){
    if( is_page() ){
        add_filter( 'the_time', '__return_false' );
        add_filter( 'the_modified_time', '__return_false' );
        add_filter( 'get_the_modified_time', '__return_false' );
        add_filter( 'the_date', '__return_false' );
        add_filter( 'the_modified_date', '__return_false' );
        add_filter( 'get_the_modified_date', '__return_false' );
    }
}
add_action( 'template_redirect', 'wpd_remove_modified_date' );


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Remove Query String */

function _remove_script_version( $src ){
  $parsed = parse_url($src);

  if (isset($parsed['query'])) {
    parse_str($parsed['query'], $qrystr);
    if (isset($qrystr['ver'])) {
      unset($qrystr['ver']); 
    }
    $parsed['query'] = http_build_query($qrystr);
  }
  // return http_build_url($parsed); // elegant but not always available

  $src = '';
  $src .= (!empty($parsed['scheme'])) ? $parsed['scheme'].'://' : '';
  $src .= (!empty($parsed['host'])) ? $parsed['host'] : '';
  $src .= (!empty($parsed['path'])) ? $parsed['path'] : '';
  $src .= (!empty($parsed['query'])) ? '?'.$parsed['query'] : '';

  return $src;
}
add_filter( 'script_loader_src', '_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', '_remove_script_version', 15, 1 );


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Add Field Visibility Section to Gravity Forms */		
		
add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );

add_filter("gform_init_scripts_footer", "init_scripts");
function init_scripts() {
return true;
}


////////////////////////////////////////////////////////////////////////////////////////////////////


/* SVG Support */	


function bodhi_svgs_disable_real_mime_check( $data, $file, $filename, $mimes ) {
    $wp_filetype = wp_check_filetype( $filename, $mimes );

    $ext = $wp_filetype['ext'];
    $type = $wp_filetype['type'];
    $proper_filename = $data['proper_filename'];

    return compact( 'ext', 'type', 'proper_filename' );
}
add_filter( 'wp_check_filetype_and_ext', 'bodhi_svgs_disable_real_mime_check', 10, 4 );

remove_filter('the_content', 'wptexturize');


////////////////////////////////////////////////////////////////////////////////////////////////////


/* If Modified Since */

add_action('template_redirect', 'last_mod_header');

function last_mod_header($headers) {
     if( is_singular() ) {
            $post_id = get_queried_object_id();
            $LastModified = gmdate("D, d M Y H:i:s \G\M\T", $post_id);
            $LastModified_unix = gmdate("D, d M Y H:i:s \G\M\T", $post_id);
            $IfModifiedSince = false;
            if( $post_id ) {
                if (isset($_ENV['HTTP_IF_MODIFIED_SINCE']))
                    $IfModifiedSince = strtotime(substr($_ENV['HTTP_IF_MODIFIED_SINCE'], 5));  
                if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
                    $IfModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
                if ($IfModifiedSince && $IfModifiedSince >= $LastModified_unix) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
                    exit;
                } 
     header("Last-Modified: " . get_the_modified_time("D, d M Y H:i:s", $post_id) );
                }
        }
}