<?php

namespace Rehyved\Http;

/**
 * Represents an instance of a HTTP request.
 * This class provides methods to construct a request and execute it with the appropriate HTTP method.
 *
 * Default configuration for some settings can be configured globally to prevent having to provide these values on each HttpRequest explicitly.
 * The following configuration options are available by defining the following constants:
 * <ul>
 *  <li><b>RPHC_DEFAULT_HEADERS</b> - An associative array of header name-> header value to be included with each HTTP request</li>
 *  <li><b>RPHC_DEFAULT_TIMEOUT</b> - An int value indicating the number of seconds to use as a timeout for HTTP requests</li>
 *  <li><b>RPHC_DEFAULT_VERIFY_SSL_CERTIFICATE</b> - a boolean value indicating if the validity of SSL certificates should be enforced in HTTP requests (default: true)</li>
 * </ul>
 * @see define()
 * @package Rehyved\Http
 */
class HttpRequest
{
    const DEFAULT_HEADERS = array();
    const DEFAULT_TIMEOUT = 30; // seconds
    const DEFAULT_VERIFY_SSL_CERTIFICATE = true;

    private $baseUrl;
    private $headers;
    private $parameters;
    private $cookies;
    private $timeout;

    private $verifySslCertificate;

    private $username;
    private $password;

    private function __construct(string $baseUrl)
    {
        $this->headers = defined("RPHC_DEFAULT_HEADERS") ? RPHC_DEFAULT_HEADERS : self::DEFAULT_HEADERS;
        $this->timeout = defined("RPHC_DEFAULT_TIMEOUT") ? RPHC_DEFAULT_TIMEOUT : self::DEFAULT_TIMEOUT;
        $this->verifySslCertificate = defined("RPHC_DEFAULT_VERIFY_SSL_CERTIFICATE") ? RPHC_DEFAULT_VERIFY_SSL_CERTIFICATE : self::DEFAULT_VERIFY_SSL_CERTIFICATE;

        $this->baseUrl = $baseUrl;
        $this->parameters = array();
        $this->cookies = array();
    }

    /**
     * Creates a new HttpRequest
     * @param string $baseUrl the base URL to which the request will go.
     * @return HttpRequest
     */
    public static function create(string $baseUrl)
    {
        return new HttpRequest($baseUrl);
    }

    /**
     * Adds a header to the request
     * @param string $name the name of the header
     * @param string $value the value for the header
     * @return HttpRequest
     */
    public function header(string $name, string $value): HttpRequest
    {
        if (!array_key_exists($name, $this->headers)) {
            $this->headers[$name] = array();
        }
        $this->headers[$name][] = $value;

        return $this;
    }

    /**
     * Adds an array of headers to the HttpRequest
     * @param array $headers An associative array of name->value string values to add as headers to the HttpRequest
     * @return HttpRequest
     */
    public function headers(array $headers): HttpRequest
    {
        foreach ($headers as $name => $value) {
            $this->header($name, $value);
        }

        return $this;
    }

    /**
     * Sets the Content-Type header of the HttpRequest
     * @param string $contentType the value to set for the Content-Type header
     * @return HttpRequest
     */
    public function contentType(string $contentType): HttpRequest
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

    /**
     * Sets the Accept header of the HttpRequest
     * @param string $contentType the value to set for the Accept header
     * @return HttpRequest
     */
    public function accept(string $contentType)
    {
        $contentType = trim($contentType);

        return $this->header("Accept", $contentType);
    }

    /**
     * Sets the Authorization header of the HttpRequest
     * @param string $scheme the scheme to use in the value of the Authorization header (e.g. Bearer)
     * @param string $value the value to set for the the Authorization header
     * @return HttpRequest
     */
    public function authorization(string $scheme, string $value): HttpRequest
    {
        if (empty($scheme)) {
            throw new \InvalidArgumentException("Scheme was null or empty");
        }
        if (empty($value)) {
            throw new \InvalidArgumentException("Value was null or empty");
        }
        return $this->header("Authorization", $scheme . " " . $value);
    }

    /**
     * Adds a query parameter to the HttpRequest.
     * @param string $name the name of the query parameter to add
     * @param string $value the value of the query parameter to add
     * @return HttpRequest
     */
    public function parameter(string $name, string $value): HttpRequest
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * Adds an array of query parameters to the HttpRequest
     * @param array $parameters An associative array of name->value string values to add as query parameters to the HttpRequest
     * @return HttpRequest
     */
    public function parameters(array $parameters): HttpRequest
    {
        foreach ($parameters as $name => $value) {
            $this->parameter($name, $value);
        }

        return $this;
    }

    /**
     * Adds a cookie to the HttpRequest
     * @param string $name the name of the cookie to add to the HttpRequest
     * @param string $value the value of the cookie to add to the HttpRequest
     * @return HttpRequest
     */
    public function cookie(string $name, string $value): HttpRequest
    {
        if ($name != 'Array') {
            $this->cookies[$name] = $value;
        }
        return $this;
    }

    /**
     * Adds an array of cookies to the HttpRequest, if the provided array is null, the PHP value $_COOKIE is used.
     * @param array $cookies An associative array of name->value string values to add as cookie to the HttpRequest,
     *      default value is $_COOKIE
     * @return HttpRequest
     */
    public function cookies(array $cookies = null): HttpRequest
    {
        if ($cookies === null) {
            $cookies = $_COOKIE;
        }
        foreach ($cookies as $name => $value) {
            $this->cookie($name, $value);
        }

        return $this;
    }

    /**
     * Sets the basic authentication to use on the HttpRequest
     * @param string $username the username to use with Basic Authentication
     * @param string $password the password to use with Basic Authentication
     * @return HttpRequest
     */
    public function basicAuthentication(string $username, string $password = ""): HttpRequest
    {
        $this->username = $username;
        $this->password = $password;
        return $this;
    }

    /**
     * Sets the request timeout on the HttpRequest
     * @param int $timeout the timeout to use for the HttpRequest
     * @return HttpRequest
     */
    public function timeout(int $timeout): HttpRequest
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Controls if the validity of SSL certificates should be verified.
     * WARNING: This should never be done in a production setup and should be used for debugging only.
     * @param bool $verifySslCertificate
     * @return HttpRequest
     * @see curl_setopt() and the CURLOPT_SSL_VERIFYPEER option
     */
    public function verifySslCertificate(bool $verifySslCertificate): HttpRequest
    {
        $this->verifySslCertificate = $verifySslCertificate;
        return $this;
    }

    /**
     * Executes the HttpRequest as a GET request to the specified path
     * @param string $path the path to execute the GET request on
     * @return HttpResponse The response to the HttpRequest
     */
    public function get(string $path = ""): HttpResponse
    {
        return $this->request($path, HttpMethod::GET);
    }

    /**
     * Executes the HttpRequest as a PUT request to the specified path with the provided body
     * @param string $path the path to execute the PUT request to
     * @param mixed body the body to PUT to the specified path
     * @return HttpResponse The response to the HttpRequest
     */
    public function put(string $path, $body)
    {
        return $this->request($path, HttpMethod::PUT, $body);
    }

    /**
     * Executes the HttpRequest as a POST request to the specified path with the provided body
     * @param string $path the path to execute the POST request to
     * @param mixed body the body to POST to the specified path
     * @return HttpResponse The response to the HttpRequest
     */
    public function post(string $path, $body)
    {
        return $this->request($path, HttpMethod::POST, $body);
    }

    /**
     * Executes the HttpRequest as a DELETE request to the specified path with the provided body
     * @param string $path the path to execute the DELETE request to
     * @param mixed body the body to DELETE to the specified path
     * @return HttpResponse The response to the HttpRequest
     */
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

        // Set request timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);

        // Set verification of SSL certificates
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySslCertificate);

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
                        $body = json_encode($body);;
                    } else if (stripos($contentType, "application/x-www-form-urlencoded") !== false) {
                        $body = http_build_query($body);
                    } else if (stripos($contentType, "multipart/form-data") !== false) {
                        $boundary = $this->parseBoundaryFromContentType($contentType);
                        $body = $this->multipartBuildBody($body, $boundary);
                    }
                }
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
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