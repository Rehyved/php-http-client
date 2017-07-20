<?php

namespace Rehyved\Http;

class HttpStatus {

	// Informational 1xx:
	const INFORMATIONAL = 100;

	const CONTINUE = 100;

	const SWITCHING_PROTOCOLS = 101;

	// Successful 2xx:
	const SUCCESSFUL = 200;

	const OK = 200;

	const CREATED = 201;

	const ACCEPTED = 202;

	const NON_AUTHORITATIVE_INFORMATION = 203;

	const NO_CONTENT = 204;

	const RESET_CONTENT = 205;

	const PARTIAL_CONTENT = 206;

	// Redirection 3xx:
	const REDIRECTION = 300;

	const MULTIPLE_CHOICES = 300;

	const MOVED_PERMANENTLY = 301;

	const FOUND = 302;

	const SEE_OTHER = 303;

	const NOT_MODIFIED = 304;

	const USE_PROXY = 305;

	// Code 306 was used in a previous HTTP specification but no longer used but kept reserved.
	const TEMPORARY_REDIRECT = 307;

	// Client Error 4xx:
	const CLIENT_ERROR = 400;

	const BAD_REQUEST = 400;

	const UNAUTHORIZED = 401;

	const PAYMENT_REQUIRED = 402;

	const FORBIDDEN = 403;

	const NOT_FOUND = 404;

	const METHOD_NOT_ALLOWED = 405;

	const NOT_ACCEPTABLE = 406;

	const PROXY_AUTHENTICATION_REQUIRED = 407;

	const REQUEST_TIMEOUT = 408;

	const CONFLICT = 409;

	const GONE = 410;

	const LENGTH_REQUIRED = 411;

	const PRECONDITION_FAILED = 412;

	const REQUEST_ENTITY_TOO_LARGE = 413;

	const REQUEST_URI_TOO_LONG = 414;

	const UNSUPPORTED_MEDIA_TYPE = 415;

	const REQUESTED_RANGE_NOT_SATISFIABLE = 416;

	const EXPECTATION_FAILED = 417;

	// Server Error 5xx:
	const SERVER_ERROR = 500;

	const INTERNAL_SERVER_ERROR = 500;

	const NOT_IMPLEMENTED = 501;

	const BAD_GATEWAY = 502;

	const SERVICE_UNAVAILABLE = 503;

	const GATEWAY_TIMEOUT = 504;

	const HTTP_VERSION_NOT_SUPPORTED = 505;

	const SERVER_ERROR_END = 600;

	const REASON_PHRASES = array(
			HttpStatus::CONTINUE => "Continue",
			HttpStatus::SWITCHING_PROTOCOLS => "Switching Protocols",
			
			HttpStatus::OK => "OK",
			HttpStatus::CREATED => "Created",
			HttpStatus::ACCEPTED => "Accepted",
			HttpStatus::NON_AUTHORITATIVE_INFORMATION => "Non-Authoritative Information",
			HttpStatus::NO_CONTENT => "No Content",
			HttpStatus::RESET_CONTENT => "Reset Content",
			HttpStatus::PARTIAL_CONTENT => "Partial Content",
			
			HttpStatus::MULTIPLE_CHOICES => "Multiple Choices",
			HttpStatus::MOVED_PERMANENTLY => "Moved Permanently",
			HttpStatus::FOUND => "Found",
			HttpStatus::SEE_OTHER => "See Other",
			HttpStatus::NOT_MODIFIED => "Not Modified",
			HttpStatus::USE_PROXY => "Use Proxy",
			HttpStatus::TEMPORARY_REDIRECT => "Temporary Redirect",
			
			HttpStatus::BAD_REQUEST => "Bad Request",
			HttpStatus::UNAUTHORIZED => "Unauthorized",
			HttpStatus::PAYMENT_REQUIRED => "Payment Required",
			HttpStatus::FORBIDDEN => "Forbidden",
			HttpStatus::NOT_FOUND => "Not Found",
			HttpStatus::METHOD_NOT_ALLOWED => "Method Not Allowed",
			HttpStatus::NOT_ACCEPTABLE => "Not Acceptable",
			HttpStatus::PROXY_AUTHENTICATION_REQUIRED => "Proxy Authentication Required",
			HttpStatus::REQUEST_TIMEOUT => "Request Time-out",
			HttpStatus::CONFLICT => "Conflict",
			HttpStatus::GONE => "Gone",
			HttpStatus::LENGTH_REQUIRED => "Length Required",
			HttpStatus::PRECONDITION_FAILED => "Precondition Failed",
			HttpStatus::REQUEST_ENTITY_TOO_LARGE => "Request Entity Too Large",
			HttpStatus::REQUEST_URI_TOO_LONG => "Request-URI Too Long",
			HttpStatus::UNSUPPORTED_MEDIA_TYPE => "Unsupported Media Type",
			HttpStatus::REQUESTED_RANGE_NOT_SATISFIABLE => "Requested Range Not Satisfiable",
			HttpStatus::EXPECTATION_FAILED => "Expectation Failed",
			
			HttpStatus::INTERNAL_SERVER_ERROR => "Internal Server Error",
			HttpStatus::NOT_IMPLEMENTED => "Not Implemented",
			HttpStatus::BAD_GATEWAY => "Bad Gateway",
			HttpStatus::SERVICE_UNAVAILABLE => "Service Unavailable",
			HttpStatus::GATEWAY_TIMEOUT => "Gateway Timeout",
			HttpStatus::HTTP_VERSION_NOT_SUPPORTED => "HTTP Version Not Supportedø" 
	
	);

	public static function isInformational( int $statusCode ): bool {
		return ( $statusCode >= HttpStatus::INFORMATIONAL ) && ( $statusCode < HttpStatus::SUCCESSFUL );
	}

	public static function isSuccessful( int $statusCode ): bool {
		return ( $statusCode >= HttpStatus::SUCCESSFUL ) && ( $statusCode < HttpStatus::REDIRECTION );
	}

	public static function isRedirection( int $statusCode ): bool {
		return ( $statusCode >= HttpStatus::REDIRECTION ) && ( $statusCode < HttpStatus::CLIENT_ERROR );
	}

	public static function isClientError( int $statusCode ): bool {
		return ( $statusCode >= HttpStatus::CLIENT_ERROR ) && ( $statusCode < HttpStatus::SERVER_ERROR );
	}

	public static function isServerError( int $statusCode ): bool {
		return ( $statusCode >= HttpStatus::SERVER_ERROR ) && ( $statusCode < HttpStatus::SERVER_ERROR_END );
	}

	public static function isError( int $statusCode ): bool {
		return ( $statusCode >= HttpStatus::CLIENT_ERROR ) && ( $statusCode < HttpStatus::SERVER_ERROR_END );
	}

	public static function getReasonPhrase( int $statusCode ): string {
		if( array_key_exists( $statusCode, HttpStatus::REASON_PHRASES ) ) {
			return HttpStatus::REASON_PHRASES[$statusCode];
		}
		throw new \InvalidArgumentException( "Invalid status code" );
	}
}