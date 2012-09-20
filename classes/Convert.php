<?php
//Based (but corrected) from http://www.php.net/manual/en/language.types.object.php#102735

final class Convert {
  # Convert a stdClass to an Array.
  static public function object_to_array(stdClass $Class){
  	# Typecast to (array) automatically converts stdClass -> array.
  	$newClass = array();
  	
  	# Iterate through the former properties looking for any stdClass properties.
  	# Recursively apply (array).
  	foreach($Class as $key => $value){
  	  if(is_object($value)&&get_class($value)==='stdClass'){
  	  	$newClass[$key] = self::object_to_array($value);
  	  }else{
  	  	$newClass[$key] = $value;
  	  }
  	}
  	return $newClass;
  }
  
  # Convert an Array to stdClass.
  static public function array_to_object(array $array){
  	# Iterate through our array looking for array values.
  	# If found recurvisely call itself.
  	$obj = new stdClass();
  	foreach($array as $key => $value){
  	  if(is_array($value)){
  	  	$obj->$key = self::array_to_object($value);
  	  }else{
  	  	$obj->$key = $value;
  	  }
  	}
  	
  	# Typecast to (object) will automatically convert array -> stdClass
  	return $obj;
  }
  
  
  //Taken from http://www.php.net/manual/en/ref.array.php#81081
  
  static public function array_copy (array $aSource) {
  	// check if input is really an array
  	if (!is_array($aSource)) {
  	  throw new Exception("Input is not an Array");
  	}
  	
  	// initialize return array
  	$aRetAr = array();
  	
  	// get array keys
  	$aKeys = array_keys($aSource);
  	// get array values
  	$aVals = array_values($aSource);
  	
  	// loop through array and assign keys+values to new return array
  	for ($x=0;$x<count($aKeys);$x++) {
  	  // clone if object
  	  if (is_object($aVals[$x])) {
  	  	$aRetAr[$aKeys[$x]]=clone $aVals[$x];
  	  	// recursively add array
  	  } elseif (is_array($aVals[$x])) {
  	  	$aRetAr[$aKeys[$x]]=self::array_copy ($aVals[$x]);
  	  	// assign just a plain scalar value
  	  } else {
  	  	$aRetAr[$aKeys[$x]]=$aVals[$x];
  	  }
  	}
  	
  	return $aRetAr;
  }
  
  
  static public function getPaths ($r, $path, $results) {
    global $lodspk;
    global $conf;
    $arr = array();
    foreach($r as $k => $v){
      if($k == "params" ){
        continue;
      }
      if($k == "0"){//if query
        return NULL;
      }
      $next = self::getPaths($r->$k, $path."endpoint.".$k."/", $arr);
      if($next == NULL){
         $aux = $path.$k;
         $root = array();
         $pointer = &$root;
         $aux2 = explode("/", $aux);
         $key = str_ireplace("endpoint.", "", array_shift($aux2));
         foreach($aux2 as $w){
           $x = str_ireplace("endpoint.", "", $w);
           $pointer[$x] = array();
           $pointer = &$pointer[$x];
         }
         $pointer = $lodspk['baseUrl'].'lodspeakr/components/'.$conf[$lodspk['module']]['prefix']."/".$lodspk['componentName']."/queries/".$aux.".query";
         if(isset($lodspk['source'][$key])){
          $lodspk['queries'][$key] = array_merge($lodspk['source'][$key], $root);
         }else{
           $lodspk['queries'][$key] = $root;
         }
      }
    }
    return 1;
  }

}
?>
