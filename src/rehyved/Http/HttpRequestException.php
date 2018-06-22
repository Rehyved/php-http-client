<?php

namespace Rehyved\Http;

/**
 * An exception thrown when a HTTP request fails
 * @package Rehyved\Http
 * @see curl_error()
 */
class HttpRequestException extends \Exception
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }

}