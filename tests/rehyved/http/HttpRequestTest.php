<?php

namespace Rehyved\http;

use PHPUnit\Framework\TestCase;

class HttpRequestTest extends TestCase {

	public function testGet() {
		$response = HttpRequest::create( "https://httpbin.org" )->get( "get" );
		$this->assertEquals( 200, $response->getHttpStatus() );
		
		$this->assertEquals( "https://httpbin.org/get", $response->getUrl() );
		$this->assertNotEmpty( $response->getContentRaw() );
		
		// See if object deserialization works as expected:
		$result = $response->getContent();
		$this->assertEquals( "https://httpbin.org/get", $result->url );
	}

	public function testGetParameter() {
		$response = HttpRequest::create( "https://httpbin.org" )->parameter( "param1", "value1" )->get( "get" );
		$this->assertEquals( 200, $response->getHttpStatus() );
		
		$this->assertEquals( "https://httpbin.org/get?param1=value1", $response->getUrl() );
		$this->assertNotEmpty( $response->getContentRaw() );
		
		// See if object deserialization works as expected:
		$result = $response->getContent();
		$this->assertEquals( "https://httpbin.org/get?param1=value1", $result->url );
		$this->assertEquals( "value1", $result->args->param1 );
	}

	public function testMultipleGetParameters() {
		$response = HttpRequest::create( "https://httpbin.org" )->parameters( array(
				"param1" => "value1",
				"param 2" => "value2" 
		) )->get( "get" );
		$this->assertEquals( 200, $response->getHttpStatus() );
		
		$this->assertEquals( "https://httpbin.org/get?param1=value1&param+2=value2", $response->getUrl() );
		$this->assertNotEmpty( $response->getContentRaw() );
		
		// See if object deserialization works as expected:
		$result = $response->getContent();
		$this->assertEquals( "https://httpbin.org/get?param1=value1&param+2=value2", $result->url );
		$this->assertEquals( "value1", $result->args->param1 );
		$this->assertEquals( "value2", $result->args->{"param 2"} );
	}

	public function testPostWithQueryFormat() {
		$response = HttpRequest::create( "https://httpbin.org" )->post( "post", "param1=value1&param2=value2" );
		$this->assertEquals( 200, $response->getHttpStatus() );
		
		$this->assertEquals( "https://httpbin.org/post", $response->getUrl() );
		$this->assertNotEmpty( $response->getContentRaw() );
		
		// See if object deserialization works as expected:
		$result = $response->getContent();
		$this->assertEquals( "https://httpbin.org/post", $result->url );
		$this->assertEquals( "value1", $result->form->param1 );
		$this->assertEquals( "value2", $result->form->param2 );
	}

	public function testPostWithNoFormatShouldUrlEncode() {
		$response = HttpRequest::create( "https://httpbin.org" )->post( "post", array(
				"param1" => "value1",
				"param2" => "value2" 
		) );
		$this->assertEquals( 200, $response->getHttpStatus() );
		
		$this->assertEquals( "https://httpbin.org/post", $response->getUrl() );
		$this->assertNotEmpty( $response->getContentRaw() );
		
		// See if object deserialization works as expected:
		$result = $response->getContent();
		$this->assertEquals( "https://httpbin.org/post", $result->url );
		$this->assertEquals( "application/x-www-form-urlencoded", $result->headers->{"Content-Type"} );
		$this->assertEquals( "value1", $result->form->param1 );
		$this->assertEquals( "value2", $result->form->param2 );
	}

	public function testPostWithJsonFormat() {
		$response = HttpRequest::create( "https://httpbin.org" )->contentType( "application/json" )->post( "post", array(
				"param1" => "value1",
				"param2" => "value2" 
		) );
		$this->assertEquals( 200, $response->getHttpStatus() );
		
		$this->assertEquals( "https://httpbin.org/post", $response->getUrl() );
		$this->assertNotEmpty( $response->getContentRaw() );
		
		// See if object deserialization works as expected:
		$result = $response->getContent();
		$this->assertEquals( "https://httpbin.org/post", $result->url );
		$this->assertEquals( "value1", $result->json->param1 );
		$this->assertEquals( "value2", $result->json->param2 );
	}

	public function testPostWithMultipartFormatShouldAddBoundary() {
		$response = HttpRequest::create( "https://httpbin.org" )->contentType( "multipart/form-data;" )->post( "post", array(
				"param1" => "value1",
				"param2" => "value2" 
		) );
		$this->assertEquals( 200, $response->getHttpStatus() );
		
		$this->assertEquals( "https://httpbin.org/post", $response->getUrl() );
		$this->assertNotEmpty( $response->getContentRaw() );
		
		// See if object deserialization works as expected:
		$result = $response->getContent();
		$this->assertEquals( "https://httpbin.org/post", $result->url );
		
		// Is expected content type:
		$this->assertTrue( stripos( $result->headers->{"Content-Type"}, "multipart/form-data" ) !== false );
		// Assert that a boundary was eventually set:
		$this->assertGreaterThan( 0, preg_match( '/boundary="([^\"]+)"/is', $result->headers->{"Content-Type"} ) );
		
		$this->assertEquals( "value1", $result->form->param1 );
		$this->assertEquals( "value2", $result->form->param2 );
	}

	public function testGetError() {
		$clientErrorResponse = HttpRequest::create( "https://httpbin.org" )->get( "status/400" );
		$this->assertEquals( 400, $clientErrorResponse->getHttpStatus() );
		$this->assertEmpty( $clientErrorResponse->getContent() );
		
		$serverErrorResponse = HttpRequest::create( "https://httpbin.org" )->get( "status/500" );
		$this->assertEquals( 500, $serverErrorResponse->getHttpStatus() );
		$this->assertEmpty( $serverErrorResponse->getContent() );
	}

	public function testSetHeader() {
		$response = HttpRequest::create( "https://httpbin.org" )->header( "Testheader", "testvalue" )->get( "headers" );
		$this->assertEquals( 200, $response->getHttpStatus() );
		
		$content = $response->getContent();
		$this->assertNotEmpty( $content );
		$this->assertNotEmpty( $content->headers );
		
		$this->assertEquals( "testvalue", $content->headers->Testheader );
	}

	public function testSetMultipleHeaders() {
		$response = HttpRequest::create( "https://httpbin.org" )->headers( array(
				"Testheader" => "testvalue",
				"Testheader2" => "testvalue2" 
		) )->get( "headers" );
		$this->assertEquals( 200, $response->getHttpStatus() );
		
		$content = $response->getContent();
		$this->assertNotEmpty( $content );
		$this->assertNotEmpty( $content->headers );
		
		$this->assertEquals( "testvalue", $content->headers->Testheader );
		$this->assertEquals( "testvalue2", $content->headers->Testheader2 );
	}

	public function testRedirect() {
		$response = HttpRequest::create( "https://httpbin.org" )->get( "redirect/2" );
		$this->assertEquals( 200, $response->getHttpStatus() );
		$this->assertEquals( "https://httpbin.org/get", $response->getUrl() );
		
		// Check if we have been redirected to the correct page and the body is not empty and as expected
		$result = $response->getContent();
		$this->assertEquals( "https://httpbin.org/get", $result->url );
	}

	public function testSendCookie() {
		$response = HttpRequest::create( "https://httpbin.org" )->cookie( "testcookie", "testcookievalue" )->get( "cookies" );
		$this->assertEquals( 200, $response->getHttpStatus() );
		$this->assertEquals( "https://httpbin.org/cookies", $response->getUrl() );
		
		$result = $response->getContent();
		$this->assertEquals( "testcookievalue", $result->cookies->testcookie );
	}

	public function testReceiveCookie() {
		$response = HttpRequest::create( "https://httpbin.org/" )->get( "cookies/set?k1=v1&k2=v2" );
		$this->assertEquals( 200, $response->getHttpStatus() );
		
		$result = $response->getContent();
		$this->assertEquals( "v1", $response->getCookie( "k1" )->getValue() );
		$this->assertEquals( "v2", $response->getCookie( "k2" )->getValue() );
		
		$cookies = $response->getCookies();
		$this->assertEquals( "v1", $cookies["k1"]->getValue() );
		$this->assertEquals( "v2", $cookies["k2"]->getValue() );
	}
}

?>