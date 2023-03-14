<?php 
/**
 * Plugin Name: LinkClicky
 * Description: WordPress plugin to compliment LinkClicky service
 * Version:     1.0.7
 * Author:      Ludwig Media
 * Author URI:  https://linkclicky.com/
 * License:     GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

if (!defined('LINKCLICKY_VERSION_NUM'))
	define('LINKCLICKY_VERSION_NUM', '1.0.7');

if ( ! defined( 'LINKCLICKY_PATH' ) ) {
        define( 'LINKCLICKY_PATH', plugin_dir_path( __FILE__ ) );
} 

require_once( LINKCLICKY_PATH . 'admin/class-linkclicky-admin.php' );

// Add the ability to WP to create async loading scripts
function linkclicky_add_async_forscript($url)
{
    if (strpos($url, '#asyncload')===false)
        return $url;
    else if (is_admin())
        return str_replace('#asyncload', '', $url);
    else
        return str_replace('#asyncload', '', $url)."' async='async"; 
}
add_filter('clean_url', 'linkclicky_add_async_forscript', 11, 1);

function add_action_links( $actions, $plugin_file ) {
	static $plugin;

       	if (!isset($plugin))
		$plugin = plugin_basename(__FILE__);
	if ($plugin == $plugin_file) {
		$settings = array('settings' => '<a href="options-general.php?page=linkclicky">' . __('Settings', 'General') . '</a>');
		$site_link = array('docs' => '<a href="https://support.linkclicky.com/?utm_source=wpplugin&utm_medium=link&utm_campaign=pluginpage" target="_blank">Docs</a>');
		$actions = array_merge($site_link, $actions);
		$actions = array_merge($settings, $actions);
	}
	return $actions;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links', 10, 5);

function linkclicky() {
?>
<script type="text/javascript" charset="utf-8">
var _lc = _lc || {};
_lc.domain = "<?php echo get_option('linkclicky-domain-name'); ?>";
_lc.cookieExpiryDays = <?php echo get_option('linkclicky-ttl'); ?>;
_lc.additional_params_map = {
	gclid: "IGCLID",
	msclkid: "IMSCLKID",
	fbclid: "IFBCLID",
};
</script>
<?php
}
add_action('wp_footer', 'linkclicky', 1);

function linkclicky_script() {
	wp_enqueue_script( 'linkclicky', plugins_url( '/js/linkclicky.js#asyncload', __FILE__ ), null, LINKCLICKY_VERSION_NUM, true);
}
add_action('wp_enqueue_scripts','linkclicky_script');
