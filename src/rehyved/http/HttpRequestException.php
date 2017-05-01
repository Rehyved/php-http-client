<?php

namespace Rehyved\http;


use Throwable;

class HttpRequestException extends \Exception
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }

}