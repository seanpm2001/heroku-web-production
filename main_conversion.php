<?php
header("Content-Type: text/html;charset=UTF-8");

function mainConversion() {
	//get name of file from command line
	$to_convert = glob("to_convert/*.html");
	$count_files = count($to_convert);

	for($i = 0; $i < $count_files; $i++) {
		$new_file = preg_replace("/.html/i", "_converted.html", $to_convert[$i]);
		$complete_file = "to_process/" . basename($new_file);
		//call the normalizeCSS function
		$the_file = normalizeCSS($to_convert[$i]);
		file_put_contents($complete_file, $the_file);
	}
}

//function normalizeCSS($in_file, $out_file) {
function normalizeCSS($in_file) {
	$search_array = Array();
	$whole = file_get_contents($in_file);

	//get css from document
	$open_css_tag = "<style";
	$close_css_tag = "</style>";
	$open_body_tag = "<body>";
	$close_body_tag = "</body>";

	//get pointers to tag indexes
	$opencss_index = stripos($whole, $open_css_tag);
	$closecss_index = stripos($whole, $close_css_tag);
	$openbody_index = stripos($whole, $open_body_tag);
	$closebody_index = stripos($whole, $close_body_tag);

	$css_length = $closecss_index - $opencss_index;
	$html_length = $closebody_index - $openbody_index;

	$css = substr($whole, $opencss_index, $css_length);
	$html = substr($whole, $openbody_index, $html_length);

	//css parsing functions
	$replace_array = Array("/<style .*>.*/i", "/.*<!--.*/i", "/.*-->.*/i");
	$css = preg_replace($replace_array, "", $css);
	$css_properties = explode("}", $css);
	//find and replace inDesign style names with proper class names to be replaced
	$search_array = getSearchTerms($css_properties);

	//html parsing functions
	foreach($search_array as $key => $value) {
		$index = count($value);
		for ($i = 0; $i < $index; $i++) {
			$html = str_replace($value, $key, $html);
		}
	}

	return $html;
}

function getSearchTerms($s_terms) {
	$count = count($s_terms);
	$master_array = Array();
	$bold_array = Array();
	$ital_array = Array();
	$acro_array = Array();
	$uppercase_array = Array();
	$section_lead_array = Array();

	for($i = 0; $i < $count; $i++) {
		$bold = "/.*[Bb][Oo][Ll][Dd].*/i";
		$ital = "/.*[Ii][Tt][Aa][Ll][Ii][Cc].*/i";
		$acronym = "/.*[Aa][Cc][Rr][Oo][Nn][Yy][Mm].*/i";
		$uppercase = "/.*[Uu][Pp][Ee][Rr][Cc][Aa][Ss][Ee].*/i";
		$byline_override = "/.*[Bb][Yy][Ll][Ii][Nn][Ee].*/i";
		$section_lead = "/.*[Ss][Ee][Cc][Tt][Ii][Oo][Nn].*[-].*[Ll][Ee][Aa][Dd].*/i";

		if($matches = preg_match($uppercase, $s_terms[$i], $uppers) && !preg_match($byline_override, $s_terms[$i])) {
			$bracket_index = stripos($s_terms[$i], "{");
			$temp = trim(substr($s_terms[$i], 0, $bracket_index));
			if(preg_match("/\./", $temp)) {
				$get_dot = stripos($temp, ".") + 1;
				$pushme = substr($temp, $get_dot);
				array_push($acro_array, $pushme);
			}
		}

		if($matches = preg_match($acronym, $s_terms[$i], $acros) && !preg_match($byline_override, $s_terms[$i])) {
			$bracket_index = stripos($s_terms[$i], "{");
			$temp = trim(substr($s_terms[$i], 0, $bracket_index));
			if(preg_match("/\./", $temp)) {
				$get_dot = stripos($temp, ".") + 1;
				$pushme = substr($temp, $get_dot);
				array_push($acro_array, $pushme);
			}
		}

		if($matches = preg_match($bold, $s_terms[$i], $bolds) && !preg_match($byline_override, $s_terms[$i])) {
			$bracket_index = stripos($s_terms[$i], "{");
			$temp = trim(substr($s_terms[$i], 0, $bracket_index));
			if(preg_match("/\./", $temp)) {
				$get_dot = stripos($temp, ".") + 1;
				$pushme = substr($temp, $get_dot);
				array_push($bold_array, $pushme);
			}
		}

		if($matches = preg_match($ital, $s_terms[$i], $itals) && !preg_match($byline_override, $s_terms[$i])) {
			$bracket_index = stripos($s_terms[$i], "{");
			$temp = trim(substr($s_terms[$i], 0, $bracket_index));
			if(preg_match("/\./", $temp)) {
				$get_dot = stripos($temp, ".") + 1;
				$pushme = substr($temp, $get_dot);
				array_push($ital_array, $pushme);
			}
		}

		if($matches = preg_match($section_lead, $s_terms[$i], $section_leads) && !preg_match($byline_override, $s_terms[$i])) {
			$bracket_index = stripos($s_terms[$i], "{");
			$temp = trim(substr($s_terms[$i], 0, $bracket_index));
			if(preg_match("/\./", $temp)) {
				$get_dot = stripos($temp, ".") + 1;
				$pushme = substr($temp, $get_dot);
				array_push($section_lead_array, $pushme);
			}
		}
	}

	if(count($acro_array) > 0) {
		$master_array["acronym"] = $acro_array;
	}

	if(count($bold_array) > 0) {
		$master_array["bold"] = $bold_array;
	}

	if(count($ital_array) > 0) {
		$master_array["italic"] = $ital_array;
	}

	if(count($uppercase_array) > 0) {
		$master_array["uppercase"] = $uppercase_array;
	}

	if(count($section_lead_array) > 0) {
		$master_array["section_lead"] = $section_lead_array;
	}

	return $master_array;
}
?>
