<?php
require_once 'rfg.php';

$response = $_GET['json_result'];

$error = NULL;
$files = NULL;
try {
	$response = parseFaviconGenerationResponse($response);
	$files = downloadAndUnpack($response);
}
catch(Exception $e) {
	$error = $e->getMessage();
}

?>
<h1>Your generated favicon</h1>

<?php if ($error != NULL) { ?>
<p>
	An error occured: <?php echo $error ?>.
</p>
<?php } else { ?>
<p>
	The files to be deployed are here: <code><?php echo $files[RFG_FAVICON_PRODUCTION_PACKAGE_PATH] ?></code>.
</p>
<p>
	These files should be moved to <code>&lt;your web site&gt;<?php echo ($response[RFG_FILES_IN_ROOT] ? '/' : $response[RFG_FILES_PATH]) ?></code>.
</p>
<p>
	The following HTML code should be inserted in the <code>&lt;head&gt;</code> section of your web pages:
</p>
<pre>
<?php echo htmlspecialchars($response[RFG_HTML_CODE]) ?>
</pre>

<h2>Preview</h2>

<p>
	In this demo, the preview is displayed directly from RealFaviconGenerator (ie. the URL starts with http://realfavicongenerator.net).
	However, keep in mind that all files are automatically removed after a few hours. So you should rather download the picture, stores it locally,
	make it available though HTTP and use this local version instead.
</p>

<?php
	if ($response[RFG_PREVIEW_PICTURE_URL] != NULL) {
?>
	<img src="<?php echo $response[RFG_PREVIEW_PICTURE_URL] ?>">
<?php
	}
	else {
?>
	<p>No preview available</p>
<?php 
	}
} ?>
