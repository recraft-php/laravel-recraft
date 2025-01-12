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
     * the ai model to use
     * @var string $model
     */
    protected static $model = 'recraftv3';

    /**
     * the supported models
     * @var array $supported_models
     */
    protected static $supported_models = ['recraftv3', 'recraft20b'];

    /**
     * default style
     * @var string
     */
    protected const DEFAULT_STYLE = 'digital';

    /**
     * default output format
     * @var string
     */
    protected const DEFAULT_OUTPUT_FORMAT = 'b64_json';

    /**
     * should use json for the request body
     * @var bool $usingJson
     */
    protected $usingJson = true;

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
     * image styles
     * @var array $styles
     */
    protected $styles = [
        "digital" => "digital_illustration",
        "realistic" => 'realistic_image'
    ];

    /**
     * the image size
     * @var array $sizes
     */
    protected $sizes = [
        "square_large" => "1024x1024",
        "landscape_medium" => "1365x1024",
        "portrait_medium" => "1024x1365",
        "landscape_large" => "1536x1024",
        "portrait_large" => "1024x1536",
        "landscape_wide" => "1820x1024",
        "portrait_tall" => "1024x1820",
        "portrait_extra_tall" => "1024x2048",
        "landscape_extra_wide" => "2048x1024",
        "landscape_standard" => "1434x1024",
        "portrait_standard" => "1024x1434",
        "portrait_standard_tall" => "1024x1280",
        "landscape_standard_wide" => "1280x1024",
        "portrait_extended" => "1024x1707",
        "landscape_extended" => "1707x1024"
    ];

    /**
     * support output format
     * @var array $supported_output_formats
     */
    protected $supported_output_formats = ["b64_json", "url"];

    /**
     * output format
     * @var string $output_format
     */
    protected $output_format = 'b64_json';

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
     * set the model
     * @param string $model
     */
    public static function setModel(string $model)
    {
        if (!in_array($model, self::$supported_models)) {
            throw new Exception("unkown model [$model]");
        }
        self::$model = $model;
    }

    /**
     * config the client
     * @param string $apiKey
     * @param string $model
     */
    public function config(string $apiKey, string $model = 'recraftv3')
    {
        self::$apiKey = $apiKey;
        self::$model = $model;
    }

    /**
     * get the style
     * @param string $style
     * @return string
     */
    protected function getStyle(string $style)
    {
        if (isset($this->styles[$style])) {
            return $this->styles[$style];
        }
        return $this->styles[self::DEFAULT_STYLE];
    }

    /**
     * check if the apikey is empty
     * @var \Exception|bool
     */
    protected function hasApiKey()
    {
        if (empty(self::$apiKey)) {
            throw new Exception("the apikey can not be empty, please set the api key");
        }
        return true;
    }

    /**
     * check if the output format is supported
     * @var string $format
     * @return \Exception|bool
     */
    protected function isSupportedOutputFormat(string $format)
    {
        if (!in_array($format, $this->supported_output_formats)) {
            throw new Exception("the output format [$format] is not supported");
        }
    }

    /**
     * get the image size
     * @param string $size
     * @return string
     */
    protected function getSize(string $size)
    {
        if (!isset($this->sizes[$size])) {
            throw new Exception("unkown image size [$size]");
        }
        return $this->sizes[$size];
    }

    /**
     * get the endpoint
     * @var string $endpoint
     * @return array ["method" => "method", "url" => "url"]
     */
    protected function getEndpoint(string $endpoint)
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
        return [
            "method" => $this->endpoints[$group][$entry]["method"],
            "url" => $this->endpoints[$group][$entry]["url"]
        ];
    }

    /**
     * make an http request to the endpoint
     * @param string $url
     * @param string $method
     * @param array $payload
     * @return array
     */
    protected function request(string $url, string $method, array $payload = [])
    {
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
