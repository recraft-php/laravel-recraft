<?php

namespace Recraft;

use Exception;
use Recraft\Client\Client;

class Recraft
{
    /**
     * the ai model to use
     * @var string $model
     */
    protected $model = 'recraftv3';

    /**
     * the supported models
     * @var array $supported_models
     */
    protected $supported_models = ['recraftv3', 'recraft20b'];

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
     * @var \Recraft\Client\Client $client
     */
    protected $client = null;

    public function __construct(
        Client $client
    ) {
        $this->client = $client;
        $this->init();
    }

    /**
     * set the api key
     * @param string $key
     * @return void
     */
    public static function setKey(string $key)
    {
        Client::setKey($key);
    }

    /**
     * set the api key and model
     * @return void
     */
    protected function init()
    {
        //Client::setKey(config('recraft.api_key', ''));
        $default_model = config('recraft.default_model', 'v3');
        $models = config('recraft.models', []);
        $model = isset($models[$default_model]) ? $models[$default_model] : null;
        $this->model = $model ?? $this->model;
    }

    /**
     * set the model
     * @param string $model
     * @return \Recraft\Recraft
     */
    public function model(string $model)
    {
        if (!in_array($model, $this->supported_models)) {
            throw new Exception("unkown model [$model]");
        }
        $this->model = $model;
        return $this;
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
        $supported_sizes = array_keys($this->sizes);
        if (!isset($this->sizes[$size])) {
            throw new Exception("unkown image size [$size] supported sizes are " . implode(",", $supported_sizes));
        }
        return $this->sizes[$size];
    }
    /**
     * generate ai images
     * @param array $options
     */
    public function generateImages(array $options)
    {
        $requiredKeys = ["prompt"];
        $payload = [
            "model" => $this->model,
            "style" => $this->styles["digital"],
            "response_format" => $this->output_format,
            "size" => $this->getSize("square_large")
        ];
        $option_keys = array_keys($options);
        foreach ($requiredKeys as $key) {
            if (!in_array($key, $option_keys)) {
                throw new Exception("the key [$key] is required");
            }
        }
        $payload["prompt"] = $options["prompt"];
        if (array_key_exists('style', $options)) {
            $payload["style"] = $this->getStyle($options["style"]);
        }
        if (array_key_exists('size', $options)) {
            $payload["size"] = $this->getSize($options["size"]);
        }
        if (array_key_exists('output', $options)) {
            $this->isSupportedOutputFormat($options["output"]);
            $payload["response_format"] = $options["output"];
        }
        $endpoint = $this->client->getEndpoint('images.generate');
        $this->client->usingJson = true;
        return $this->client->request($endpoint["url"], $endpoint["method"], $payload);
    }
}
