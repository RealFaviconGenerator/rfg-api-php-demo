<?php
session_start();
?>

<html>
	<body>
		<h1>RealFaviconGenerator API demo</h1>
		
		<p>Generate a favicon:</p>
		
		<form method="post" action="http://realfavicongenerator.net/api/favicon_generator">
			<input type="hidden" name="json_params" id="json_params"/>
			<button type="submit" id="form_button" disabled="disabled">Submit</button>
		</form>
		
		<img src="demo_favicon.png" id="favicon_picture" style="display: none">
	</body>
	<script type="text/javascript" src="/jquery-1.11.0.min.js"></script>
	<script type="text/javascript">
		var picData = null;
		
		// See http://stackoverflow.com/questions/934012/get-image-data-in-javascript
		// Credits: Matthew Crumley
		function getBase64Image(img) {
		    // Create an empty canvas element
		    var canvas = document.createElement("canvas");
		    canvas.width = img.width;
		    canvas.height = img.height;
		    
		    // Copy the image contents to the canvas
		    var ctx = canvas.getContext("2d");
		    ctx.drawImage(img, 0, 0);
		
		    // Get the data-URL formatted image
		    // Firefox supports PNG and JPEG. You could check img.src to
		    // guess the original format, but be aware the using "image/jpg"
		    // will re-encode the image.
		    var dataURL = canvas.toDataURL("image/png");
		    
		    return dataURL.replace(/^data:image\/(png|jpg);base64,/, "");
		}
		
		$('#favicon_picture').load(function() {
			picData = getBase64Image(document.getElementById('favicon_picture'));
			
			var params = {
				favicon_generation: {
					api_key: "87d5cd739b05c00416c4a19cd14a8bb5632ea563",
					master_picture: {
						type: "no_picture",
//						type: "inline",
//						content: picData
//						type: "url",
//						url: "http://192.168.1.94/site/demo_favicon.png"
					},
					files_location: {
						type: "root"
					},
					callback: {
						url: "http://192.168.1.94/back",
					}
				}
			};
			$('#json_params').val(JSON.stringify(params));
			
			$('#form_button').removeAttr('disabled');
		});
	</script>
</html>
