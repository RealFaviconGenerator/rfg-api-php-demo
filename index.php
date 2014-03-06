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
		
		$(document).ready(function() {
			var img = new Image;
			img.src = '/demo_favicon.png';
			img.onload = function() {
				picData = getBase64Image(img);
				
				var params = {
					favicon_generation: {
						// Demo key. That's fine.
						api_key: "87d5cd739b05c00416c4a19cd14a8bb5632ea563",
						master_picture: {
							// No master picture, use selects it from RFG
							type: "no_picture",
							
							// Inline pic: you send the master picture along with the other parameters
//							type: "inline",
//							content: picData
	
							// Picture from URL: you send the URL of the master picture, which will be downloaded by RFG
//							type: "url",
//							url: "http://realfavicongenerator.net/demo_favicon.png"
						},
						files_location: {
							type: "root"
						},
						callback: {
							url: "http://localhost/back",
						}
					}
				};
				$('#json_params').val(JSON.stringify(params));
				
				$('#form_button').removeAttr('disabled');
			}
		});
	</script>
</html>
