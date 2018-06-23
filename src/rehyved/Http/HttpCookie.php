<?php

namespace Rehyved\Http;

class HttpCookie
{
    private $name;
    private $value;
    private $expires;
    private $maxAge;
    private $domain;
    private $path = '/';
    private $secure = false;
    private $httpOnly = true;
    private $sameSite;

    public function __construct(string $cookieString)
    {
        $cookieParts = array_map('trim', explode(';', $cookieString));
        foreach ($cookieParts as $cookiePartString) {
            $cookiePart = array_map('trim', explode('=', $cookiePartString));
            $name = $cookiePart[0];
            $value = $cookiePart[1] ?? false;
            if (stripos($name, "Expires") !== false) {
                $this->expires = strtotime($value);
            } else if (stripos($name, "Max-Age") !== false) {
                $this->maxAge = $value;
            } else if (stripos($name, "Domain") !== false) {
                $this->domain = $value;
            } else if (stripos($name, "Path") !== false) {
                $this->path = $value;
            } else if (stripos($name, "Secure") !== false) {
                $this->secure = true;
            } else if (stripos($name, "HttpOnly") !== false) {
                $this->httpOnly = true;
            } else if (stripos($name, "SameSite") !== false) {
                $this->sameSite = $value;
            } else {
                $this->name = $name;
                $this->value = $value;
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function getMaxAge()
    {
        return $this->maxAge;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getSecure()
    {
        return $this->secure;
    }

    public function getHttpOnly()
    {
        return $this->httpOnly;
    }

    public function getSameSite()
    {
        return $this->sameSite;
    }
}