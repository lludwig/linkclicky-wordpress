<?php 

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

add_shortcode('linkclicky_sessionid', 'linkclicky_sessionid');
function linkclicky_sessionid($url) {
   if ((is_page() || is_single() ) && !is_admin()) {
      $sessionid=$_COOKIE[LC_SESSIONS_COOKIE] ?? null;
      return('s:'.$sessionid);
   }
}
