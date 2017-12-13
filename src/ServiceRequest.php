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
            $data = strval((new Client())->get(sprintf(self::URL, $this->extractorId, $this->apiKey))->getBody());
// Client's test data
//            $data = "{\"url\":\"https://www.amazon.com/gp/offer-listing/B008PPZO94/ref=olp_f_primeEligible?ie=UTF8&f_new=true&f_primeEligible=true\",\"Size Large\":\"$35.22\",\"Details\":\"Details\",\"Details_link\":\"https://www.amazon.com/gp/help/customer/display.html/ref=mk_gship_olp/146-7621390-2922011?ie=UTF8&nodeId=527692&pop-up=1\",\"List Item\":\"10 hours and 28 minutes\",\"And Choose\":\"Two-Day Shipping\",\"See Details\":\"See details\",\"See Details_link\":\"https://www.amazon.com/gp/help/customer/display.html/ref=ftinfo_olp_1/146-7621390-2922011?ie=UTF8&nodeId=3510241&pop-up=1\",\"Shipping\":\"Shipping rates\",\"Shipping_link\":\"https://www.amazon.com/gp/aag/details/ref=olp_merch_ship_1/146-7621390-2922011?ie=UTF8&asin=B008PPZO94&seller=ATVPDKIKX0DER&sshmPath=shipping-rates#aag_shipping\",\"Return Policy\":\"return policy\",\"Return Policy_link\":\"https://www.amazon.com/gp/aag/details/ref=olp_merch_return_1/146-7621390-2922011?ie=UTF8&asin=B008PPZO94&seller=ATVPDKIKX0DER&sshmPath=returns#aag_returns\",\"Size Medium\":\"Tactical Intent\",\"Size Medium_link\":\"https://www.amazon.com/gp/aag/main/ref=olp_merch_name_1/146-7621390-2922011?ie=UTF8&asin=B008PPZO94&isAmazonFulfilled=1&seller=A36ZAOEPX7I0P8\",\"100positive\":\"over the past 12 months. (36,088 total ratings)\",\"Add To Cart\":\"from seller Tactical Intent and price $35.22\"}
//{\"url\":\"https://www.amazon.com/gp/offer-listing/B008PPZO94/ref=olp_f_primeEligible?ie=UTF8&f_new=true&f_primeEligible=true\",\"Size Large\":\"$35.92\",\"Details\":\"Details\",\"Details_link\":\"https://www.amazon.com/gp/help/customer/display.html/ref=mk_gship_olp/146-7621390-2922011?ie=UTF8&nodeId=527692&pop-up=1\",\"List Item\":\"\",\"And Choose\":\"One-Day Shipping\",\"See Details\":\"See details\",\"See Details_link\":\"https://www.amazon.com/gp/help/customer/display.html/ref=ftinfo_olp_2/146-7621390-2922011?ie=UTF8&nodeId=3510241&pop-up=1\",\"Shipping\":\"Shipping rates\",\"Shipping_link\":\"https://www.amazon.com/gp/aag/details/ref=olp_merch_ship_2/146-7621390-2922011?ie=UTF8&asin=B008PPZO94&seller=ATVPDKIKX0DER&sshmPath=shipping-rates#aag_shipping\",\"Return Policy\":\"return policy\",\"Return Policy_link\":\"https://www.amazon.com/gp/aag/details/ref=olp_merch_return_2/146-7621390-2922011?ie=UTF8&asin=B008PPZO94&seller=ATVPDKIKX0DER&sshmPath=returns#aag_returns\",\"Size Medium\":\"TLIC-Distribution\",\"Size Medium_link\":\"https://www.amazon.com/gp/aag/main/ref=olp_merch_name_2/146-7621390-2922011?ie=UTF8&asin=B008PPZO94&isAmazonFulfilled=1&seller=A3PPNQGNI2KG7A\",\"100positive\":\"over the past 12 months. (279 total ratings)\",\"Add To Cart\":\"from seller TLIC-Distribution and price $35.92\"}";
// My Test data
//            $data = "{\"title\": \"Test 1\", \"price\": \"1.11\", \"count\": \"42\"}\n" .
//                "{\"title\": \"Test 2\", \"price\": 2.22, \"count\": 42}\n" .
//                "{\"title\": \"Test 3\", \"price\": 3.33, \"count\": 42}\n" .
//                "{\"title\": \"Test 4\", \"price\": 4.44, \"count\": 42}\n" .
//                "{\"title\": \"Test 5\", \"price\": 5.55, \"count\": 42}\n" .
//                "{\"title\": \"Test 6\", \"price\": 6.66, \"count\": 42}\n" .
//                "{\"title\": \"Test 7\", \"price\": 7.77, \"count\": 42}\n" .
//                "{\"title\": \"Test 8\", \"price\": 8.88, \"count\": 42}\n" .
//                "{\"title\": \"Test 9\", \"price\": 9.99, \"count\": 42}\n" .
//                "{\"title\": \"Test 10\", \"price\": 10.10, \"count\": 42}\n" .
//                "{\"title\": \"Test 11\", \"price\": 11.11, \"count\": 42}\n" .
//                "{\"title\": \"Test 12\", \"price\": 12.12, \"count\": 42}\n" .
//                "{\"title\": \"Test 12\", \"price\": 13.13, \"count\": 42}\n" .
//                "{\"title\": \"Test 13\", \"price\": 14.14, \"count\": 42}";

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
