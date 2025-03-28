<?php 
/**
 * Plugin Name:         LinkClicky
 * Plugin URI:          https://linkclicky.com/support/wordpress/
 * Description:         WordPress plugin to compliment LinkClicky service
 * Version:             1.2.3
 * Author:              LinkClicky
 * Author URI:          https://linkclicky.com/
 * Update URI:          https://linkclicky.com/support/wordpress/
 * License:             GNU General Public License v3 or later
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least:   6.0.1
 * Requires PHP:        8.1
 */

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

if (!defined('LINKCLICKY_VERSION_NUM')) {
   define('LINKCLICKY_VERSION_NUM', '1.2.3'); 
}

if (!defined('LINKCLICKY_PATH')) {
        define('LINKCLICKY_PATH', plugin_dir_path( __FILE__ ));
} 

require_once( LINKCLICKY_PATH . 'includes/vendor/autoload.php');
require_once( LINKCLICKY_PATH . 'admin/class-linkclicky-admin.php');
require_once( LINKCLICKY_PATH . 'includes/shortcodes.php');
require_once( LINKCLICKY_PATH . 'includes/myfunctions.php');
require_once( LINKCLICKY_PATH . 'includes/LinkClicky.php');
require_once( LINKCLICKY_PATH . 'includes/LinkClickyUpdateChecker.php');
require_once( LINKCLICKY_PATH . 'includes/debug.php');

define('LC_SESSIONS_COOKIE','_lc_s');

// Add the ability to WP to create async loading scripts
function linkclicky_add_async_forscript($url) {
    if (strpos($url, '#asyncload')===false)
        return $url;
    else if (is_admin())
        return str_replace('#asyncload', '', $url);
    else
        return str_replace('#asyncload', '', $url)."' async='async"; 
}
add_filter('clean_url', 'linkclicky_add_async_forscript', 11, 1);

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links', 10, 5);
function add_action_links( $actions, $plugin_file ) {
	static $plugin;

   if (!isset($plugin))
      $plugin = plugin_basename(__FILE__);

	if ($plugin == $plugin_file) {
		$settings = [ 'settings' => '<a href="options-general.php?page=linkclicky">' . __('Settings', 'General') . '</a>' ];
		$site_link = [ 'docs' => '<a href="https://linkclicky.com/support/?utm_source=wpplugin&utm_medium=link&utm_campaign=pluginpage" target="_blank">Docs</a>' ];
		$actions = array_merge($site_link, $actions);
		$actions = array_merge($settings, $actions);
	}
	return $actions;
}

function linkclicky_js_header() {
	wp_enqueue_script( 'linkclicky', 'https://'.get_option('linkclicky-api-server').'/js/t/', null, null, ['strategy' => 'async']);
}
add_action('wp_enqueue_scripts','linkclicky_js_header');

$rvmedia = get_option('linkclicky-rvmedia', true);
if ($rvmedia) {
   function linkclicky_js_header_rvmedia() {
      wp_enqueue_script( 'linkclicky-rvmedia', 'https://'.get_option('linkclicky-api-server').'/js/t/rvmedia.js', null, null, ['strategy' => 'async']);
   }
   add_action('wp_enqueue_scripts','linkclicky_js_header_rvmedia');
}

$gobankingrates = get_option('linkclicky-gobankingrates', true);
if ($gobankingrates) {
   function linkclicky_js_header_gobankingrates() {
      wp_enqueue_script( 'linkclicky-gobankingrates', 'https://'.get_option('linkclicky-api-server').'/js/t/gobankingrates.js', null, null, ['strategy' => 'async']);
   }
   add_action('wp_enqueue_scripts','linkclicky_js_header_gobankingrates');
}

// only display if we need to have the session data sent
function linkclicky_js_footer() {
?>
<script type="text/javascript" charset="utf-8">
var _lc = _lc || {};
_lc.domain = "<?php echo get_option('linkclicky-domain-name'); ?>";
</script>
<?php
}
add_action('wp_footer', 'linkclicky_js_footer', 1);

// check to see if there's a new version
new LinkClickyUpdateChecker();

