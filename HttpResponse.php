<?php

namespace http;

class HttpResponse
{
    private $url;
    private $httpStatus;
    private $headerSize;
    private $headers;
    private $cookies;
    private $contentType;
    private $contentLength;
    private $content;
    private $error;

    public function __construct(array $requestInfo, array $headers, $response, $error)
    {
        $this->url = $requestInfo["url"];
        $this->httpStatus = $requestInfo["http_code"];

        $this->headers = $headers;
        $this->cookies = $this->processCookieHeaders($headers);

        if ($response !== false) {
            $this->contentType = $requestInfo["content_type"];

            $this->headerSize = $requestInfo["header_size"];
            $this->contentLength = $requestInfo["content_length"] ?? strlen($response) - $this->headerSize;

            $this->content = $this->processContent($response, $this->headerSize);
        }

        $this->error = $error;
    }

    private function processCookieHeaders($headers)
    {
        $cookies = array();
        foreach ($headers as $header => $value) {
            if (mb_stripos($header, "Set-Cookie") !== false) {

                foreach ($value as $cookieString) {
                    $cookie = new HttpCookie($cookieString);
                    $cookies[$cookie->getName()] = $cookie;
                }
            }
        }
        return $cookies;
    }

    private function processContent($response, $headerSize)
    {
        return substr($response, $headerSize);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader($name)
    {
        if (array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        }
        return false;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getCookie($name): HttpCookie
    {
        if (array_key_exists($name, $this->cookies)) {
            return $this->cookies[$name];
        }
        return null;
    }

    public function importCookies()
    {
        foreach ($this->cookies as $cookie) {
            if (!empty($cookie->getValue())) {
                setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpires(), $cookie->getPath(), $cookie->getDomain(), $cookie->getSecure() ?? false, $cookie->getHttpOnly());
            } else {
                unset($_COOKIE[$cookie["name"]]);
                setcookie($cookie["name"], null, time() - 3600);
            }
        }
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getContentLength(): int
    {
        return $this->contentLength;
    }

    public function getContentRaw(): string
    {
        return $this->content;
    }

    public function getContent()
    {
        if ($this->contentType == "application/json") {
            return json_decode($this->content);
        }
        if ($this->contentType == "text/xml" || $this->contentType == "application/xml") {
            return simplexml_load_string($this->content);
        }
        return $this->content;
    }

    public function isError()
    {
        return !empty($this->error) || HttpStatus::isError($this->httpStatus);
    }
}

?>