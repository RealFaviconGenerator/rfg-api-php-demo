<?php
require_once 'rfg_api_response.php';

class RFGTest extends PHPUnit_Framework_TestCase {

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParseFaviconGenerationResponse_NoJson() {
		$response = new RFGApiResponse(NULL);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParseFaviconGenerationResponse_InvalidJson() {
		$response = new RFGApiResponse("this is not JSON!");
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParseFaviconGenerationResponse_InvalidFormat() {
		$response = new RFGApiResponse('{ "bad": "format" }');
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Oops!!
	 */
	public function testParseFaviconGenerationResponse_ServerReturnsAnError() {
		$response = new RFGApiResponse('{ "favicon_generation_result": { ' .
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
		$response = new RFGApiResponse($json);
		$this->assertEquals('http://realfavicongenerator.net/files/0156213fd1232131.zip', $response->getPackageUrl());
		$this->assertEquals('Html code here...', $response->getHtmlCode());
		$this->assertTrue($response->isCompressed());
		$this->assertFalse($response->isFilesInRoot());
		$this->assertEquals('/path/to/icons', $response->getFilesLocation());
		$this->assertEquals('http://realfavicongenerator.net/files/preview_pic.png', $response->getPreviewUrl());
		$this->assertEquals('ref=157539001', $response->getCustomParameter());
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
		$response = new RFGApiResponse($json);
		$this->assertEquals('http://realfavicongenerator.net/files/0156213fd1232131.zip', $response->getPackageUrl());
		$this->assertFalse($response->isCompressed());
		$this->assertEquals('Html code here...', $response->getHtmlCode());
		$this->assertTrue($response->isFilesInRoot());
		$this->assertEquals('/', $response->getFilesLocation());
		$this->assertNull($response->getPreviewUrl());
		$this->assertNull($response->getCustomParameter());
	}
	
}
?>
