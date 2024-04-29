<?php 

use LinkClickySDK\LinkClicky;

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

function linkclicky_generateRandomString($length = 20) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[random_int(0, $charactersLength - 1)];
	}
	return $randomString;
}

function linkclicky_create_sessionid() {
	// store the uid
	$sessionid = linkclicky_generateRandomString( 20 );
	
	return($sessionid);
}

function linkclicky_sessions_set_cookie( string $sessionid ) {
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

function linkclicky_sanitize_ip($ip ) {
	return (preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip ));
}

add_action( 'send_headers', 'linkclicky_sessions_init' );
function linkclicky_sessions_init() {
   // get cookie
   $sessionid = $_COOKIE[LC_SESSIONS_COOKIE] ?? null;

   if(empty($sessionid)) {
      do_action( 'qm/start', 'linkclicky_sessions_init' );
      $sessionid = linkclicky_create_sessionid();
      linkclicky_sessions_set_cookie($sessionid);

      $lc_server = get_option('linkclicky-api-server');
      $lc_key = get_option('linkclicky-api-key');
      if (!empty($lc_server) && !empty($lc_key)) {
         $lc = new LinkClicky($lc_server, $lc_key);
         $lc->SessionAdd($sessionid, linkclicky_get_IP());
      }
      do_action( 'qm/stop', 'linkclicky_sessions_init' );
   }
   else {
      linkclicky_sessions_set_cookie($sessionid);
   }
}
