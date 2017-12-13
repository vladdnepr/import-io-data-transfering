<?php

namespace VladDnepr\ImportIO;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class ServiceRequest
{
    const URL = 'https://data.import.io/extractor/%s/json/latest?_apikey=%s';

    protected $extractorId;
    protected $apiKey;

    /**
     * @var array
     */
    protected $errors = [];

    public function __construct($apiKey, $extractorId)
    {
        $this->apiKey = $apiKey;
        $this->extractorId  = $extractorId;
    }

    /**
     * @return null|ServiceResponse
     */
    public function getResponse()
    {
        $response = null;

        $this->errors = [];

        try {
//            $data = strval((new Client())->get(sprintf(self::URL, $this->extractorId, $this->apiKey))->getBody());
            $data = "{\"title\": \"Test 1\", \"price\": \"1.11\", \"count\": \"42\"}\n" .
                "{\"title\": \"Test 2\", \"price\": 2.22, \"count\": 42}\n" .
                "{\"title\": \"Test 3\", \"price\": 3.33, \"count\": 42}";

            $response = new ServiceResponse($data);
        } catch (BadResponseException $e) {
            $data = \json_decode(strval($e->getResponse()->getBody()), true);

            $this->errors[] = isset($data['message']) ?
                $data['message'] . ' ' . (isset($data['details']) ? $data['details'] : '') :
                $e->getMessage();
        }

        return $response;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
