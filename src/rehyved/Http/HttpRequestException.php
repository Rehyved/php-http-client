<?php

namespace Rehyved\Http;

/**
 * An exception thrown when a HTTP request fails or results in an error response
 * @package Rehyved\Http
 */
class HttpRequestException extends \Exception
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }

}