<?php 
session_start();
include("includes_classes/verbindung_mysqli.php");

// Memory-Limit erweitern
ini_set('memory_limit', '232M');

$XML_Daten = new ArrayToXML;
echo "<br /><br />Test:<br /><br />".$xml_test = $XML_Daten -> toXml($_SESSION,'Viewer_Dokument_Definition');


$data = GetXMLTree ($xml_test);

echo "<br /><br /><br /><br /><br />";
echo "<pre>";
print_r($data);
echo "</pre>";

class ArrayToXML
{
	/**
	* The main function for converting to an XML document.
	* Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
	*
	* @param array $data
	* @param string $rootNodeName - what you want the root node to be - defaultsto data.
	* @param SimpleXMLElement $xml - should only be used recursively
	* @return string XML
	*/
	public static function toXml($data, $rootNodeName = 'data', $xml=null)
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1)
		{
		ini_set ('zend.ze1_compatibility_mode', 0);
		}
	
		 
		if ($xml == null)
		{
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
		}
		
		// loop through the data passed in.
		foreach($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				$key = "Node_".$key;
			}
			// replace anything not alpha numeric
			// $key = preg_replace('/[^a-z]/i', '', $key);
			// if there is another array found recrusively call this function
			if (is_array($value))
			{
				$node = $xml->addChild($key);
				// recrusive call.
				ArrayToXML::toXml($value, $rootNodeName, $node);
			}
			else
			{
				// add single node.
				//$value = htmlentities($value);
				$xml->addChild($key,$value);
			}
		}
		// pass back as string. or simple xml object if you want!
		return $xml->asXML();
	}
}





function GetXMLTree ($xmldata)
{
	// we want to know if an error occurs
	ini_set ('track_errors', '1');

	$xmlreaderror = false;

	$parser = xml_parser_create ('ISO-8859-1');
	xml_parser_set_option ($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parser_set_option ($parser, XML_OPTION_CASE_FOLDING, 0);
	if (!xml_parse_into_struct ($parser, $xmldata, $vals, $index)) {
		$xmlreaderror = true;
		echo "error";
	}
	xml_parser_free ($parser);

	if (!$xmlreaderror) {
		$result = array ();
		$i = 0;
		if (isset ($vals [$i]['attributes']))
			foreach (array_keys ($vals [$i]['attributes']) as $attkey)
			$attributes [$attkey] = $vals [$i]['attributes'][$attkey];

		$result [$vals [$i]['tag']] = array_merge ($attributes, GetChildren ($vals, $i, 'open'));
	}

	ini_set ('track_errors', '0');
	return $result;
}

function GetChildren ($vals, &$i, $type)
{
	if ($type == 'complete') {
		if (isset ($vals [$i]['value']))
			return ($vals [$i]['value']);
		else
			return '';
	}

	$children = array (); // Contains node data

	/* Loop through children */
	while ($vals [++$i]['type'] != 'close') {
		$type = $vals [$i]['type'];
		// first check if we already have one and need to create an array
		if (isset ($children [$vals [$i]['tag']])) {
			if (is_array ($children [$vals [$i]['tag']])) {
				$temp = array_keys ($children [$vals [$i]['tag']]);
				// there is one of these things already and it is itself an array
				if (is_string ($temp [0])) {
					$a = $children [$vals [$i]['tag']];
					unset ($children [$vals [$i]['tag']]);
					$children [$vals [$i]['tag']][0] = $a;
				}
			} else {
				$a = $children [$vals [$i]['tag']];
				unset ($children [$vals [$i]['tag']]);
				$children [$vals [$i]['tag']][0] = $a;
			}

			$children [$vals [$i]['tag']][] = GetChildren ($vals, $i, $type);
		} else
			$children [$vals [$i]['tag']] = GetChildren ($vals, $i, $type);
		// I don't think I need attributes but this is how I would do them:
		if (isset ($vals [$i]['attributes'])) {
			$attributes = array ();
			foreach (array_keys ($vals [$i]['attributes']) as $attkey)
			$attributes [$attkey] = $vals [$i]['attributes'][$attkey];
			// now check: do we already have an array or a value?
			if (isset ($children [$vals [$i]['tag']])) {
				// case where there is an attribute but no value, a complete with an attribute in other words
				if ($children [$vals [$i]['tag']] == '') {
					unset ($children [$vals [$i]['tag']]);
					$children [$vals [$i]['tag']] = $attributes;
				}
				// case where there is an array of identical items with attributes
				elseif (is_array ($children [$vals [$i]['tag']])) {
					$index = count ($children [$vals [$i]['tag']]) - 1;
					// probably also have to check here whether the individual item is also an array or not or what... all a bit messy
					if ($children [$vals [$i]['tag']][$index] == '') {
						unset ($children [$vals [$i]['tag']][$index]);
						$children [$vals [$i]['tag']][$index] = $attributes;
					}
					$children [$vals [$i]['tag']][$index] = array_merge ($children [$vals [$i]['tag']][$index], $attributes);
				} else {
					$value = $children [$vals [$i]['tag']];
					unset ($children [$vals [$i]['tag']]);
					$children [$vals [$i]['tag']]['value'] = $value;
					$children [$vals [$i]['tag']] = array_merge ($children [$vals [$i]['tag']], $attributes);
				}
			} else
				$children [$vals [$i]['tag']] = $attributes;
		}
	}

	return $children;
}




