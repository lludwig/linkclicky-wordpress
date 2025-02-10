<?php 

use LinkClickySDK\LinkClicky;

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

function linkclicky_generateRandomString($length = 20):string {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[random_int(0, $charactersLength - 1)];
	}
	return($randomString);
}

function linkclicky_create_sessionid():string {
	// store the uid
	$sessionid = linkclicky_generateRandomString( 20 );
	
	return($sessionid);
}

function linkclicky_sessions_set_cookie( string $sessionid ):void {
   header('Expires: Thu, 23 Mar 1972 07:00:00 GMT');
   header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
   header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
   header('Pragma: no-cache');

   setcookie(LC_SESSIONS_COOKIE, $sessionid, [
      'expires'  => strtotime('+3650 days'),
      'path'     => '/',
      'domain'   => get_option('linkclicky-domain-name'),
      'secure'   => false,
      'httponly' => false,
      'samesite' => 'strict',
   ]);
   // store it in a cookie session since PHP doesn't do this for the same page view event.
   $_COOKIE[LC_SESSIONS_COOKIE]=$sessionid;
}

function linkclicky_get_IP() {
   $ip = '';

   // Precedence: if set, X-Forwarded-For > HTTP_X_FORWARDED_FOR > HTTP_CLIENT_IP > HTTP_VIA > REMOTE_ADDR
   $headers = [ 'X-Forwarded-For', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_VIA', 'REMOTE_ADDR' ];
   foreach( $headers as $header ) {
      if ( !empty( $_SERVER[ $header ] ) ) {
         $ip = $_SERVER[ $header ];
         break;
      }
   }

   // headers can contain multiple IPs (X-Forwarded-For = client, proxy1, proxy2). Take first one.
   if ( strpos( $ip, ',' ) !== false )
      $ip = substr( $ip, 0, strpos( $ip, ',' ) );

   return ((string) linkclicky_sanitize_ip( $ip ));
}

function linkclicky_sanitize_ip($ip ):string {
	return (preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip ));
}

add_action( 'send_headers', 'linkclicky_sessions_init' );
function linkclicky_sessions_init():void {
   // debug
   do_action( 'qm/start', 'linkclicky_sessions_init' );

   // get cookie
   $sessionid = $_COOKIE[LC_SESSIONS_COOKIE] ?? null;

   $woopra_domain = get_option('linkclicky-woopra-domain');

   // create a Woopra cookie only if the option is set and we do not have one currently
   if (!empty($woopra_domain) && empty($_COOKIE['wooTracker']) ) { 
      $woopra = new WoopraTracker([
         'domain'            => $woopra_domain,
         'cookie_domain'     => get_option('linkclicky-domain-name'),
         'download_tracking' => true,
         'outgoing_tracking' => true,
         'idle_timeout'      => 3600000,
      ]);

      $woopra->set_woopra_cookie();
   }

   if(empty($sessionid)) {
      $lc_server = get_option('linkclicky-api-server');
      $lc_key = get_option('linkclicky-api-key');
      if (!empty($lc_server) && !empty($lc_key)) {
         $sessionid = linkclicky_create_sessionid();

         $lc = new LinkClicky($lc_server, $lc_key);

         $data = [];
         $data = $_GET;
         // make sure the data is properly escaped
         foreach ($data as $key => $value) {
               $data[$key] = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
         }

         // add woopra's cookie
         if (!empty($woopra_domain)) { 
            $data += [
               'wootracker'   => $woopra->current_config['cookie_value'],
            ];
         }
         $lc->SessionAdd($sessionid, linkclicky_get_IP(), $data);

         linkclicky_sessions_set_cookie($sessionid);
      }
   }

   // debug
   do_action( 'qm/stop', 'linkclicky_sessions_init' );
}
