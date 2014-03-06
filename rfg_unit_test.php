<?php
require_once 'rfg.php';

class RFGTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParseFaviconGenerationResponse_NoJson() {
		parseFaviconGenerationResponse(NULL);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParseFaviconGenerationResponse_InvalidJson() {
		parseFaviconGenerationResponse("this is not JSON!");
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParseFaviconGenerationResponse_InvalidFormat() {
		parseFaviconGenerationResponse('{ "bad": "format" }');
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Oops!!
	 */
	public function testParseFaviconGenerationResponse_ServerReturnsAnError() {
		parseFaviconGenerationResponse('{ "favicon_generation_result": { ' .
			'"result": {' .
				'"status": "error", '.
				'"error_message": "Oops!!"' .
			'} } }');
	}

	public function testParseFaviconGenerationResponse_FilesNotInRoot() {
		$json = '{ "favicon_generation_result": {' .
			'"result": {' .
				'"status": "success"' .
			'},' .
			'"favicon": {' .
				'"package_url": "http://realfavicongenerator.net/files/0156213fd1232131.zip",' .
				'"html_code": "Html code here...",' .
				'"compression": "true"' .
			'},' .
			'"files_location": {' .
				'"type": "path",' .
				'"path": "/path/to/icons"' .
			'},' .
			'"preview_picture_url": "http://realfavicongenerator.net/files/preview_pic.png",' .
			'"custom_parameter": "ref=157539001"' .
		'} }';
		$out = parseFaviconGenerationResponse($json);
		$this->assertEquals('http://realfavicongenerator.net/files/0156213fd1232131.zip', $out[RFG_PACKAGE_URL]);
		$this->assertTrue($out[RFG_COMPRESSION]);
		$this->assertEquals('Html code here...', $out[RFG_HTML_CODE]);
		$this->assertFalse($out[RFG_FILES_IN_ROOT]);
		$this->assertEquals('/path/to/icons', $out[RFG_FILES_PATH]);
		$this->assertEquals('http://realfavicongenerator.net/files/preview_pic.png', $out[RFG_PREVIEW_PICTURE_URL]);
		$this->assertEquals('ref=157539001', $out[RFG_CUSTOM_PARAMETER]);
	}

	public function testParseFaviconGenerationResponse_FilesInRoot() {
		$json = '{ "favicon_generation_result": {' .
			'"result": {' .
				'"status": "success"' .
			'},' .
			'"favicon": {' .
				'"package_url": "http://realfavicongenerator.net/files/0156213fd1232131.zip",' .
				'"html_code": "Html code here...",' .
				'"compression": "false"' .
			'},' .
			'"files_location": {' .
				'"type": "root"' .
			'}' .
		'} }';
		$out = parseFaviconGenerationResponse($json);
		$this->assertEquals('http://realfavicongenerator.net/files/0156213fd1232131.zip', $out[RFG_PACKAGE_URL]);
		$this->assertFalse($out[RFG_COMPRESSION]);
		$this->assertEquals('Html code here...', $out[RFG_HTML_CODE]);
		$this->assertTrue($out[RFG_FILES_IN_ROOT]);
		$this->assertFalse(isset($out[RFG_FILES_PATH]));
		$this->assertNull($out[RFG_PREVIEW_PICTURE_URL]);
		$this->assertNull($out[RFG_CUSTOM_PARAMETER]);
	}
	
}
?>
