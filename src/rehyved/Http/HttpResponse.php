<?php

namespace Rehyved\Http;

/**
 * Represents an instance of a HTTP response
 * This class provides methods to retrieve the contents and status of the request.
 * @package Rehyved\Http
 */
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

    /**
     * HttpResponse constructor.
     * @param array $requestInfo the curl request info
     * @param array $headers the headers returned by the HTTP request as an associative array of header name -> header value
     * @param mixed $response the response content
     * @param string $error the error returned by the HTTP request if one has occurred.
     * @see curl_getinfo() for information about the $requestInfo parameter
     * @see curl_exec() for information about the $response parameter
     * @see curl_error() for information about the $error parameter
     */
    public function __construct(array $requestInfo, array $headers, $response, string $error)
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

    /**
     * Returns the URL that was used to retrieve the HTTP response
     * @return string the URL from which the HTTP response was received.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Returns the HTTP status code returned in the response
     * @return int the status code
     * @see HttpStatus
     */
    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * Returns the headers returned in the HTTP response as an associative array
     * @return array the headers in the HTTP response
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns the value of the header in the HTTP response with the provided name
     * @param string $name the header name for which to retrieve the value
     * @return mixed the value of the header or NULL if the header was not in the HTTP response
     */
    public function getHeader(string $name)
    {
        if (array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        }
        return null;
    }

    /**
     * Returns the cookies returned in the HTTP response as an associative array
     * @return array the cookies in the HTTP response
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Returns the value of the cookie in the HTTP response with the provided name
     * @param string $name the cookie name for which to retrieve the value
     * @return HttpCookie the value of the cookie or NULL if the header was not in the HTTP response
     */
    public function getCookie(string $name): HttpCookie
    {
        if (array_key_exists($name, $this->cookies)) {
            return $this->cookies[$name];
        }
        return null;
    }

    /**
     * Imports the cookies from the HTTP response.
     * If the cookie already exists it is replaced by the value in the HTTP response.
     * @see setcookie()
     */
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

    /**
     * Retrieves the value of the Content-Type header in the HTTP response
     * @return string the value of the Content-Type header
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Returns the length of the content of the HTTP response in bytes
     * @return int the length of the HTTP response content in bytes
     */
    public function getContentLength(): int
    {
        return $this->contentLength;
    }

    /**
     * Returns the raw content of the HTTP response
     * @return string the raw content of the HTTP response
     */
    public function getContentRaw(): string
    {
        return $this->content;
    }

    /**
     * Returns the content of the HTTP response.
     * If the response has a Content-Type of application/json the content is returned as a deserialized object.
     * If the response has a Content-Type of text/xml or application/xml content is returned as a deserialized SimpleXMLElement.
     * @return bool|mixed|\SimpleXMLElement|string
     * @see SimpleXMLElement
     * @see json_decode()
     */
    public function getContent()
    {
        if (strpos($this->contentType, "application/json") !== false) {
            return json_decode($this->content);
        }
        if (strpos($this->contentType, "text/xml") !== false
            || strpos($this->contentType, "application/xml") !== false
        ) {
            return simplexml_load_string($this->content);
        }
        return $this->content;
    }

    /**
     * Indicates if the HTTP response indicated success or failure
     * @return bool
     * @see HttpResponse::getHttpStatus()
     * @see HttpStatus
     */
    public function isError()
    {
        return !empty($this->error) || HttpStatus::isError($this->httpStatus);
    }
}
