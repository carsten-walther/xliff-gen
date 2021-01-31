<?php

namespace CarstenWalther\XliffGen\Translator;

use GuzzleHttp\Client;

/**
 * Class AbstractService
 *
 * @package CarstenWalther\XliffGen\Translator
 */
class AbstractService
{
    /**
     * REQUEST_METHOD_GET
     */
    public const REQUEST_METHOD_GET = 'GET';

    /**
     * REQUEST_METHOD_POST
     */
    public const REQUEST_METHOD_POST = 'POST';

    /**
     * REQUEST_METHOD_PUT
     */
    public const REQUEST_METHOD_PUT = 'PUT';

    /**
     * REQUEST_METHOD_DELETE
     */
    public const REQUEST_METHOD_DELETE = 'DELETE';

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * AbstractService constructor.
     */
    public function __construct()
    {
        $this->client = new Client([
            'http_errors' => false
        ]);

        $this->setHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @param $headers
     *
     * @return \CarstenWalther\XliffGen\Translator\AbstractService
     */
    public function setHeaders($headers) : AbstractService
    {
        $this->options['headers'] = $headers;
        return $this;
    }

    /**
     * @param $url
     *
     * @return \CarstenWalther\XliffGen\Translator\AbstractService
     */
    public function setUrl($url) : AbstractService
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param $apiKey
     *
     * @return \CarstenWalther\XliffGen\Translator\AbstractService
     */
    public function setApiKey($apiKey) : AbstractService
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @param $type
     *
     * @return \CarstenWalther\XliffGen\Translator\AbstractService
     */
    public function setType($type) : AbstractService
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param $method
     *
     * @return \CarstenWalther\XliffGen\Translator\AbstractService
     */
    public function setMethod($method) : AbstractService
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param $data
     *
     * @return \CarstenWalther\XliffGen\Translator\AbstractService
     */
    public function setData($data) : AbstractService
    {
        $this->options['json'] = $data;
        return $this;
    }

    /**
     * @param $multipart
     *
     * @return \CarstenWalther\XliffGen\Translator\AbstractService
     */
    public function setMultipart($multipart) : AbstractService
    {
        $this->options['multipart'] = $multipart;
        return $this;
    }

    /**
     * @return \CarstenWalther\XliffGen\Translator\Response
     * @throws \GuzzleHttp\Exception\GuzzleException|\JsonException
     */
    public function request() : Response
    {
        if ($this->apiKey) {
            $this->options['auth'] = ['apiKey', $this->apiKey];
        }
        return new Response($this->client->request($this->type, $this->url . $this->method, $this->options));
    }
}
