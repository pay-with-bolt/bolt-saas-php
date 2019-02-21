<?php

namespace SaasPayments;

use SaasPayments\SaasPayments;

class Refund extends SaasPayments
{
    
    public function __construct(array $settings = [])
    {
        parent::__construct($settings);
    }
    
    /**
     * Refund
     *
     * Requires an array to be passed in with the following keys:
     *
     * - instance_key
     * 
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function doRefund(array $settings)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        if (!isset($settings['instance_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_INSTANCE_KEY']);
        }

        $url = self::$api_host . '/c/api/instances/' . $settings['instance_key'] . '/payments/' . $settings['payment'] . '/refund';

        try {
            $res = self::$guzzle_client->request('POST', $url, [
                'auth' => [
                    self::$secret_key,
                    ''
                ],
                'json' => [
                    "refund" => [
                        "amount" => $settings['amount'],
                        "reason" => $settings['reason']
                    ]
                ]
            ]);

            return $res->getBody();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return Psr7\str($e->getResponse());
            }
        }
    }

    /**
     * Get refund
     *
     * * * Requires an array to be passed in with the following keys:
     *
     * - instance_key
     * - refund_key
     * 
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function getRefund(array $settings)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        if (!isset($settings['instance_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_INSTANCE_KEY']);
        }

        if (!isset($settings['refund_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_REFUND_KEY']);
        }

        $url = self::$api_host . '/c/api/instances/' . $settings['instance_key'] . '/refunds/' . $settings['refund_key'];
        $res = self::$guzzle_client->request('GET', $url, [
            'auth' => [
                self::$secret_key,
                ''
            ]
        ]);

        return $res->getBody();
    }
}