<?php
use SaasPayments\Setup;

class SetupTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    private static $settings = [
        'shared_key' => '1186_62503',
        'secret_key' => '696344858da5c4900d05b6e5acde4b0a'
    ];

    private static $options = [
        "instance_key" => "22222",
        "company_name" => "Jim's Shoe Shop",
        "company_country" => "US",
        "contact_name" => "Jim Doe",
        "contact_phone" => "+1614123456",
        "contact_email" => "jim@email.com",
    ];

    private static $setup;

    const BUTTON_CODE = 'eyJpbnN0YW5jZUtleSI6IjIyMjIyIiwiY29tcGFueU5hbWUiOiJKaW0ncyBTaG9lIFNob3AiLCJjb250YWN0TmFtZSI6IkppbSBEb2UiLCJjb250YWN0UGhvbmUiOiIrMTYxNDEyMzQ1NiIsImNvbnRhY3RFbWFpbCI6ImppbUBlbWFpbC5jb20iLCJjb3VudHJ5Q29kZSI6IlVTIiwicmVmZXJyYWxDb2RlIjpmYWxzZX0=';
    const SIGNATURE_CODE = '1186_62503-123-a70d136255f15d00acc67a7df6867995';
    const TIMESTAMP = '123';
    const SETUP_URL = "https://payments.withbolt.com/c/setup/#/api/setup/eyJpbnN0YW5jZUtleSI6IjIyMjIyIiwiY29tcGFueU5hbWUiOiJKaW0ncyBTaG9lIFNob3AiLCJjb250YWN0TmFtZSI6IkppbSBEb2UiLCJjb250YWN0UGhvbmUiOiIrMTYxNDEyMzQ1NiIsImNvbnRhY3RFbWFpbCI6ImppbUBlbWFpbC5jb20iLCJjb3VudHJ5Q29kZSI6IlVTIiwicmVmZXJyYWxDb2RlIjpmYWxzZX0=";
    
    protected function _before()
    {
        self::$setup = new Setup(self::$settings);
    }

    // tests
    public function testSetupButton()
    {
        $this->assertEquals(self::BUTTON_CODE, self::$setup->setupButton(self::$options));
    }

    public function testSetupSignature()
    {
        $this->assertEquals(self::SIGNATURE_CODE, self::$setup->setupSignature(self::$options, self::TIMESTAMP));
    }

    public function testSetupUrl()
    {
        $arr = explode('/', self::$setup->setupUrl(self::$options));
        array_pop($arr);
        $urlWithoutSignature = implode('/', $arr);
        
        $this->assertEquals(self::SETUP_URL, $urlWithoutSignature);
    }

    public function testGetSetup()
    {
        $setup = self::$setup->getSetup(['instance_key'=> self::$options['instance_key']]);
        $setup = json_decode($setup);
        $this->assertTrue($setup->payments_ready);
    }
}