<?php

namespace Rehyved\http;


class HttpRequestException extends \Exception
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }

}