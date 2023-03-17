<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    linkclicky
 * @subpackage linkclicky/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    linkclicky
 * @subpackage linkclicky/admin
 * @author     Ludwig Media <support@linkclicky.com>
 */

require (__DIR__ . '/../includes/vendor/autoload.php');

use Pdp\Rules;
use Pdp\Domain;


class LinkClicky_Admin {
	public $settings_slug = 'linkclicky';
	private $default_domain_name ='';

	public function __construct() {
		$publicSuffixList = Rules::fromPath(__DIR__ . '/../data/public_suffix_list.dat');
		$domain = Domain::fromIDNA2008( $this->urlToDomain( get_site_url() ) );
		$result = $publicSuffixList->resolve($domain);
		// add leading period
		$this->default_domain_name = '.'.$result->registrableDomain()->toString(); 

		$this->init();
	}

	public function init() {
		// create and set defaults for the options
		add_option( 'linkclicky-domain-name' , $this->default_domain_name );
		add_option( 'linkclicky-ttl' , 30 );

		// register the option types
		register_setting( 'linkclicky', 'linkclicky-domain-name', array ('type' => 'string', 'description' => 'If different than the default domain name used in confirmation. Useful when hosting a subdomain.' ));
		register_setting( 'linkclicky', 'linkclicky-ttl', array ('type' => 'number', 'description' => 'How long should the cookie be active in days.' ) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
	}
	
	public function admin_menu() {
		add_options_page(
			__('LinkClicky Settings', 'liknclicky'),
                        __('LinkClicky', 'liknclicky'),
                        'manage_options',
                        'linkclicky',
                        array($this, 'settings_page')
                );
	}

        public function settings() {
                add_settings_section( 'linkclicky-section', null, array ($this, 'settings_section_description'), 'linkclicky' );
                add_settings_field( 'linkclicky-domain-name', 'Domain Name Cookie', array ($this, 'domain_name_field'), 'linkclicky', 'linkclicky-section' );
                add_settings_field( 'linkclicky-ttl', 'Cookie Age', array ($this, 'ttl_field'), 'linkclicky', 'linkclicky-section' );
        }

	public function settings_section_description(){
		echo wpautop( "<span style=\"font-size: 18px;\">For more documentation on using this plugin, please visit our <a href=\"https://support.linkclicky.com/?utm_source=wpplugin&utm_medium=link&utm_campaign=settings\" target=\"_blank\">online manual</a>.</span>" );
	}

	public function settings_page() {
?>
<div class="wrap">
	<h1>LinkClicky Settings</h1>
	<form method="post" action="options.php">
<?php
            settings_fields( 'linkclicky' );
            do_settings_sections( 'linkclicky' );
            submit_button();
?>
	</form>
</div>
<?php
	}

	public function domain_name_field() {
		$output  = '<input id="linkclicky-domain-name" type="text" name="linkclicky-domain-name" value="'. get_option('linkclicky-domain-name') .'" size="40">';
		$output .= ' <small>start the domain with a period to allow subdomains.</small>';
		echo $output;
	}

	public function ttl_field() {
		$output  = 'Domain Name: <input id="linkclicky-ttl" type="text" name="linkclicky-ttl" value="'. get_option('linkclicky-ttl') .'" size="3">';
		$output .= ' <small>Time for the cooke to live (in days).</small>';
		echo $output;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );

	}

	// Strip out just the domain name
	private function urlToDomain($url) {
		return implode(array_slice(explode('/', preg_replace('/https?:\/\/(www\.)?/', '', $url)), 0, 1));
	}
}

$linkclicky_admin = new LinkClicky_Admin();
