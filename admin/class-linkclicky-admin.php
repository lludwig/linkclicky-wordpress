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

class LinkClicky_Admin {
	public $settings_slug = 'linkclicky';
	private $default_domain_name ='';

   public function __construct() {
      do_action( 'qm/start', 'linkclicky_admin' );
      $url = get_site_url();
      $parse = parse_url($url);
		$this->default_domain_name = $parse['host']; 

		$this->init();
      do_action( 'qm/stop', 'linkclicky_admin' );
	}

	public function init() {
		// create and set defaults for the options
		add_option( 'linkclicky-domain-name' , $this->default_domain_name );
		add_option( 'linkclicky-ttl' , 30 );

		// register the option types
		register_setting( 'linkclicky', 'linkclicky-domain-name', ['type' => 'string', 'description' => 'If different than the default domain name used in confirmation. Useful when hosting a subdomain.' ]);
		register_setting( 'linkclicky', 'linkclicky-ttl', ['type' => 'number', 'description' => 'How long should the cookie be active in days.' ] );
		register_setting( 'linkclicky', 'linkclicky-api-server', ['type' => 'string', 'description' => 'LinkClicky\'s URI' ] );
		register_setting( 'linkclicky', 'linkclicky-api-key', ['type' => 'string', 'description' => 'LinkClicky\'s API Key' ] );
		register_setting( 'linkclicky', 'linkclicky-woopra-domain', ['type' => 'string', 'description' => 'Woopra Domain' ] );

		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'settings' ] );
	}
	
	public function admin_menu() {
		add_options_page(
			__('LinkClicky Settings', 'liknclicky'),
                        __('LinkClicky', 'linkclicky'),
                        'manage_options',
                        'linkclicky',
                        [$this, 'linkclicky_settings_page']
                );
	}

        public function settings() {
                add_settings_section( 'linkclicky-section', null, [$this, 'settings_section_description'], 'linkclicky' );
                add_settings_field( 'linkclicky-domain-name', 'Domain Name Cookie', [$this, 'domain_name_field'], 'linkclicky', 'linkclicky-section' );
                add_settings_field( 'linkclicky-ttl', 'Cookie Age', [$this, 'ttl_field'], 'linkclicky', 'linkclicky-section' );
                add_settings_field( 'linkclicky-api-server', 'Linkclicky API Server', [$this, 'api_server'], 'linkclicky', 'linkclicky-section' );
                add_settings_field( 'linkclicky-api-key', 'Linkclicky API Key', [$this, 'api_key'], 'linkclicky', 'linkclicky-section' );
                add_settings_field( 'linkclicky-woopra-domain', 'Woopra Domain', [$this, 'woopra_domain'], 'linkclicky', 'linkclicky-section' );
        }

	public function settings_section_description(){
		echo wpautop( "<span style=\"font-size: 18px;\">For more documentation on using this plugin, please visit our <a href=\"https://linkclicky.com/support/?utm_source=wpplugin&utm_medium=link&utm_campaign=settings\" target=\"_blank\">online manual</a>.</span>" );
	}

   public function linkclicky_settings_page() {
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

   public function api_server() {
      $output  = 'https:// <input id="linkclicky-api-server" type="text" name="linkclicky-api-server" value="'. get_option('linkclicky-api-server') .'" size="40">';
      $output .= ' <small>the URL of the LinkClicky installation.</small>';
      echo $output;
   }

   public function api_key() {
      $output  = '<input id="linkclicky-api-key" type="text" name="linkclicky-api-key" value="'. get_option('linkclicky-api-key') .'" size="40">';
      echo $output;
   }

   public function woopra_domain() {
      $output  = '<input id="linkclicky-woopra-domain" type="text" name="linkclicky-woopra-domain" value="'. get_option('linkclicky-woopra-domain') .'" size="40">';
      $output .= ' <small>If a domain is entered, LinkClicky will create a Woopra cookie.</small>';
      echo $output;
   }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
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

	private function get_domain($domain) {
		$original = $domain = strtolower($domain);     
		if (filter_var($domain, FILTER_VALIDATE_IP)) { return $domain; }    

		$arr = array_slice(array_filter(explode('.', $domain, 4), function($value){
			return $value !== 'www'; }), 0); //rebuild array indexes

		if (count($arr) > 2)    {
			$count = count($arr);
			$_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);

			if (count($_sub) === 2)  { // two level TLD
				$removed = array_shift($arr);
				if ($count === 4) // got a subdomain acting as a domain
					$removed = array_shift($arr);            
			}
			elseif (count($_sub) === 1){ // one level TLD
				$removed = array_shift($arr); //remove the subdomain             
  				if (strlen($_sub[0]) === 2 && $count === 3) // TLD domain must be 2 letters
					array_unshift($arr, $removed);                
				else{
					// non country TLD according to IANA
					$tlds = [ 'aero', 'arpa', 'asia', 'biz', 'cat', 'com', 'coop', 'edu', 'gov', 'info', 'jobs', 'mil', 'mobi', 'museum', 'name', 'net', 'org', 'post', 'pro', 'tel', 'travel', 'xxx',    ];             
					if (count($arr) > 2 && in_array($_sub[0], $tlds) !== false) {//special TLD don't have a country
						array_shift($arr);
					}
				}
			}
			else { // more than 3 levels, something is wrong
				for ($i = count($_sub); $i > 1; $i--) 
					$removed = array_shift($arr);
			}
		}
		elseif (count($arr) === 2) {
			$arr0 = array_shift($arr);     
			if (strpos(join('.', $arr), '.') === false && in_array($arr[0], array('localhost','test','invalid')) === false) // not a reserved domain
				{
					// seems invalid domain, restore it
					array_unshift($arr, $arr0);
				}
		}     
		return join('.', $arr);
	}
}

$linkclicky_admin = new LinkClicky_Admin();
