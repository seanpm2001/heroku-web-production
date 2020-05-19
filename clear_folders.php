<?php
function clearDirs() {
	$delete_html = "*.html";
	$delete_css = "*.css";
	$dir_arrays = Array("html/", "to_convert/", "to_process/", "web_production/");

	for($i = 0; $i < count($dir_arrays); $i++) {
		$rm_command = "rm -f " . $dir_arrays[$i] . $delete_html;
		exec($rm_command);
		$rm_command = "rm -f " . $dir_arrays[$i] . $delete_css;
		exec($rm_command);
	}
}
?>
