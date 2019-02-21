<?php

namespace SaasPayments;

use SaasPayments\SaasPayments;

class Payment extends SaasPayments
{
    
    public function __construct(array $settings = [])
    {
        parent::__construct($settings);
    }

    /**
     * Convert settings key names to API expected format 
     *
     * @param array $settings
     * @throws \Exception
     * @return array
     */
    protected static function _convertPaymentSettings(array $settings)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        $account = $settings['account'];
		$accountKey = is_string($account) ? $account : (isset($account['id']) ? $account['id'] : null);

        $accSettings = [
            "instanceKey" => $settings['instance_key'],
			"currency" => $settings['currency'],
			"amount" => $settings['amount'],
			"defaultAmount" => isset($settings['default_amount']) ? $settings['default_amount'] : null,
			"altKey" => isset($settings['alt_key']) ? $settings['alt_key'] : null,
			"orderDesc" => isset($settings['description']) ? $settings['description'] : null,
			"accountKey" => $accountKey,
			"crm" => isset($account) ? [
				"address" => isset($account['address']) ? [
					"country" => isset($account['address']['country']) ? $account['address']['country'] : null,
					"line1" => isset($account['address']['line1']) ? $account['address']['line1'] : null,
					"line2" => isset($account['address']['line2']) ? $account['address']['line2'] : null,
					"line3" => isset($account['address']['line3']) ? $account['address']['line3'] : null,
					"line4" => isset($account['address']['line4']) ? $account['address']['line4'] : null,
					"line5" => isset($account['address']['line5']) ? $account['address']['line5'] : null,
					"line6" => isset($account['address']['line6']) ? $account['address']['line6'] : null,
                 ] : null,
				"tag" => isset($account['alt_key']) ? $account['alt_key'] : null,
				"firstname" => isset($account['first_name']) ? $account['first_name'] : null,
				"lastname" => isset($account['last_name']) ? $account['last_name'] : null,
				"company" => isset($account['company']) ? $account['company'] : null,
				"email" => isset($account['email']) ? $account['email'] : null,
				"phone" => isset($account['phone']) ? $account['phone'] : null,
             ] : null,

			"onSuccessEmail" => isset($settings['success_url']) ? $settings['success_url'] : null,
			"channelKey" => isset($settings['channel_key']) ? $settings['channel_key'] : "web",
			"frequency" => "ONEOFF",
			"disableMyDetails" => "TRUE",
			"nonce" => isset($settings['nonce']) ? $settings['nonce'] : ("bolt_" . (time() * 1000))
        ];

        unset($accSettings['account']);

        return $accSettings;
    }

    /**
     * Generate payment encoded string
     *
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function paymentButton(array $settings)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        if (!isset($settings['instance_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_INSTANCE_KEY']);
        }

        $settings = self::_convertPaymentSettings($settings);
        return base64_encode(json_encode($settings));
    }

    /**
     * Generate payment encoded string
     *
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function paymentSignature(array $settings, string $timestamp = null)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        if (!isset($settings['instance_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_INSTANCE_KEY']);
        }

        $settings = self::_convertPaymentSettings($settings);
        return self::_sign('doPayment', $settings, $timestamp);
    }

    /**
     * Generate payment url
     *
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function paymentUrl(array $settings)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        if (!isset($settings['instance_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_INSTANCE_KEY']);
        }

        $settingsTmp = self::_convertPaymentSettings($settings);

        $signature = self::_sign('doPayment', $settingsTmp);

        $query = base64_encode(json_encode($settingsTmp));
        $channel = isset($settings['channel_key']) ? $settings['channel_key'] : 'web';

        return self::$api_host . '/c/' . $channel . '/api/doPayment?q=' . $query . "&signature=" . $signature;
    }
    
     /**
     * Make payment
     *
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function doPayment(array $settings)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        if (!isset($settings['instance_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_INSTANCE_KEY']);
        }

        $url = self::$api_host . '/c/api/instances/' . $settings['instance_key'] . '/payments';
        unset($settings['instance_key']);

        try {
            $res = self::$guzzle_client->request('POST', $url, [
                'auth' => [
                    self::$secret_key,
                    ''
                ],
                'json' => [
                    "payment" => $settings
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
     * Get payment
     *
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function getPayment(array $settings)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        if (!isset($settings['instance_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_INSTANCE_KEY']);
        }

        if (!isset($settings['payment_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_PAYMENT_KEY']);
        }

        $url = self::$api_host . '/c/api/instances/' . $settings['instance_key'] . '/payments/' . $settings['payment_key'];
        $res = self::$guzzle_client->request('GET', $url, [
            'auth' => [
                self::$secret_key,
                ''
            ]
        ]);

        return $res->getBody();
    }
}