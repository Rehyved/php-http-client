<?php

namespace Rehyved\Http;

class HttpRequest
{
    const DEFAULT_TIMEOUT = 30; // seconds

    private $baseUrl;
    private $headers;
    private $parameters;
    private $cookies;
    private $mirrorCookies;
    private $timeout = HttpRequest::DEFAULT_TIMEOUT;

    private $username;
    private $password;

    private function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->headers = array();
        $this->parameters = array();
        $this->cookies = array();
        $this->mirrorCookies = false;
    }

    public static function create($baseUrl)
    {
        return new HttpRequest($baseUrl);
    }

    public function header($name, $value)
    {
        if (!array_key_exists($name, $this->headers)) {
            $this->headers[$name] = array();
        }
        $this->headers[$name][] = $value;

        return $this;
    }

    public function headers(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->header($name, $value);
        }

        return $this;
    }

    public function contentType(string $contentType)
    {
        $contentType = trim($contentType);

        // If this is a multipart request and boundary was not defined, we define a boundary as this is required for multipart requests:
        if (stripos($contentType, "multipart/") !== false) {
            if (stripos($contentType, "boundary") === false) {
                $contentType .= "; boundary=\"" . uniqid(time()) . "\"";
                $contentType = preg_replace('/(.)(;{2,})/', '$1;', $contentType); // remove double semi-colon, except after scheme
            }
        }

        return $this->header("Content-Type", $contentType);
    }

    public function accept(string $contentType)
    {
        $contentType = trim($contentType);

        return $this->header("Accept", $contentType);
    }

    public function authorization(string $scheme, $value)
    {
        if (empty($scheme)) {
            throw new \InvalidArgumentException("Scheme was null or empty");
        }
        if (empty($value)) {
            throw new \InvalidArgumentException("Value was null or empty");
        }
        return $this->header("Authorization", $scheme . " " . $value);
    }

    public function parameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function parameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->parameter($name, $value);
        }

        return $this;
    }

    public function cookie(string $name, string $value)
    {
        if ($name != 'Array') {
            $this->cookies[$name] = $value;
        }
        return $this;
    }

    public function cookies(array $cookies = null)
    {
        if ($cookies === null) {
            $cookies = $_COOKIE;
        }
        foreach ($cookies as $name => $value) {
            $this->cookie($name, $value);
        }

        return $this;
    }

    public function mirrorCookies()
    {
        $this->mirrorCookies = true;
        return $this->cookies();
    }

    public function basicAuthentication(string $username, string $password = "")
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function timeout(int $timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function get(string $path = ""): HttpResponse
    {
        return $this->request($path, HttpMethod::GET);
    }

    public function put(string $path, $body)
    {
        return $this->request($path, HttpMethod::PUT, $body);
    }

    public function post(string $path, $body)
    {
        return $this->request($path, HttpMethod::POST, $body);
    }

    public function delete(string $path, $body)
    {
        return $this->request($path, HttpMethod::DELETE, $body);
    }

    private function request(string $path, string $method, $body = null): HttpResponse
    {
        $ch = curl_init();

        $this->processUrl($path, $ch);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $this->processBody($ch, $body); // Do body first as this might add additional headers
        $this->processHeaders($ch);
        $this->processCookies($ch);

        return $this->send($ch);
    }

    private function send($ch)
    {
        $returnHeaders = array();
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$returnHeaders) {
            if (strpos($header, ':') !== false) {
                list($name, $value) = explode(':', $header);
                if (!array_key_exists($name, $returnHeaders)) {
                    $returnHeaders[$name] = array();
                }

                $returnHeaders[$name][] = trim($value);
            }

            return strlen($header);
        });

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Ensure we are coping with 300 (redirect) responses:
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);

        if (!empty($this->username)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
        }

        $response = curl_exec($ch);
        $requestInfo = curl_getinfo($ch);
        $error = curl_error($ch);

        if (!empty($error)) {
            throw new HttpRequestException($error);
        }

        $httpResponse = new HttpResponse($requestInfo, $returnHeaders, $response, $error);

        if ($this->mirrorCookies === true) {
            $httpResponse->importCookies();
        }

        return $httpResponse;
    }

    private function processUrl(string $path, $ch)
    {
        $url = $this->buildUrl($path);
        curl_setopt($ch, CURLOPT_URL, $url);
    }

    private function buildUrl(string $path)
    {
        $url = $this->baseUrl;
        if (!empty($path)) {
            $url .= "/" . $path;
        }

        if (count($this->parameters) > 0) {
            $url .= '?' . http_build_query($this->parameters);
        }

        // Clean url
        $url = preg_replace('/([^:])(\/{2,})/', '$1/', $url); // remove double slashes, except after scheme
        $url = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $url); // convert arrays with indexes to arrays without (i.e. parameter[0]=1 -> parameter[]=1)

        return $url;
    }

    private function processHeaders($ch)
    {
        $headers = array();
        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                $headers[] = $name . ": " . $value;
            }
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
    }

    private function processCookies($ch)
    {
        // forward current cookies to curl
        $cookies = array();
        foreach ($this->cookies as $key => $value) {
            $cookies[] = $key . '=' . $value;
        }
        curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
    }

    private function processBody($ch, $body)
    {
        if ($body !== null) {
            if (is_object($body) || is_array($body)) {

                if (isset($this->headers["Content-Type"][0])) {
                    $contentType = $this->headers["Content-Type"][0];
                    if (stripos($contentType, "application/json") !== false) {
                        $body = json_encode($body);
                    }
                    if (stripos($contentType, "application/x-www-form-urlencoded") !== false) {
                        $body = http_build_query($body);
                    }
                    if (stripos($contentType, "multipart/form-data") !== false) {
                        $boundary = $this->parseBoundaryFromContentType($contentType);
                        $body = $this->multipartBuildBody($body, $boundary);
                    }
                } else {
                    $body = http_build_query($body);
                    $this->contentType("application/x-www-form-urlencoded");
                }
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            $this->header("Content-Length", strlen($body));
        }
    }

    private function parseBoundaryFromContentType($contentType)
    {
        $match = array();
        if (preg_match('/boundary="([^\"]+)"/is', $contentType, $match) > 0) {
            return $match[1];
        }
        throw new \InvalidArgumentException("The provided Content-Type header contained a 'multipart/*' content type but did not define a boundary.");
    }

    private function multipartBuildBody($fields, $boundary)
    {
        $body = '';

        foreach ($fields as $name => $value) {
            if (is_array($value)) {
                $data = $value['data'];
                $filename = (isset($value['filename'])) ? $value['filename'] : false;

                $body .= "--$boundary\nContent-Disposition: form-data; name=\"$name\"";
                if ($filename !== false) {
                    $body .= ";filename=\"$filename";
                }

                $body .= "\n\n$data\n";
            } else if (!empty($value)) {
                $body .= "--$boundary\nContent-Disposition: form-data; name=\"$name\"\n\n$value\n";
            }
        }

        $body .= "--$boundary--";
        return $body;
    }
}

?>