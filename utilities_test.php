<?php
header("Content-Type: text/html;charset=UTF-8");
function parseHTML($code_html, $filename) {
	//get original HTML file
	if(empty($code_html)) {
		print "file: " . $filename . " has no content\n";
		return false;
	}
	$file = new DOMDocument("1.0", "UTF-8");
	$file->formatOutput = true;
	$file->preserveWhiteSpace = false;

	@$file->loadHTML($code_html);		//using @ to avoid showing warnings which are not important
	//create new DOM document for the export file
	$out_file = new DOMDocument("1.0", "UTF-8");

	//get divs & paras from original HTML for manipulation
	$para = $file->getElementsByTagName("p");
	$divs = $file->getElementsByTagName("div");
	$hed_file_name = basename($filename);
	$hed = "";
	//get length for divs & paras
	$count_para = $para->length;
	$count_divs = $divs->length;
	//to avoid multiple byline meta tags
	$count_byline = 0;
	//allowable html tags
	$allow_html = "<p><strong><em><b><i><u><sub><sup>";

	/*------- create basic html for out_file------*/
	$html = $out_file->createElement("html");
	$html_head = $out_file->appendChild($html);
	$head = $out_file->createElement("head");
	$head_e = $html_head->appendChild($head);
	$title = $out_file->createElement("title");
	$title_e = $head->appendChild($title);
	$body = $out_file->createElement("body");
	$body_e = $html->appendChild($body);
	/*--------- end basic html file creation ---------*/

	//loop thorugh node list of all divs
	foreach($divs as $div) {
		//get rid of all id references
		if($div->getAttribute("id")) {
			$div->removeAttribute("id");
		}

		//main story.  Loop through all p nodes & set attributes
		$p = $div->getElementsByTagName("p");
		$count_p = $p->length;

		for($x = 0; $x < $count_p; $x++) {
			$class = $p->item($x)->getAttribute("class");
			if(preg_match("/[^\n\r>]*caption[-]*bold[-]*black[^\n\r>]*/",$class)) {
				$p->item($x)->nodeValue = "<span Bgcolor=\"#aaaaaa\"><strong>" . $p->item($x)->nodeValue . "</strong></span>";
			}
			if(preg_match("/[^\n\r>]*[-]*[Ss][Uu][Bb][Hh][Ee][Aa][Dd][-]*[Rr][Ee][Dd][^\n\r>]*/", $class)) {
				$p->item($x)->nodeValue = strtoupper($p->item($x)->nodeValue);
			}
			if(preg_match("/[^\n\r>]*[Tt][Rr][Aa][Cc][Kk][-]*[Nn][Uu][Mm][Bb][Ee][Rr][^\n\r>]*/", $class)) {
				$p->item($x)->nodeValue = strtoupper($p->item($x)->nodeValue);
			}
			if(preg_match("/[^\n\r>]*[Ff][Ee][Aa][Tt][Uu][Rr][Ee][-]*[Dd][Ee][Pp][Aa][Rr][Tt][Mm][Ee][Nn][Tt][-]*[Hh][Ee][Dd][^\n\r>]*/", $class)) {
				$new_text = $file->createTextNode(strtoupper($p->item($x)->nodeValue));
				$new_bold = $file->createElement("strong");
				$inject_upper = $new_bold->appendChild($new_text);
				$p->item($x)->nodeValue = "";
				$new_p = $p->item($x)->appendChild($new_bold);
			}
			$p_e = $p->item($x);
			$p_e = $out_file->importNode($p_e, true);
			if($p_e->hasChildNodes()) {
				$spans = $p_e->getElementsByTagName("span");
				$count_spans = $spans->length;

				for($y = 0; $y < $count_spans; $y++) {
					$class_value = $spans->item($y)->getAttribute("class");
					if(preg_match("/[^\n\r>]*[Ss][Mm][Aa][Ll][Ll][-]*[Cc][Aa][Pp][Ss][-]*[Ll][Ee][Aa][Dd][^\n\r>]*/", $class_value) || preg_match("/[^\n\r>]*[Cc][Aa][Pp][Ss][-]*[Ll][Ee][Aa][Dd][^\n\r>]*/", $class_value)) {
						$new_bold = $out_file->createElement("strong");
						$new_bold->nodeValue = strtoupper($spans->item($y)->nodeValue);
						$new_smallcaps = $p_e->insertBefore($new_bold, $spans->item($y));
					}
					if(preg_match("/[^\n\r>]*[Bb][Oo][Ll][Dd][^\n\r>]*/", $class_value) || preg_match("/[^\n\r>]*[Tt][Ii][Tt][Ll][Ee][^\n\r>]*/", $class_value) || preg_match("/[^\n\r>]*[Ll][Ee][Aa][Dd][Ii][Nn][^\n\r>]*/", $class_value)) {
						$strong = $out_file->createElement("strong");
						if(preg_match("/[^\n\r>]*[Tt][Ii][Tt][Ll][Ee][^\n\r>]*/", $class_value)) {
							$strong->nodeValue = strtoupper($spans->item($y)->nodeValue);
						}
						else {
							$strong->nodeValue = ucfirst($spans->item($y)->nodeValue);
						}
						$new_strong = $p_e->insertBefore($strong, $spans->item($y));
					}
					if(preg_match("/[^\n\r>]*[Ii][Tt][Aa][Ll][Ii][Cc][^\n\r>]*/", $class_value)) {
						$itals = $out_file->createElement("em");
						$itals->nodeValue = $spans->item($y)->nodeValue;
						$new_itals = $p_e->insertBefore($itals, $spans->item($y));
					}
					if(preg_match("/[^\n\r>]*[Aa][Cc][Rr][Oo][Nn][Yy][Mm][^\n\r>]*/", $class_value)) {
						$spans->item($y)->nodeValue = strtoupper($spans->item($y)->nodeValue);
					}
				}

				$back = $count_spans - 1;

				for($b = $back; $b > -1; $b--) {
					$class_value = $spans->item($b)->getAttribute("class");
					if(preg_match("/[^\n\r>]*[Ss][Mm][Aa][Ll][Ll][-]*[Cc][Aa][Pp][Ss][-]*[Ll][Ee][Aa][Dd][^\n\r>]*/", $class_value) || preg_match("/[^\n\r>]*[Bb][Oo][Ll][Dd][^\n\r>]*/", $class_value) || preg_match("/[^\n\r>]*[Ii][Tt][Aa][Ll][Ii][Cc][^\n\r>]*/", $class_value) || preg_match("/[^\n\r>]*[Tt][Ii][Tt][Ll][Ee][^\n\r>]*/", $class_value) || preg_match("/[^\n\r>]*[Ll][Ee][Aa][Dd][Ii][Nn][^\n\r>]*/", $class_value)) {
						$p_e->removeChild($spans->item($b));
						$back--;
					}
				}

				$bolds = $p_e->getElementsByTagName("strong");
				$itals = $p_e->getElementsByTagName("em");
				$count_bolds = $bolds->length;
				$count_itals = $itals->length;

				for($i = 0; $i < $count_bolds; $i++) {
					if($bolds->item($i)->hasAttribute("class")) {
						$bolds->item($i)->removeAttribute("class");
					}
					if($bolds->item($i)->hasAttribute("id")) {
						$bolds->item($i)->removeAttribute("id");
					}
					if($bolds->item($i)->hasAttribute("style")) {
						$bods->item($i)->removeAttribute("style");
					}
				}

				for($i = 0; $i < $count_itals; $i++) {
					if($itals->item($i)->hasAttribute("class")) {
						$itals->item($i)->removeAttribute("class");
					}
					if($itals->item($i)->hasAttribute("id")) {
						$itals->item($i)->removeAttribute("id");
					}
					if($itals->item($i)->hasAttribute("style")) {
						$itals->item($i)->removeAttribute("style");
					}
				}
			}
			$p_body = $body->appendChild($p_e);
		}
		$del_p_count = $count_p - 1;
		//marker
	}
	//insert the issue pub date in the date meta tag
	$date_meta = $out_file->createElement("meta");
	$date_meta->setAttribute("name", "dc.date.issued");
	$date_e = $head->appendChild($date_meta);

	//loop through HTML, clear all reference to css classes, styles, and ids in paragraphs
	$para = $out_file->getElementsByTagName("p");
	$count_para = $para->length;
	for($i = 0; $i < $count_para; $i++) {
		$element = $para->item($i);
		$element->removeAttribute("class");
		$element->removeAttribute("style");
		$element->removeAttribute("id");
	}

	//concatenate name of file and save
	//first file with 2 appended to name is "dirty" file
	if($hed_file_name == "" || $hed_file_name == null) {
		$hed_file_name = "temp-" . rand(1, 25);
	}
	$hed_file_name = preg_replace("/\.html/i", "", $hed_file_name);
	$file_name = "html/" . $hed_file_name . "2.html";
	//this is the clean file
	$clean_file = "html/" . $hed_file_name . ".html";
	if(!$out_file->saveHTMLFile($file_name)) {
		print "something went wrong\n";
	}
	$cleanup = fopen($file_name, "r");
	$cleanme = fopen($clean_file, "w+");

	while($line = fgets($cleanup)) {
		$line = preg_replace("@<\?@", "#?#", $line);
		$reformat = "/[\n\r>]*\/>/";
		$line = preg_replace("/#\?#/", "<?", $line);
		$line = cleanUpChars($line);
		$line = strip_tags($line, $allow_html);
		$savel = preg_replace($reformat, " />", $line);
		$savel = strip_tags($savel, $allow_html);

		if(!fputs($cleanme, $savel)) {
			print "I'm sorry Dave, but I'm afraid I can't do that.\n";
		}
	}
	fclose($cleanup);
	fclose($cleanme);
	$out_file->formatOutput = true;
	$out_file->preserveWhiteSpace = false;
	$saved = $out_file->saveHTML();
	//remove dirty file from directory
	$clear2 = "rm -f " . $file_name;
	$saveme =  shell_exec($clear2);
	return html_entity_decode(trim($saved));
}

function cleanUpChars($wip) {
	//Characters to delete
	//"/(‚àö√°¬¨¬Æ¬¨¬®¬¨√Ü~is)/",‚Äö√Ñ√∂ "/(~(‚Äö√†√∂¬¨¬¢)/"
	$del_array = Array("/(~‚Äö√†√∂¬¨¬¢)/", "/(‚àö√°¬¨¬Æ¬¨¬®¬¨√Ü~)/","/~(.)(&#160;)(\s)~/","/(‚àö√°¬¨¬Æ¬¨¬®¬¨√Ü‚Äö√†√∂¬¨¬¢)/", "/(~‚Äö√†√∂¬¨¬¢‚Äö√Ñ√∂‚àö√°¬¨¬Æ¬¨¬®¬¨¬©~is)/", "/(~‚Äö√†√∂¬¨¬¢)/", "/(‚àö√°¬¨¬Æ\'\)~)/", "/(~‚Äö√Ñ√∂‚àö√ë‚àö√Ü\?~)/", "/~(\s)(&#160;)(.)~/");
	$wip = preg_replace($del_array, "", $wip);
	//Replace quotes with proper html entities for kindle edition only
	/*$wip = str_replace("‘", "&lsquo;", $wip);
	 $wip = str_replace("’", "&lsquo;", $wip);
	 $wip = str_replace("’", "&rsquo;", $wip);
	 $wip = str_replace("”", "&rdquo;", $wip);
	 $wip = str_replace("“", "&ldquo;", $wip);*/
	$wip = str_replace("—", "&#8212;", $wip);
	$wip = str_replace("—", "&#8212;", $wip);
	$wip = str_replace("ﬁ", "fi", $wip);
	/*$dquote_count = count($dquote_array);
	 for($d = 0; $d < $dquote_count; $d++) {
	 $wip = str_replace($dquote_array, "\"", $wip);
	 }*/
	//Removes ‚Äö√†√∂¬¨¬¢‚Äö√Ñ√∂‚àö√°¬¨¬Æ¬¨¬®¬¨¬© [ords 226 128 169] preceeding </p>
	$wip = preg_replace('/(~(‚Äö√†√∂¬¨¬¢‚Äö√Ñ√∂‚àö√°¬¨¬Æ¬¨¬®¬¨¬©<\/p>)~is)/', '</p>', $wip);
	//Replaces <p><p> with <p>
	$wip = preg_replace('/(~<p><p>~is)/', '<p>', $wip);
	//Removes extra punctuation between end of last sentence and </p>
	$wip = preg_replace('~(\'|\"|\!|\?|\.)\s*\?</p>~', "\\1"."</p>", $wip);
	//Removes unknown symbol in spacing paragraphs
	$wip = preg_replace('~<p>\?</p>~is', '<p></p>', $wip);
	//Replaces strange characters with elipses
	$elipses = Array("/(~(‚Äö√Ñ√∂‚àö√ë¬¨‚àÇ)~)/", "/(~(‚Äö√†√∂¬¨¬¢‚Äö√Ñ√∂‚àö√°¬¨¬Æ¬¨¬®¬¨‚àÇ)~)/");
	//replace all instances of non html mdash occurences with &#8212;
	$mdash_array = Array("/(‚àö√°¬¨¬Æ)/", "/(‚àö√Ñ‚àö‚à´~)/","/(~‚Äö√†√∂‚àö¬¥~)/","/(‚Äö√Ñ√∂‚àö√ë‚àö√Ü)/", "/(~‚àö¬¢‚Äö√á¬®‚Äö√Ñ√π~)/","/(‚Äö√Ñ√Æ)/","/(~‚Äö√†√∂¬¨¬¢~)/", "/(&mdash;)/", "/—/");
	$wip = preg_replace($mdash_array, "&#8212;", $wip);
	//replace weird ellipsis problem
	$wip = str_replace("…", "...", $wip);
	$wip = str_replace("‚Äö√Ñ¬∂", "...", $wip);

	//------- new code for special alphabet to html entities ------//
	$wip = str_replace( "À", "&#192;", $wip );
	$wip = str_replace( "Á", "&#193;", $wip );
	$wip = str_replace( "Â", "&#194;", $wip );
	$wip = str_replace( "Ã", "&#195;", $wip );
	$wip = str_replace( "Ä", "&#196;", $wip );
	$wip = str_replace( "Å", "&#197;", $wip );
	$wip = str_replace( "Æ", "&#198;", $wip );
	$wip = str_replace( "Ç", "&#199;", $wip );
	$wip = str_replace( "È", "&#200;", $wip );
	$wip = str_replace( "É", "&#201;", $wip );
	$wip = str_replace( "Ê", "&#202;", $wip );
	$wip = str_replace( "Ë", "&#203;", $wip );
	$wip = str_replace( "Ì", "&#204;", $wip );
	$wip = str_replace( "Í", "&#205;", $wip );
	$wip = str_replace( "Î", "&#206;", $wip );
	$wip = str_replace( "Ï", "&#207;", $wip );
	$wip = str_replace( "Ð", "&#208;", $wip );
	$wip = str_replace( "Ñ", "&#209;", $wip );
	$wip = str_replace( "Ò", "&#210;", $wip );
	$wip = str_replace( "Ó", "&#211;", $wip );
	$wip = str_replace( "Ô", "&#212;", $wip );
	$wip = str_replace( "Õ", "&#213;", $wip );
	$wip = str_replace( "Ö", "&#214;", $wip );
	$wip = str_replace( "×", "&#215;", $wip );
	$wip = str_replace( "Ø", "&#216;", $wip );
	$wip = str_replace( "Ù", "&#217;", $wip );
	$wip = str_replace( "Ú", "&#218;", $wip );
	$wip = str_replace( "Û", "&#219;", $wip );
	$wip = str_replace( "Ü", "&#220;", $wip );
	$wip = str_replace( "Ý", "&#221;", $wip );
	$wip = str_replace( "Þ", "&#222;", $wip );
	$wip = str_replace( "ß", "&#223;", $wip );
	$wip = str_replace( "à", "&#224;", $wip );
	$wip = str_replace( "á", "&#225;", $wip );
	$wip = str_replace( "â", "&#226;", $wip );
	$wip = str_replace( "ã", "&#227;", $wip );
	$wip = str_replace( "ä", "&#228;", $wip );
	$wip = str_replace( "å", "&#229;", $wip );
	$wip = str_replace( "æ", "&#230;", $wip );
	$wip = str_replace( "ç", "&#231;", $wip );
	$wip = str_replace( "è", "&#232;", $wip );
	$wip = str_replace( "é", "&#233;", $wip );
	$wip = str_replace( "ê", "&#234;", $wip );
	$wip = str_replace( "ë", "&#235;", $wip );
	$wip = str_replace( "ì", "&#236;", $wip );
	$wip = str_replace( "í", "&#237;", $wip );
	$wip = str_replace( "î", "&#238;", $wip );
	$wip = str_replace( "ï", "&#239;", $wip );
	$wip = str_replace( "ð", "&#240;", $wip );
	$wip = str_replace( "ñ", "&#241;", $wip );
	$wip = str_replace( "ò", "&#242;", $wip );
	$wip = str_replace( "ó", "&#243;", $wip );
	$wip = str_replace( "ô", "&#244;", $wip );
	$wip = str_replace( "õ", "&#245;", $wip );
	$wip = str_replace( "ö", "&#246;", $wip );
	$wip = str_replace( "÷", "&#247;", $wip );
	$wip = str_replace( "ø", "&#248;", $wip );
	$wip = str_replace( "ù", "&#249;", $wip );
	$wip = str_replace( "ú", "&#250;", $wip );
	$wip = str_replace( "û", "&#251;", $wip );
	$wip = str_replace( "ü", "&#252;", $wip );
	$wip = str_replace( "ý", "&#253;", $wip );
	$wip = str_replace( "þ", "&#254;", $wip );
	$wip = str_replace( "ÿ", "&#255;", $wip );
	//end accent replacement

	$wip = preg_replace("/(‚Äö√†√∂¬¨¬©)/", '&#233;', $wip);
	$wip = preg_replace('(r‚àö√Ñ‚àö¬∞)', '&#345;', $wip);

	$wip = trim($wip);

	return $wip;
}

function webProd() {
	$web_production = "web_production/";
	$html_files = glob("html/*.html");
	$num_html_f = count($html_files);

	for($i = 0; $i < $num_html_f; $i++) {
		$file_name = $web_production . $html_files[$i];
		$file_name = str_replace("html/", "" ,$file_name);

		$html_contents = file_get_contents($html_files[$i]);
		$html_contents = str_replace("</p>", "</p>\n", $html_contents);
		$html_contents = str_replace("<strong> </strong>", " ", $html_contents);
		$html_contents = str_replace("<em> </em>", " ", $html_contents);
		$html_contents = str_replace(" </em>", "</em>", $html_contents);

		file_put_contents($file_name, $html_contents);
	}
}
?>
