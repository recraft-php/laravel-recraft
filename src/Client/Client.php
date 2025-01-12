<?php

namespace Recraft\Client;

use Exception;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Recraft\Exception\InvalidApiKeyException;

class Client
{
    /**
     * the recraft base url
     * @var string $baseUrl
     */
    protected $baseUrl = 'https://external.api.recraft.ai/v1';

    /**
     * the api key
     * @var string $apiKey
     */
    protected static $apiKey = '';

    /**
     * should use json for the request body
     * @var bool $usingJson
     */
    public $usingJson = true;

    /**
     * the api endpoints
     * @var string $endpoints
     */
    protected $endpoints = [
        "images" => [
            "generate" => [
                "method" => "post",
                "url" => "/images/generations"
            ]
        ],
    ];

    /**
     * the http client
     * @var \GuzzleHttp\Client $client
     */
    protected $client = null;

    /**
     * ioc container
     * @var \Illuminate\Container\Container $app
     */
    protected $app = null;

    public function __construct(
        GuzzleHttpClient $client,
        Container $app,
    ) {
        $this->client = $client;
        $this->app = $app;
    }

    /**
     * set the api key
     * @param string $key
     * @return void
     */
    public static function setKey(string $key)
    {
        self::$apiKey = $key;
    }

    /**
     * check if the apikey is empty
     * @var \Exception|bool
     */
    protected function hasApiKey()
    {
        if (empty(self::$apiKey)) {
            throw new Exception("the api key can not be empty, please set the api key");
        }
        return true;
    }

    /**
     * get the endpoint
     * @var string $endpoint
     * @return array ["method" => "method", "url" => "url"]
     */
    public function getEndpoint(string $endpoint)
    {
        $parts = explode(".", $endpoint, 2);
        if (count($parts) <= 1) {
            throw new Exception(
                "Invalid endpoint, endpoint shoud be of group.entry format e.g generation.endpoint"
            );
        }
        [$group, $entry] = $parts;
        if (!isset($this->endpoints[$group][$entry])) {
            throw new Exception("Unkown endpoint [$endpoint]");
        }
        $url = $this->endpoints[$group][$entry]["url"];
        $full_url = $this->baseUrl . ((str_starts_with($url, "/")) ? $url : "/" . $url);
        return [
            "method" => $this->endpoints[$group][$entry]["method"],
            "url" => $full_url
        ];
    }

    /**
     * make an http request to the endpoint
     * @param string $url
     * @param string $method
     * @param array $payload
     * @return array
     */
    public function request(string $url, string $method, array $payload = [])
    {
        $this->hasApiKey();
        $method = strtoupper($method);
        $headers = [
            "Authorization" => "Bearer " . self::$apiKey
        ];
        if ($this->usingJson) {
            $headers["Content-type"] = "application/json";
        }
        $options = [
            "headers" => $headers
        ];
        if (!empty($payload) || !is_null($payload)) {
            if ($method !== 'GET') {
                if ($this->usingJson) {
                    $options["json"] = $payload;
                } else {
                    $options["form_params"] = $payload;
                }
            } else {
                $options["form_params"] = $payload;
            }
        }
        try {
            $response = $this->client->request($method, $url, $options);
            return $this->handle($response);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            if ($statusCode === 401) {
                throw new InvalidApiKeyException(self::$apiKey);
            }
            throw new Exception($response->getBody()->getContents());
        }
    }
    /**
     * handle the response
     * @param ResponseInterface $response
     * @return mixed
     */
    protected function handle(ResponseInterface $response)
    {
        $expectJson = strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false;
        $responseBody = $response->getBody()->getContents();
        if ($expectJson) {
            return json_decode($responseBody, true);
        }
        return $responseBody;
    }
}
