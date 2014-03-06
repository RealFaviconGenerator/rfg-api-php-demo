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
<h1>The favicon was generated</h1>

<?php if ($error != NULL) { ?>
<p>
	An error occured: <?php echo $error ?>.
</p>
<?php } else { ?>
<p>
	Package local path: <?php echo $files[RFG_FAVICON_PRODUCTION_PACKAGE_PATH] ?>
</p>
<p>
	To be placed in <?php echo ($response[RFG_FILES_IN_ROOT] ? 'root directory' : $response[RFG_FILES_PATH]) ?>.
</p>
<p>
	Favicon code:
</p>
<pre>
<?php echo htmlspecialchars($response[RFG_HTML_CODE]) ?>
</pre>

<h2>Preview</h2>

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
