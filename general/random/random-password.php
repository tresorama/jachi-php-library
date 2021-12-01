<?php

function create_random_string( $length = 10 ) {
  // generate a string based on current time
  $random_string = md5(time());
  // shuffle charaters position
  $random_string = str_shuffle( $random_string );
  // slice by number of desired characters
  $random_string = substr( $random_string, 0, $length );
  // return
  return $random_string;
}