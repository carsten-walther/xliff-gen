<?php

namespace CarstenWalther\XliffGen\Translator\Service;

use CarstenWalther\XliffGen\Translator\AbstractService;
use CarstenWalther\XliffGen\Utility\DebugUtility;

/**
 * Class Watson
 *
 * Documentation:
 *
 * @see     : https://cloud.ibm.com/apidocs/language-translator
 *
 * @package CarstenWalther\XliffGen\Translator\Service
 */
class Watson extends AbstractService
{
    /**
     * @var string
     */
    protected $url = 'https://api.eu-de.language-translator.watson.cloud.ibm.com/instances/f6ed0274-d4af-4888-9f66-259ae709485c';

    /**
     * @var string
     */
    protected $apiKey = '';

    /**
     * @var string
     */
    protected $version = '?version=2018-05-01';

    /**
     * @retuen void
     */
    public function initialize() : void
    {
        $this->apiKey = APIKEY;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function languages() : ?array
    {
        $result = $this
            ->setUrl($this->url)
            ->setApiKey($this->apiKey)
            ->setMethod('/v3/languages?version=2018-05-01')
            ->setType(AbstractService::REQUEST_METHOD_GET)
            ->request();

        if ($result->getStatus() === 200) {
            return $result->getBody()->languages;
        }
        return null;
    }

    /**
     * @param string[] $text
     * @param string   $modelId
     * @param string   $source
     * @param string   $target
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function translate(array $text, $modelId = '', $source = '', $target = '')
    {
        $data = array_filter([
            'text' => $text,
            'model_id' => $modelId,
            'source' => $source,
            'target' => $target,
        ]);

        $result = $this
            ->setUrl($this->url)
            ->setApiKey($this->apiKey)
            ->setMethod('/v3/translate' . $this->version)
            ->setType(AbstractService::REQUEST_METHOD_POST)
            ->setData($data)
            ->request();


        if ($result->getStatus() === 200) {
            return $result->getBody()->translations;
        }
        return null;
    }
}
