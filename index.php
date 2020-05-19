<?php
ini_set("display_errors", 1);
//require "utilities.php";
include "main_conversion.php";
include "batch_kindle.php";
include "utilities_test.php";
include "clear_folders.php";

$if_request = false;
clearDirs();

$errors = "";
$downloads = "";

if(isset($_FILES) && !empty($_FILES)) {
	$if_request = true;
}

if($if_request) {
	$to_convert = "to_convert/";
	$file_name_html = "";
	$file_name_css = "";
	$tmp_name_html = "";
	$tmp_name_css = "";
	$html_count = count($_FILES["production"]["name"]);
	$css_count = count($_FILES["css"]["name"]);

	if($html_count !== $css_count) {
		$errors = "HTML file count and CSS file count need to match.\n";
	}

	for($i = 0; $i < $html_count; $i++) {
		$the_doc = new DOMDocument();

		if($_FILES["production"]["error"][$i] === UPLOAD_ERR_OK) {
			$tmp_name_html = $_FILES["production"]["tmp_name"][$i];
			$file_name_html = $_FILES["production"]["name"][$i];
			move_uploaded_file($tmp_name_html, $to_convert . $file_name_html);
		}
		if($_FILES["css"]["error"][$i] === UPLOAD_ERR_OK) {
			$tmp_name_css = $_FILES["css"]["tmp_name"][$i];
			$tmp_file_name = str_replace(".html", "", $file_name_html);
			$file_name_css = str_replace("idGeneratedStyles", $tmp_file_name, $_FILES["css"]["name"][$i]);
			move_uploaded_file($tmp_name_css, $to_convert . $file_name_css);
		}

		if($the_doc->loadHTMLFile($to_convert . $file_name_html)) {
			$temp = basename($file_name_html);
			$find_suffix = stripos($temp, ".html");
			$path_name = substr($temp, 0, $find_suffix) . ".css";
			$body_tag = $the_doc->getElementsByTagName("body");
			$working_body = $body_tag[0];
		}
		else {
			exit("HTML file could not be loaded: $path_name. Please check that you are in the correct working directory. \n");
		}

		if($css_file_contents = file_get_contents($to_convert . $path_name)) {
			$temp_node = $the_doc->createElement("style");
			$temp_text = $the_doc->createTextNode($css_file_contents);
			$temp_node->appendChild($temp_text);
			$head_nodes = $the_doc->getElementsByTagName("head");
			$head_nodes[0]->appendChild($temp_node);
		}
		else {
			exit("CSS file could not be loaded: $path_name. Please check that you are in the correct working directory. \n");
		}

		$full_save_path = $to_convert . $temp;
		$the_doc->saveHTMLFile($full_save_path);
	}

	/*foreach($_FILES["css"]["error"] as $key => $error) {
		$tmp_name = $_FILES["css"]["tmp_name"][$key];
		$file_name = $_FILES["css"]["name"][$key];
		if($error == UPLOAD_ERR_OK) {
			move_uploaded_file($tmp_name, $to_convert . $file_name);
		}
		else {
			$errors .= "File name " . $file_name . "did not upload correctly. Please try again.";
		}
	}
	$css_files = glob("to_convert/*.css");*/

	/*foreach ($_FILES["production"]["error"] as $key => $error) {
		$tmp_name = $_FILES["production"]["tmp_name"][$key];
		$file_name = $_FILES["production"]["name"][$key];
		if($error == UPLOAD_ERR_OK) {
			move_uploaded_file($tmp_name, $to_convert . $file_name);
		}
		else {
			$errors = "File name " . $file_name . " did not upload correctly.  Please try again.";
		}
	}*/
	mainConversion();
	batchKindle();
	$downloads = glob("web_production/*.html");
	unset($_FILES);
}
else {
	$chmod[0] = $chmod_html = glob("html/*.html");
	$chmod[1] = $chmod_convert = glob("to_convert/*.html");
	$chmod[2] = $chmod_process = glob("to_process/*.html");
	$chmod[3] = $chmod_production = glob("web_production/*.html");

	if(count($chmod[0]) > 0 && count($chmod[1]) > 0 && count($chmod[2]) > 0 && count($chmod[3] > 0)) {
		for($i = 0; $i < count($chmod); $i++) {
			$new_arr = $chmod[$i];
			for($c = 0; $c < count($new_arr); $c++) {
				if(!chmod($new_arr[$c], 0777)) {
					print "The satelite dish seems to be miscalibrated, Dave. Can you please fix it?";
				}
			}
		}
		clearDirs();
		unset($_FILES);
	}
}
?>
<html>
	<head>
		<title>Web Production HTML formatting tool</title>
		<style>
			#title_me {text-align:center;
				color:#fff;
				background-color: #000;
				margin: 0 auto;
				padding:5px 0;}
			.go_left {float:left;}
			.upload_field {float:center;
				clear:both;}
			input:file {width:25px;
				float:left;}
			#errors {font-size: 12px;
				color: #e11233;}
			#upload_box {margin: 10px auto;
				padding: 5px;
				width:800px;}
			#downloads {clear:both;}
			#add_one {margin:0 20px;
				color:#fff;
				background-color: #000;
				border-radius: 35px;}
			#refresh_page {color: #fff;
				background-color:#000;
				padding: 3px 5px;
				border-radius: 15px;}
			#submit {color: #fff;
				background-color: #000;
				border-radius: 15px;
				padding:5px 5px;
				width:100px;}
			#html_input, #css_input {text-align:center;
				clear:both;}
			#css_input {border-left:1px solid #000;}
			#add_file {width:100%;}
			td {text-align:center;}
			#header {background-color: #eee;
	display:table;
	width:100%;
	margin:0 0;
	padding:10px 0;
	border-bottom: 2px inset #933;}
	#logo {margin-left: 120px;
  width:200px;
  padding: 0 10px;
  clear:both;
  float:left;}
#head_text {text-align:center;
  font-weight:bold;
  float:left;}
		</style>
		<script type="text/javascript">
			function addFile() {
				var file_box = document.getElementById("add_file");
				var html_box = document.getElementById("html_input");
				var css_box = document.getElementById("css_input");
				var submit_btn = document.getElementById("submit");
				var html_block = document.getElementById("html_block");
				var css_block = document.getElementById("css_block");

				if((document.forms[0].elements.length) < 22) {
					var input_file = document.createElement("input");
					var input_file2 = document.createElement("input");
					input_file.type = "file";
					input_file2.type = "file";
					input_file.name = "production[]";
					input_file2.name = "css[]";
					input_file.class="upload_field";
					input_file2.class = "upload_field";
					html_box.insertBefore(input_file, html_block);
					css_box.insertBefore(input_file2, css_block);
				}
				else {
					alert("You have reached the maximum files upload limit");
				}
			}
		</script>
	</head>
	<body style="width:100%;max-width:100%;margin: 0;padding:0;">
	<div id="header">
  	<div id="logo">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 813.67 165.61"><path d="M53.72 97.86h-.39l-8.05-86.64H0v152.49h28.78v-85.9l-1.67-36.19 2.05 14.73 14.39 107.36h19.18L77.12 56.35l2.05-14.73-1.66 36.19v-.04 85.94h28.77V11.22H62.54l-8.82 86.64M391.4 87.12c0-33.06-1.23-58.67-34-58.67S321 61.67 321 91.17c0 37.54 5.56 74.1 39.71 74.1 19.34 0 30.18-17.68 30.18-17.68v-31.07s-8.4 21.23-24.62 21.23c-11.32 0-17.65-10.58-17.65-27.83h42c.01-3.49.78-13.92.78-22.8zm-42.79-3.22c0-13.9 1.92-24.9 9.6-24.9 8.25 0 10.17 12.31 10.17 24.94zM148.49 28.44c-38.75 0-38.75 27.18-38.75 65.11 0 43.53 3.93 71.72 38.75 71.72s38.75-26 38.75-75c.01-35.87.01-61.83-38.75-61.83zm0 104.47c-9.21 0-10-5-10-36.06 0-29.34.77-36.06 10-36.06s10 7.08 10 36.06c-.02 27.23-.79 36.07-10 36.07zM442.34 28.44c-9.37 0-13.73 8.49-16 17.24l-2.56 19.49V30h-29v133.7h31.08V92.19c0-26 7.86-30.34 15.94-30.34 11.79 0 14.55 14.16 14.55 14.16V35.77s-5.48-7.33-14.01-7.33zm-145.72 0c-10.55 0-17.44 7.48-19.7 18.65l-2.17 12.5V11.22h-31.08v152.49h31.08V74.29c0-5.13.44-13.88 6.33-13.88s5.37 7.57 5.37 14.67v88.63h31.08V50.92c0-11.48-4.8-22.48-20.91-22.48zm261.29.34c-38.75 0-38.75 27.18-38.75 65.11 0 43.53 3.93 71.72 38.75 71.72s38.75-26 38.75-75c0-35.87 0-61.83-38.75-61.83zm0 104.47c-9.21 0-10-5-10-36.06 0-29.34.77-36.06 10-36.06s10 7.08 10 36.06c-.03 27.22-.8 36.06-10 36.06zm95.16-104.81c-10.55 0-17.44 7.48-19.7 18.65l-2.18 12.5V30h-31.07v133.7h31.08V74.29c0-5.13.44-13.88 6.33-13.88s5.37 7.59 5.37 14.67v88.63H674V50.92c0-11.48-4.82-22.48-20.93-22.48zm-196.65 91.04v35.95s5.38 9.57 24.19 9.57c28.22 0 35.09-23.13 35.09-48.79v-105h-28.78v92.09c0 26-7.86 30.34-15.94 30.34-11.8-.01-14.56-14.16-14.56-14.16zm294.37-2.96c2.8 10.71 8.62 21.34 21.82 21.34 8.73 0 12.83-3 13.24-11.5.89-18.69-34.34-27.06-34.34-59.75 0-28.46 13.87-38.16 33.77-38.16 11.63 0 23.26 7.85 26.62 15.53V76c-3.36-9.36-10.27-20.08-19.8-20.08-7.55 0-11.17 3.6-11.51 10.72-.92 19.3 33.09 26.18 33.09 56.64 0 25.71-9 42-34.34 42-20.13 0-28.54-17.68-28.54-17.68zm-2.95-29.4c0-33.06-1.23-58.67-34-58.67s-36.45 33.23-36.45 62.73c0 37.54 5.56 74.1 39.71 74.1 19.34 0 30.18-17.68 30.18-17.68v-31.08s-8.4 21.23-24.62 21.23c-11.32 0-17.65-10.58-17.65-27.83h42c.07-3.49.83-13.92.83-22.8zm-42.78-3.22c0-13.9 1.94-24.9 9.6-24.9 8.25 0 10.17 12.31 10.17 24.94z"></path><path class="cls-1" d="M221.79 0l-37 33.52s5.88 14.91 5.88 40.66v65.18c0 18.45 10.74 25.93 25.9 25.93 10.43 0 17.28-2.83 23.6-8.49v-27.63c-2.51 4.56-5.31 7-9.72 7-4.67 0-8.69-3.38-8.69-10.79V58.2h18.44V30h-18.41z"></path></svg>
    </div>
  <h3 id="head_text">HTML formatting tool for web production</h3>
</div>
	<br>
	<h1 style="text-align:center;">Upload files</h1>
	<p style="margin-left:120px;"><a href="https://docs.motherjones.com/2018/06/29/online-web-production-tool/">Read the docs</a></p>
	<div id="upload_box">
		<form class="go_left" action="<?php __FILE__ ?>" id="add_file"  method="post" enctype="multipart/form-data">
			<table align="center" cellpadding="5" width="100%" style="border:1px solid #000;">
				<tr>
					<th>HTML</th>
					<th style="border-left:1px solid #000;">CSS</th>
					<th style="border-left:1px solid #000;">&nbsp</th>
				</tr>
				<tr>
					<td id="html_input" width="40%"><input type="file" name="production[]" /><input type="hidden" id="html_block" name="html_block" /></td>
					<td id="css_input" width="40%"><input type="file" name="css[]" /><input type="hidden" id="css_block" name="css_block" /></td>
					<td rowspan="3" valign="middle" width="100" style="border-left:1px solid #000;"><input type="button" id="add_one" class="go_left" value="More files" onclick="addFile();" /></td>
				</tr>
			</table>
			<br>
			<br>
			<div style="text-align:center;">
			<input type="submit" value="Submit" id="submit" />
			<div>
			<br>
			<br>
	<div style="text-align:right;margin-top:10px;padding:0;">
		<!-- <button id="refresh_page" onclick="refreshPage();">Reset the page</button>  -->
	</div>
		</form>
		<div id="errors">
			<?php
			 if($errors !== "") {
			 	print $errors . "<br />";
			 }
			?>
		</div>
		<div id="downloads">
			<?php
				if($downloads !== "" && $downloads != null) {
					print "<h3>Download files</h3>";
					$count = count($downloads);
					for($i = 0; $i < $count; $i++) {
						print "<a href=\"/web-production/" . $downloads[$i] . "\" download>" . str_replace("web_production/", "", $downloads[$i]) . "</a><br />";
					}
				}
			?>
		</div>
	</div>
	<script>
		function refreshPage() {
			//window.loaction.href = "http://util.motherjones.com/web-production";
			window.location.replace("http://util.motherjones.com/web-production");
		}
	</script>
</body>
</html>
