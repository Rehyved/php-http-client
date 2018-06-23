# HTTP Client for PHP
*A HTTP Client implementation on top of the PHP cURL extension for PHP inspired by the builder pattern.*

[![Build Status](https://travis-ci.org/Rehyved/php-http-client.svg?branch=master)](https://travis-ci.org/Rehyved/php-http-client)


This library uses a builder pattern similar to the [Apache HTTP client Fluent API](https://hc.apache.org/httpcomponents-client-ga/tutorial/html/fluent.html) for Java.
The goal is to provide a readable and maintainable way of writing HTTP request logic inside your PHP websites and applications.

Feedback is always welcome.

* [Installation](#installation)
* [Usage](#usage)
  * [HttpRequest](#httprequest-class)
    * [Overriding default configuration](#overriding-default-configuration)
    * [Request types](#request-types)
      * [GET](#get-request)
      * [PUT](#put-request)
      * [POST](#post-request)
      * [DELETE](#delete-request)
    * [Adding query parameters](#adding-query-parameters)
    * [Adding headers](#adding-headers)
    * [Adding cookies](#adding-cookies)
    * [Basic authentication](#basic-authentication)
    * [Authorization header](#authorization-header)
    * [Changing request timeout](#changing-request-timeout)
    * [Disabling SSL certificate verification](#disabling-ssl-certificate-verification)
  * [HttpResponse](#httpresponse-class)
  * [HttpStatus](#httpstatus-class)

## Installation
**Prerequisites**:
* PHP 7.0 or higher
* PHP cURL module (ext-curl)
* PHP DOM module (ext-dom)

This library is available through [Packagist](https://packagist.org) and can be imported using [Composer](https://getcomposer.org):
```
composer require rehyved/php-http-client
``` 

## Usage
The goal with this library is to make it easy to produce HTTP requests in PHP whilst keeping the code readable and understandable.
The main starting point for this is the [HttpRequest class](#httprequest-class) and the [HttpResponse class](#httpresponse-class).

### HttpRequest class
The following examples show different usages of the HttpRequest class to perform HTTP requests:

#### Overriding default configuration
Default configuration for some settings can be configured globally to prevent having to provide these values on each creation of an HttpRequest. 

The following configuration options are available by defining the appropriate constants with [define()](http://php.net/manual/en/function.define.php):

* **RPHC_DEFAULT_HEADERS** - An associative array of header name-> header value to be included with each HTTP request (**default**: `array()`)
* **RPHC_DEFAULT_TIMEOUT** - An int value indicating the number of seconds to use as a timeout for HTTP requests (**default**: `30`)
* **RPHC_DEFAULT_VERIFY_SSL_CERTIFICATE** - a boolean value indicating if the validity of SSL certificates should be enforced in HTTP requests (**default**: `true`)

#### Request types
##### GET request
```php
$response = HttpRequest::create("https://httpbin.org")  // Base url
    ->get("get");                                       // Path
```
*[https://httpbin.org]() is a nice service to test HTTP requests against, it provides several ways to try different kinds of requests with a configurable response*

##### PUT request
```php
$response = HttpRequest::create("https://httpbin.org")  // Base url
    ->contentType("application/json")                   // Content-Type header
->put("put", array("key" => "value");                   // Path & body
```

##### POST request
```php
$response = HttpRequest::create("https://httpbin.org")  // Base url
    ->contentType("application/json")                   // Content-Type header
->post("post", array("key" => "value");                 // Path & body
```

##### DELETE request
```php
$response = HttpRequest::create("https://httpbin.org")  // Base url
    ->contentType("application/json")                   // Content-Type header
->delete("delete", array("key" => "value");               // Path & body
```


#### Adding query parameters
```php
$response = HttpRequest::create("https://httpbin.org")  // Base url
    ->parameter("search", "Search query")               // Add a single query parameter
    ->parameters(array("key" => "value"))               // Add an array of query parameters
    ->get("get");                                       // Path
```

#### Adding Headers
```php
$response = HttpRequest::create("https://httpbin.org")  // Base url
    ->header("Accept", "application/json")              // Add a single header
    ->headers(array("key" => "value"))                  // Add an array of headers
    ->get("get");                                       // Path
```

#### Adding Cookies
```php
$response = HttpRequest::create("https://httpbin.org")  // Base url
    ->cookie("search", "Search query")                  // Add a single cookie
    ->cookies(array("key" => "value"))                  // Add an array of cookies
    ->cookies()                                         // Adds all cookies from $_COOKIE to the request
    ->get("get");                                       // Path
```

#### Basic Authentication
```php
$response = HttpRequest::create("https://httpbin.org")  // Base url
    ->basicAuthentication("username", "password")       // Adds basic authentication to the request
    ->get("get");                                       // Path
```

#### Authorization header
```php
$response = HttpRequest::create("https://httpbin.org")  // Base url
    ->authorization("Bearer", "<JWT-token>")            // Convenience method to add an Authorization header
    ->get("get");                                       // Path
```

#### Changing request timeout
```php
$response = HttpRequest::create("https://httpbin.org")  // Base url
    ->timeout(20)                                       // Changes the timeout for the request to 20 seconds
    ->get("get");                                       // Path
```

#### Disabling SSL certificate verification
**NOTE:** This feature is not recommended in a production system but is meant as a convenience option in test environments
```php
$response = HttpRequest::create("https://httpbin.org")  // Base url
    ->verifySslCertificate(false)                       // Disables the verification of SSL certificates
    ->get("get");                                       // Path
```


### HttpResponse class
The `$response` variable will hold an instance of HttpResponse. 
This type of object holds the resulting content of the HttpRequest and provides useful methods to extract further information.

#### Status handling
```php
$isError = $response->isError()){ // checks the HTTP status to see if it is an error see the HttpStatus class
$statusCode = $response->getHttpStatus(); 
```

#### Header handling
```php
$contentType = $response->getContentType();
$header = $response->getHeader("Content-Type");
$headers = $response->getHeaders(); // an associative array of header name -> header value
```
#### Cookie handling
```php
$cookie = $response->getCookie("chocolatechip"); // returns a HttpCookie object
$cookie = $response->getCookies(); // a list of HttpCookie objects
$response->importCookies(); // Adds all cookies to the current session by using setcookie (http://php.net/manual/en/function.setcookie.php)
```
#### Response body handling
```php
$contentLength = $response->getContentLength();
$content = $response->getContent(); // Will deserialize JSON or XML content if the matching Content-Type was received 
$contentRaw = $response->getContentRaw() // Does not try to deserialize and returns the raw response body
```


### HttpStatus class
This class provides constants to retrieve the matching status code for an HTTP status as well as convenience methods to get the reason phrase and check the type of a status code.

#### Constants
The class provides constants for the HTTP statuses, for example:
```php
HttpStatus::OK
HttpStatus::CLIENT_ERROR
HttpStatus::SERVER_ERROR

etc...
```

#### Methods
```php
HttpStatus::isInformational(int $statusCode)
HttpStatus::isSuccessful(int $statusCode)
HttpStatus::isRedirection(int $statusCode)
HttpStatus::isClientError(int $statusCode)
HttpStatus::isServerError(int $statusCode)
HttpStatus::isError(int $statusCode)
HttpStatus::getReasonPhrase(int $statusCode)
```