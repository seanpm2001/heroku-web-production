<?php
header("Content-Type: text/html;charset=UTF-8");

function batchKindle() {

	$unprocessed = "to_process/";
	$processed = "html/";
	$unproc_arr = glob("to_process/*.html");
	$unproc_count = count($unproc_arr);

	for($p = 0; $p < $unproc_count; $p++) {
		$file = new DOMDocument();
		@$file->loadHTMLFile($unproc_arr[$p]);
		$html_f = $file->saveHTML();
		$html_f =  cleanUpChars($html_f);
		parseHTML($html_f, $unproc_arr[$p]);
		webProd();
	}
}
?>
