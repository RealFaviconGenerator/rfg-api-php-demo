<?php

define('RFG_PACKAGE_URL',         'package_url');
define('RFG_COMPRESSION',         'compression');
define('RFG_HTML_CODE',           'html_code');
define('RFG_FILES_IN_ROOT',       'files_in_root');
define('RFG_FILES_PATH',          'files_path');
define('RFG_PREVIEW_PICTURE_URL', 'preview_picture_url');
define('RFG_CUSTOM_PARAMETER',    'custom_parameter');

define('RFG_FAVICON_PRODUCTION_PACKAGE_PATH',   'favicon_production_package_path');
define('RFG_FAVICON_COMPRESSED_PACKAGE_PATH',   'favicon_compressed_package_path');
define('RFG_FAVICON_UNCOMPRESSED_PACKAGE_PATH', 'favicon_uncompressed_package_path');
define('RFG_PREVIEW_PATH',                      'preview_path');


/**
 * Parse the JSON document sent back by RealFaviconGenerator. Example:
 * 
 * <code>
 * $response = parseFaviconGenerationResponse($_REQUEST['json_result']);
 * echo $response[RFG_PACKAGE_URL];
 * echo $response[RFG_COMPRESSION]; // true or false
 * echo $response[RFG_HTML_CODE];
 * echo $response[RFG_FILES_IN_ROOT]; // true or false
 * echo $response[RFG_FILES_PATH]; // Make sense only when $response[RFG_FILES_IN_ROOT] == false
 * echo $response[RFG_PREVIEW_PICTURE_URL];
 * echo $response[RFG_CUSTOM_PARAMETER];
 * </code>
 */
function parseFaviconGenerationResponse($json) {
	$output = array();
	
	if ($json == NULL) {
		throw new InvalidArgumentException("No response from RealFaviconGenerator");
	}
	
	$response = json_decode($json, true);
	
	if ($response == NULL) {
		throw new InvalidArgumentException("JSON could not be parsed");
	}
	
	$response = getParam($response, 'favicon_generation_result');
	$result = getParam($response, 'result');
	$status = getParam($result, 'status');
	
	if ($status != 'success') {
		$msg = getParam($result, 'error_message', false);
		$msg = $msg != NULL ? $msg : 'An error occured';
		throw new InvalidArgumentException($msg);
	}
	
	$favicon = getParam($response, 'favicon');
	$output[RFG_PACKAGE_URL] = getParam($favicon, 'package_url');
	$output[RFG_COMPRESSION] = getParam($favicon, 'compression') == 'true';
	$output[RFG_HTML_CODE] = getParam($favicon, 'html_code');
	
	$filesLoc = getParam($response, 'files_location');
	$output[RFG_FILES_IN_ROOT] = getParam($filesLoc, 'type') == 'root';
	if (! $output[RFG_FILES_IN_ROOT]) {
		$output[RFG_FILES_PATH] = getParam($filesLoc, 'path');
	}
	
	$output[RFG_PREVIEW_PICTURE_URL] = getParam($response, 'preview_picture_url', false);
	
	$output[RFG_CUSTOM_PARAMETER] = getParam($response, 'custom_parameter', false);
	
	return $output;
}

function getParam($params, $paramName, $throwIfNotFound = true) {
	if (isset($params[$paramName])) {
		return $params[$paramName];
	}
	else if ($throwIfNotFound) {
		throw new InvalidArgumentException("Cannot find parameter " . $paramName);
	}
}

/**
 * Download and extract the files referenced by the response sent back by RealFaviconGenerator. 
 * 
 * Warning: as this method does HTTP accesses, calling it can take a few seconds. Better invoke it
 * in an Ajax call, to not slow down the user experience.
 * 
 * For example:
 * 
 * <code>
 * $response = parseFaviconGenerationResponse($_REQUEST['json_result']);
 * $files = downloadAndUnpack($response);
 * echo $files[RFG_FAVICON_PRODUCTION_PACKAGE_PATH]; // Directory where the production favicon files are stored.
 *     // These are the files to deployed to the targeted web site. When the user asked for compression,
 *     // this is the path to the compressed folder. Else, this is the path to the regular files folder.
 * echo $files[RFG_FAVICON_COMPRESSED_PACKAGE_PATH]; // Directory where the compressed files are stored. Can be NULL.
 * echo $files[RFG_FAVICON_UNCOMPRESSED_PACKAGE_PATH]; // Directory where the uncompressed files are stored. Can be NULL.
 * echo $files[RFG_PREVIEW_PATH]; // Path to the preview picture
 * </code>
 */
function downloadAndUnpack($response, $outputDirectory = NULL) {
	$output = array();
	
	if ($outputDirectory == NULL) {
		$outputDirectory = sys_get_temp_dir();
	}
	
	if ($response[RFG_PACKAGE_URL] != NULL) {
		$packagePath = $outputDirectory . DIRECTORY_SEPARATOR . 'favicon_package.zip';
		downloadFile($packagePath, $response[RFG_PACKAGE_URL]);
		
		$zip = new ZipArchive();
		$r = $zip->open($packagePath);
		if ($r === TRUE) {
			$extractedPath = $outputDirectory . DIRECTORY_SEPARATOR . 'favicon_package';
			if (! file_exists($extractedPath)) {
				mkdir($extractedPath);
			}
			
			$zip->extractTo($extractedPath);
			$zip->close();
			
			if ($response[RFG_COMPRESSION]) {
				$output[RFG_FAVICON_COMPRESSED_PACKAGE_PATH]   = $extractedPath . DIRECTORY_SEPARATOR . 'compressed';
				$output[RFG_FAVICON_UNCOMPRESSED_PACKAGE_PATH] = $extractedPath . DIRECTORY_SEPARATOR . 'uncompressed';
				$output[RFG_FAVICON_PRODUCTION_PACKAGE_PATH]   = $output[RFG_FAVICON_COMPRESSED_PACKAGE_PATH];
			}
			else {
				$output[RFG_FAVICON_COMPRESSED_PACKAGE_PATH]   = NULL;
				$output[RFG_FAVICON_UNCOMPRESSED_PACKAGE_PATH] = $extractedPath;
				$output[RFG_FAVICON_PRODUCTION_PACKAGE_PATH]   = $output[RFG_FAVICON_UNCOMPRESSED_PACKAGE_PATH];
			}
		}
		else {
			throw new InvalidArgumentException('Cannot open package. Invalid Zip file?!');
		}
	}
	
	if ($response[RFG_PREVIEW_PICTURE_URL] != NULL) {
		$previewPath = $outputDirectory . DIRECTORY_SEPARATOR . 'favicon_preview.png';
		downloadFile($previewPath, $response[RFG_PREVIEW_PICTURE_URL]);
		$output[RFG_PREVIEW_PATH] = $previewPath;
	}
	
	return $output;
}

function downloadFile($localPath, $url) {
	$s = file_put_contents($localPath, file_get_contents($url));
	if (($s == NULL) || ($s == 0)) {
		throw new InvalidArgumentException("Cannot download file at " . $url);
	}
}
?>
