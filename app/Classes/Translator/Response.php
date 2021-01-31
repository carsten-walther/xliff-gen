<?php

namespace CarstenWalther\XliffGen\Translator;

use Psr\Http\Message\ResponseInterface;

/**
 * Class Response
 *
 * @package CarstenWalther\XliffGen\Translator
 */
class Response
{
    /**
     * The body
     *
     * @var mixed
     */
    public $body;

    /**
     * The message
     *
     * @var mixed
     */
    public $message;

    /**
     * The status
     *
     * @var mixed
     */
    public $status;

    /**
     * Response constructor.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @throws \JsonException
     */
    public function __construct(ResponseInterface $response)
    {
        $this->status = $response->getStatusCode();
        $this->message = $response->getReasonPhrase();
        $this->body = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Return the response body.
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Return the response status.
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Return the response message.
     *
     * @return null|string
     */
    public function getMessage() : ?string
    {
        return $this->message;
    }
}
