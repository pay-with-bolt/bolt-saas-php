<?php

namespace SaasPayments;

use SaasPayments\SaasPayments;

class Setup extends SaasPayments
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
    protected static function _convertSetupSettings(array $settings)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        return [
            "instanceKey" => isset($settings['instance_key']) ? $settings['instance_key'] : null,
			"companyName" => isset($settings['company_name']) ? $settings['company_name'] : null,
			"contactName" => isset($settings['contact_name']) ? $settings['contact_name'] : null,
			"contactPhone" => isset($settings['contact_phone']) ? $settings['contact_phone'] : null,
			"contactEmail" => isset($settings['contact_email']) ? $settings['contact_email'] : null,
			"countryCode" => isset($settings['company_country']) ? $settings['company_country'] : null,
			"referralCode" => false
        ];
    }

     /**
     * Generate setup url to access bolt app
     *
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function setupUrl(array $settings)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        if (!isset($settings['instance_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_INSTANCE_KEY']);
        }

        $settingsTmp = self::_convertSetupSettings($settings);
        $signature = self::_sign('doSetup', $settingsTmp);
        $query = base64_encode(json_encode($settingsTmp));
        $unsignedOptions = base64_encode(json_encode(['signature' => $signature]));
        $channel = isset($settings['channel_key']) ? $settings['channel_key'] : 'setup';
        
        return self::$api_host . '/c/' . $channel . '/#/api/setup/' . $query . '/' . $unsignedOptions;
    }

    /**
     * Generate setup encoded string
     *
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function setupButton(array $settings)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        if (!isset($settings['instance_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_INSTANCE_KEY']);
        }

        $settings = self::_convertSetupSettings($settings);
        return base64_encode(json_encode($settings));
    }

    /**
     * Generate signature encoded string
     *
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function setupSignature(array $settings, string $timestamp = null)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        if (!isset($settings['instance_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_INSTANCE_KEY']);
        }

        $settings = self::_convertSetupSettings($settings);

        return self::_sign('doSetup', $settings, $timestamp);
    }
    
    /**
     * Get client setup data
     *
     * @param array $settings
     * @throws \Exception
     * @return string
     */
    public function getSetup(array $settings)
    {
        if (!isset($settings)) {
            throw new \Exception(self::ERRORS_LIST['ERROR_SETTINGS']);
        }

        if (!isset($settings['instance_key'])) {
            throw new \Exception(self::ERRORS_LIST['ERROR_INSTANCE_KEY']);
        }

        $url = self::$api_host . '/c/api/instances/' . $settings['instance_key'] . '/setup/payments';
        $res = self::$guzzle_client->request('GET', $url, [
            'auth' => [
                self::$secret_key,
                ''
            ]
        ]);

        return $res->getBody();
    }
}