<?php

namespace SaasPayments;

use \GuzzleHttp\Psr7;
use \GuzzleHttp\Exception\RequestException;

class SaasPayments
{
    /**
     * Pay with bolt default host
     *
     * @var string
     */
    const PWB_DEFAULT_HOST = "https://payments.withbolt.com";
    

    /**
     * List of error messages
     *
     * @var array
     */
    const ERRORS_LIST = [
        'ERROR_SHARED_KEY' => "'shared_key' must be provided",
        'ERROR_SECRET_KEY' => "'secret_key' must be provided",
        'ERROR_METHOD_NAME' => "'method_name' must be provided",
        'ERROR_SETTINGS' => "'settings' array must be provided",
        'ERROR_INSTANCE_KEY' => "'instance_key' must be provided",
        'ERROR_PAYMENT_KEY' => "'payment_key' must be provided",
        'ERROR_REFUND_KEY' => "'refund_key' must be provided",
        'ERROR_WEBHOOK' => "'webhook body' invalid, are you sure this came from Bolt?",
    ];

    /**
     * Shared Key to connect to the API
     *
     * @var string
     */
    protected static $shared_key;

    /**
     * Secret Key to connect to the API
     *
     * @var string
     */
    protected static $secret_key;

    /**
     * API host
     *
     * @var string
     */
    protected static $api_host;

    /**
     * GuzzleClient instance
     *
     * @var object
     */
    protected static $guzzle_client;
    
     /**
     * Configure the API with required credentials.
     *
     * Requires an array to be passed in with the following keys:
     *
     * - shared_key
     * - secret_key
     *
     * @param array $settings
     * @throws \Exception
     */

    public function __construct(array $settings = [])
    {
        if (!isset($settings['shared_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SHARED_KEY']);
        }

        if (!isset($settings['secret_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SECRET_KEY']);
        }

        self::$shared_key = $settings['shared_key'];
        self::$secret_key = $settings['secret_key'];
        self::$api_host = isset($settings['host']) ? $settings['host'] : self::PWB_DEFAULT_HOST;
        self::$guzzle_client = new \GuzzleHttp\Client();
    }

    /**
     * Generate signature format
     *
     * Requires a string methodName and array settings to be passed in:
     *
     * - methodName
     * - settings
     *
     * @param string $methodName
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    protected static function _sign(string $methodName, array $settings, string $timestamp = null)
    {
        if (!isset(self::$shared_key)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SHARED_KEY']);
        }

        if (!isset(self::$secret_key)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SECRET_KEY']);
        }

        if (!isset($methodName)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_METHOD_NAME']);
        }

        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        $timestamp = isset($timestamp) ? $timestamp : time() * 1000;

        unset($settings['host']);
        unset($settings['nonce']);
        
        $signatureBody = json_encode($settings);
        
        $bodyReplacedBackSlash = str_replace('\/','/', ($methodName . $signatureBody));
        return self::$shared_key . "-" . $timestamp . "-" . md5($timestamp . $bodyReplacedBackSlash . self::$secret_key);
    }

    /**
     * Get webhook
     *
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function getWebhook(array $settings)
    {
        if (!isset($settings) || !$settings['application'] || !$settings['instance_key']) {
            throw new \Exception(self::ERRORS_LIST['ERROR_WEBHOOK']);
        }
                
        $url = (isset($settings['payment']) ? '/payments/' . $settings['payment'] : 
                (strpos($settings['action'], 'REFUND.') === 0 ? '/refunds/' . $settings['transaction'] : null));

        if($url) {
            $url = self::$api_host . '/c/api/instances/' . $settings['instance_key'] . $url;
            
            $res = self::$guzzle_client->request('GET', $url, [
                'auth' => [
                    self::$secret_key,
                    ''
                ]
            ]);
    
            return $res->getBody();
        }
        
        return null;
    }
}