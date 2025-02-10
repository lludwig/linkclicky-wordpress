<?php

// pass an array and spits out json
function debug_json($data, $event = null, $print = false) {
   if ($event != null ) {
      $output  = PHP_EOL . $event . ':' . PHP_EOL;
   }
   else {
      $output = null;
   }
   $output .= json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
   if ( $print == true ) {
      print $output;
   }
   else {
      return($output);
   }
}
