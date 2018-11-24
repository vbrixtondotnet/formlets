<?php
include_once('../vendor/autoload.php');

use google\appengine\api\cloud_storage\CloudStorageTools;

$options = ['gs_bucket_name' => 'formletsdev.appspot.com'];
$upload_url = CloudStorageTools::createUploadUrl('/fileaccept.php', $options);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
	<title>Form Upload Test</title>
	<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['level'];?>static/css/form.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
</head>
<body>
	<div style="text-align: center;">
		<div style="width:400px;margin-left: auto;margin-right: auto;margin-top: 80px">
			<form id="formtest" method="POST" action="/fileshow.php" enctype="multipart/form-data">
				<input type="file" id="fileInput" name="file" onchange="uploadFile()" />
				<input type="hidden" id="fileInput_result" name="file_result" />
				<br><br>
				Progress: <div id="progress">0%</div>
				<br><br>
				<input type="submit" id="submitbtn" value="Submit" disabled />
			</form>
		</div>
	</div>

	<script type="text/javascript">
		function uploadFile() {
			var file = document.getElementById("fileInput");
			var file = file.files[0];

			var formData = new FormData();
			formData.append('files[]', file, file.name);

			// Set up the request.
			var xhr = new XMLHttpRequest();

			// Open the connection.
			xhr.open('POST', '<?php echo $upload_url; ?>', true);

			// Set up a handler for when the request finishes.
			xhr.onload = function () {
			  if (xhr.status === 200) {
			    // File(s) uploaded.
			  } else {
			    alert('An error occurred!');
			  }
			};

			if(xhr.upload) {
				// progress bar
				xhr.upload.addEventListener("progress", function(e) {
					var pc = parseInt((e.loaded / e.total * 100));
					document.getElementById('progress').innerHTML=pc+'%';
				}, false);
			}

			xhr.onreadystatechange = function() {
				if (xhr.readyState == XMLHttpRequest.DONE) {
			        obj = JSON.parse(xhr.responseText);
			        if(obj.error) {
			        	alert(obj.error);
			        } else {
			        	document.getElementById('fileInput_result').value = obj.file;
			        	document.getElementById('submitbtn').disabled = false;
			        }
			    }
			}

			// Send the Data.
			xhr.send(formData);
		}
	</script>
</body>
</html>
