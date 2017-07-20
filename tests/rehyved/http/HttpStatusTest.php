<?php

namespace Rehyved\Http;

use PHPUnit\Framework\TestCase;

class HttpStatusTest extends TestCase {
	public function testIsInformational(){
		$this->assertTrue(HttpStatus::isInformational(100));
		$this->assertTrue(HttpStatus::isInformational(101));
		$this->assertTrue(HttpStatus::isInformational(199));
		
		$this->assertFalse(HttpStatus::isInformational(99));
		$this->assertFalse(HttpStatus::isInformational(200));
		$this->assertFalse(HttpStatus::isInformational(201));
	}
	
	public function testIsSuccessful(){
		$this->assertTrue(HttpStatus::isSuccessful(200));
		$this->assertTrue(HttpStatus::isSuccessful(201));
		$this->assertTrue(HttpStatus::isSuccessful(299));
		
		$this->assertFalse(HttpStatus::isSuccessful(199));
		$this->assertFalse(HttpStatus::isSuccessful(300));
		$this->assertFalse(HttpStatus::isSuccessful(301));
	}
	
	public function testIsRedirection(){
		$this->assertTrue(HttpStatus::isRedirection(300));
		$this->assertTrue(HttpStatus::isRedirection(301));
		$this->assertTrue(HttpStatus::isRedirection(399));
		
		$this->assertFalse(HttpStatus::isRedirection(299));
		$this->assertFalse(HttpStatus::isRedirection(400));
		$this->assertFalse(HttpStatus::isRedirection(401));
	}
	
	public function testIsClientError(){
		$this->assertTrue(HttpStatus::isClientError(400));
		$this->assertTrue(HttpStatus::isClientError(401));
		$this->assertTrue(HttpStatus::isClientError(499));
		
		$this->assertFalse(HttpStatus::isClientError(399));
		$this->assertFalse(HttpStatus::isClientError(500));
		$this->assertFalse(HttpStatus::isClientError(501));
	}
	
	public function testIsServerError(){
		$this->assertTrue(HttpStatus::isServerError(500));
		$this->assertTrue(HttpStatus::isServerError(501));
		$this->assertTrue(HttpStatus::isServerError(599));
		
		$this->assertFalse(HttpStatus::isServerError(499));
		$this->assertFalse(HttpStatus::isServerError(600));
		$this->assertFalse(HttpStatus::isServerError(601));
	}
	
	public function testIsError(){
		$this->assertTrue(HttpStatus::isError(400));
		$this->assertTrue(HttpStatus::isError(401));
		$this->assertTrue(HttpStatus::isError(499));
		$this->assertTrue(HttpStatus::isError(400));
		$this->assertTrue(HttpStatus::isError(401));
		$this->assertTrue(HttpStatus::isError(499));
		
		$this->assertFalse(HttpStatus::isError(399));
		$this->assertFalse(HttpStatus::isError(600));
		$this->assertFalse(HttpStatus::isError(601));
	}
}

?>