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

class RFGApiResponse {
	
	private $params = array();
	
	public function RFGApiResponse($json) {
		if ($json == NULL) {
			throw new InvalidArgumentException("No response from RealFaviconGenerator");
		}
		
		$response = json_decode($json, true);
		
		if ($response == NULL) {
			throw new InvalidArgumentException("JSON could not be parsed");
		}
		
		$response = $this->getParam($response, 'favicon_generation_result');
		$result = $this->getParam($response, 'result');
		$status = $this->getParam($result, 'status');
		
		if ($status != 'success') {
			$msg = $this->getParam($result, 'error_message', false);
			$msg = $msg != NULL ? $msg : 'An error occured';
			throw new InvalidArgumentException($msg);
		}
		
		$favicon = $this->getParam($response, 'favicon');
		$this->params[RFG_PACKAGE_URL] = $this->getParam($favicon, 'package_url');
		$this->params[RFG_COMPRESSION] = $this->getParam($favicon, 'compression') == 'true';
		$this->params[RFG_HTML_CODE] = $this->getParam($favicon, 'html_code');
		
		$filesLoc = $this->getParam($response, 'files_location');
		$this->params[RFG_FILES_IN_ROOT] = $this->getParam($filesLoc, 'type') == 'root';
		$this->params[RFG_FILES_PATH] = $this->params[RFG_FILES_IN_ROOT] ? '/' : $this->getParam($filesLoc, 'path');
		
		$this->params[RFG_PREVIEW_PICTURE_URL] = $this->getParam($response, 'preview_picture_url', false);
		
		$this->params[RFG_CUSTOM_PARAMETER] = $this->getParam($response, 'custom_parameter', false);
    }

	/**
	 * For example: <code>"http://realfavicongenerator.net/files/1234f5d2s34f3ds2/package.zip"</code>
	 */
	public function getPackageUrl() {
		return $this->params[RFG_PACKAGE_URL];
	}
	
	/**
	 * For example: <code>"&lt;link ..."</code>
	 */
	public function getHtmlCode() {
		return $this->params[RFG_HTML_CODE];
	}

	/**
	 * <code>true</code> if the user chose to compress the pictures, <code>false</code> otherwise.
	 */	
	public function isCompressed() {
		return $this->params[RFG_COMPRESSION];
	}

	/**
	 * <code>true</code> if the favicon files are to be stored in the root directory of the target web site, <code>false</code> otherwise.
	 */
	public function isFilesInRoot() {
		return $this->params[RFG_FILES_IN_ROOT];
	}

	/**
	 * Indicate where the favicon files should be stored in the target web site. For example: <code>"/"</code>, <code>"/path/to/icons"</code>.
	 */	
	public function getFilesLocation() {
		return $this->params[RFG_FILES_PATH];
	}

	/**
	 * For example: <code>"http://realfavicongenerator.net/files/1234f5d2s34f3ds2/preview.png"</code>
	 */	
	public function getPreviewUrl() {
		return $this->params[RFG_PREVIEW_PICTURE_URL];
	}
	
	/**
	 * Return the customer parameter, as it was transmitted during the invocation of the API.
	 */
	public function getCustomParameter() {
		return $this->params[RFG_CUSTOM_PARAMETER];
	}
	
	private function getParam($params, $paramName, $throwIfNotFound = true) {
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
	 */
	public function downloadAndUnpack($outputDirectory = NULL) {
		if ($outputDirectory == NULL) {
			$outputDirectory = sys_get_temp_dir();
		}
		
		if ($this->getPackageUrl() != NULL) {
			$packagePath = $outputDirectory . DIRECTORY_SEPARATOR . 'favicon_package.zip';
			$this->downloadFile($packagePath, $this->getPackageUrl());
			
			$zip = new ZipArchive();
			$r = $zip->open($packagePath);
			if ($r === TRUE) {
				$extractedPath = $outputDirectory . DIRECTORY_SEPARATOR . 'favicon_package';
				if (! file_exists($extractedPath)) {
					mkdir($extractedPath);
				}
				
				$zip->extractTo($extractedPath);
				$zip->close();
				
				if ($this->isCompressed()) {
					$this->params[RFG_FAVICON_COMPRESSED_PACKAGE_PATH]   = $extractedPath . DIRECTORY_SEPARATOR . 'compressed';
					$this->params[RFG_FAVICON_UNCOMPRESSED_PACKAGE_PATH] = $extractedPath . DIRECTORY_SEPARATOR . 'uncompressed';
					$this->params[RFG_FAVICON_PRODUCTION_PACKAGE_PATH]   = $this->params[RFG_FAVICON_COMPRESSED_PACKAGE_PATH];
				}
				else {
					$this->params[RFG_FAVICON_COMPRESSED_PACKAGE_PATH]   = NULL;
					$this->params[RFG_FAVICON_UNCOMPRESSED_PACKAGE_PATH] = $extractedPath;
					$this->params[RFG_FAVICON_PRODUCTION_PACKAGE_PATH]   = $this->params[RFG_FAVICON_UNCOMPRESSED_PACKAGE_PATH];
				}
			}
			else {
				throw new InvalidArgumentException('Cannot open package. Invalid Zip file?!');
			}
		}
		
		if ($this->getPreviewUrl() != NULL) {
			$previewPath = $outputDirectory . DIRECTORY_SEPARATOR . 'favicon_preview.png';
			$this->downloadFile($previewPath, $this->getPreviewUrl());
			$this->params[RFG_PREVIEW_PATH] = $previewPath;
		}
	}

 	/**
	 * Directory where the compressed files are stored. Method <code>downloadAndUnpack</code> must be called first in order to populate this field. 
	 * Can be <code>NULL</code>.
	 */
 	public function getCompressedPackagePath() {
		return $this->params[RFG_FAVICON_COMPRESSED_PACKAGE_PATH];
	}

 	/**
	 * Directory where the uncompressed files are stored. Method <code>downloadAndUnpack</code> must be called first in order to populate this field. 
	 * Can be <code>NULL</code>.
	 */
	public function getUncompressedPackagePath() {
		return $this->params[RFG_FAVICON_UNCOMPRESSED_PACKAGE_PATH];
	}

 	/**
	 * Directory where the production favicon files are stored.
	 * These are the files to deployed to the targeted web site. When the user asked for compression,
	 * this is the path to the compressed folder. Else, this is the path to the regular files folder.
	 * Method <code>downloadAndUnpack</code> must be called first in order to populate this field.
	 */
	public function getProductionPackagePath() {
		return $this->params[RFG_FAVICON_PRODUCTION_PACKAGE_PATH];
	}
	
	/**
	 * Path to the preview picture.
	 */
	public function getPreviewPath() {
		return $this->params[RFG_PREVIEW_PATH];
	}
	
	private function downloadFile($localPath, $url) {
		$content = file_get_contents($url);
		if ($content === FALSE) {
			throw new InvalidArgumentException("Cannot download file at " . $url);
		}
		$ret = file_put_contents($localPath, $content);
		if ($ret === FALSE) {
			throw new InvalidArgumentException("Cannot store content of " . $url . " to " . $localPath);
		}
	}
	
}
?>
