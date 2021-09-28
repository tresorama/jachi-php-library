<?php

/* =================================================== 
      SQL UTILITIES
=================================================== */
function woo_uti_sql_doSql_returnArrayOfArray( $sql = '' ) {
  if (empty($sql)) {return [];}
  global $wpdb;
  $result = $wpdb->get_results($sql);
  $result = woo_uti_sql_convertSqlResultToArrayOfArray($result);
  if (empty($result)) {return [];}
  return $result;
}

function woo_uti_sql_convertSqlResultToArrayOfArray( $result ) {
  return array_map(function($a) {return (array)$a;}, $result );
}

/* =================================================== 
      ARRAY UTILITIES
=================================================== */

function woo_uti_fromArrayOfArray_returnArrayOfChosenProperty( $array, $property_name = '' ) {
  
  if ( empty($property_name) ) {
    return [];
  }
  if ( empty($array) ) {
    return [];
  }

  $n = $property_name;
  
  // exclude array elements that not have $property_name property.
  $filtered = array_filter( $array, function($a) use($n) {
    return isset( $a[$n] );
  });
  
  // create an array of only property value
  $property_array = array_map( function($a) use($n) {
    return $a[$n]; 
  }, $filtered );
  
  return $property_array;

}

function woo_template_uti_array_remove_empty_property($array) {
  $new_array = array_filter($array, function($item) {
    if ( empty($item) || $item === 0 ) {
      return false;
    }
    return true;
  });
  return $new_array;
}

function woo_template_uti_array_or_class_to_multidimensional_array($array_or_class) {
  foreach ($array_or_class as $key => $value) {
    if (is_object($value) || is_array($value) ) {
      if ( is_object($value)) {
        $value = (array) $value;
      }
      $value = woo_template_uti_array_or_class_to_multidimensional_array($value);
      $array_or_class[$key] = $value;
    }
    else {
      $array_or_class[$key] = $value;
    }
  }
  return $array_or_class;
}

function woo_template_uti_json_decode_multidimensional($json_string = '') {
  
  $result = [];

  if (empty($json_string)) {
    return $result;
  }

  // sanitize string
  $json_string = stripslashes($json_string);
  // decode
  $json_string = json_decode($json_string);
  // convert back to array
  $result = (array) $json_string;
  // ensure nested elements of type object became array
  $result = woo_template_uti_array_or_class_to_multidimensional_array($result);
  // ensure empty value are not included
  $result = woo_template_uti_array_remove_empty_property($result);

  return $result;

}

function woo_template_print_array_as_js_object( $array ) {
	$html = '{';

	foreach ( $array as $key => $value ) {
		$nome = "'" . $key . "'";
		if ( is_string( $value ) ) {
			$valore = "'" . $value . "'";
		}
		if (is_numeric($value )) {
			$valore = $value;
		}
		if ( is_array( $value )) {
			ob_start();
			woo_template_print_array_as_js_object( $value );
			$valore = ob_get_clean();
		}
		$html .= $nome . ' : ' . $valore . ',';
	}
	$html .= '}';
	echo $html;
}



?>