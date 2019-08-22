<?php 
//  Can implode an array of any dimension
        //  Uses a few basic rules for implosion:
        //        1. Replace all instances of delimeters in strings by '/' followed by delimeter
        //        2. 2 Delimeters in between keys
        //        3. 3 Delimeters in between key and value
        //        4. 4 Delimeters in between key-value pairs
        function implodeMDA($array, $delimeter, $keyssofar = '') {
            $output = '';
            foreach($array as $key => $value) {
                if (!is_array($value)) {
                    $value = str_replace($delimeter, '/'.$delimeter, $value);
                    $key = str_replace($delimeter, '/'.$delimeter, $key);
                    if ($keyssofar != '') $key = $key.$delimeter.$delimeter;
                    $pair = $key.$keyssofar.$delimeter.$delimeter.$delimeter.$value;
                    if ($output != '') $output .= $delimeter.$delimeter.$delimeter.$delimeter;
                    $output .= $pair;
                }
                else {
                    if ($output != '') $output .= $delimeter.$delimeter.$delimeter.$delimeter;
                    if ($keyssofar != '') $key = $key.$delimeter.$delimeter;
                    $output .= implodeMDA($value, $delimeter, $key.$keyssofar);
                }
            }
            return $output;
        }
       
       
        //  Can explode a string created by corresponding implodeMDA function
        //  Uses a few basic rules for explosion:
        //        1. Instances of delimeters in strings have been replaced by '/' followed by delimeter
        //        2. 2 Delimeters in between keys
        //        3. 3 Delimeters in between key and value
        //        4. 4 Delimeters in between key-value pairs
        function explodeMDA($string, $delimeter) {
            $output = array();
            $pair_delimeter = $delimeter.$delimeter.$delimeter.$delimeter;
            $pairs = explode($pair_delimeter, $string);
            foreach ($pairs as $pair) {
                $keyvalue_delimeter = $delimeter.$delimeter.$delimeter;
                $keyvalue = explode($keyvalue_delimeter, $pair);
                $key_delimeter = $delimeter.$delimeter;
                $keys = explode($key_delimeter, $keyvalue[0]);
                $value = str_replace('/'.$delimeter, $delimeter, $keyvalue[1]);
                $keys[0] = str_replace('/'.$delimeter, $delimeter, $keys[0]);
                $pairarray = array($keys[0] => $value);
                for ($counter = 1; $counter < count($keys); $counter++) {
                    $pairarray = array($keys[$counter] => $pairarray);
                }
                $output = array_merge_recursive($output, $pairarray);
            }
            return $output;
        }
?>