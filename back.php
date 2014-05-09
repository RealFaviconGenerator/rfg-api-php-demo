<?php
require_once 'rfg_api_response.php';

$response = $_GET['json_result'];
$responseUrl = $_GET['json_result_url'];

$error = NULL;
$files = NULL;

if ($responseUrl != NULL) {
	// This operation can take a few seconds... better put this behind an Ajax request
	$response = file_get_contents($responseUrl);
}

if ($response != NULL) {
	try {
	  $response = new RFGApiResponse($response);
	  
	  // This call can take a few seconds... better put this behind an Ajax request
	  $response->downloadAndUnpack();
	}
	catch(Exception $e) {
	  $error = $e->getMessage();
	}
}
else {
	$error = 'No response from RealFaviconGenerator';
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Favicon generation result</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
   <div class="container">
      <h1>Generated favicon</h1>

<?php if ($error != NULL) { ?>
      <p>
      An error occured: <?php echo $error ?>.
      </p>
<?php } else { ?>
      <p>
      The files to be deployed are here: <code><?php echo $response->getPackagePath() ?></code>.
      </p>
      <p>
      These files should be moved to <code>&lt;the web site&gt;<?php echo $response->getFilesLocation() ?></code>.
      </p>
      <p>
      The following HTML code should be inserted in the <code>&lt;head&gt;</code> section of the web pages:
      </p>
<pre>
<?php echo htmlspecialchars($response->getHtmlCode()) ?>
</pre>

      <h2>Preview</h2>

      <p>
      In this demo, the preview is displayed directly from RealFaviconGenerator (ie. the URL starts with http://realfavicongenerator.net).
      However, keep in mind that all files are automatically removed after a few hours. So you should rather download the picture, stores it locally,
      make it available though HTTP and use this local version instead.
      </p>

<?php
if ($response->getPreviewUrl() != NULL) {
?>
      <img class="img-responsive img-thumbnail" src="<?php echo $response->getPreviewUrl() ?>">
<?php
}
else {
?>
      <p>No preview available</p>
<?php 
}
?>
      <h2>Misc</h2>
      
      <p>
      The package was <?php echo $response->isCompressed() ? '' : 'not ' ?>compressed.
      </p>
      
      <p>
      <?php if ($response->getCustomParameter() != NULL) { ?>
      The customer callback parameter: <code><?php echo $response->getCustomParameter() ?></code>.
      <?php } else { ?>
      There was no callback parameter.
      <?php } ?>
      </p>
      
<?php } ?>

      <h2>Next</h2>

      <ul>
        <li><a href="/">Run the demo again</a></li>
        <li><a href="http://realfavicongenerator.net/api">Study the API</a></li>
        <li><a href="https://github.com/RealFaviconGenerator/rfg-api-php-demo">Clone this demo project</a></li>
        <li>Did you code a great project? <a href="mailto:contact@realfavicongenerator.net">Tell us!</a></li>      
      </ul>
    </div>
  </body>
</html>
