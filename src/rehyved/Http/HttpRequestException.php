<?php

namespace Rehyved\Http;


class HttpRequestException extends \Exception
{
    public function __construct($message = "")
    {
        parent::__construct($message);
    }

}