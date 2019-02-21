<?php
use SaasPayments\Payment;
use SaasPayments\Refund;

class PaymentTest extends \Codeception\Test\Unit
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
        "currency" => "GBP",
        "amount" => 1, 
        "alt_key" => "1234",
        "description" => "About the payment",
        "source" => "moto",
        "account" => [
            "crm_key" => "ID1",
            "first_name" => "James",
            "last_name" => "Boer",
            "company" => "Jinky's Lollie Shop",
            "email" => "james@jinkies.com",
            "phone" => "0712345678",
            "address" => [  
                "line1" => "1 High St",
                "line4" => "London",
                "line5" => "W10 6RU",
                "country" => "GB"
            ]
        ],
        "success_url" => "https://jinkies.com/receipt",
        "nonce" => '123',
    ];

    private static $refundOptions = [
        'instance_key' => "22222",
        'payment' => "pay_2270_1363",
        'amount' =>  1,
        'reason' => "test"
    ];

    private static $paymentWebhookOptions = [
        "action" => "PAYMENT.SUCCESS",
        "application" => "TEST",
        "payment" => null,
        "instance_key" => null,
    ];

    private static $refundWebhookOptions = [
        "action" => "REFUND.SUCCESS",
        "application" => "TEST",
        "instance_key" => null,
        'transaction' => null,
    ];

    private static $payment;
    private static $paymentObjectTmp;
    private static $refund;
    private static $refundObjectTmp;

    const BUTTON_CODE = 'eyJpbnN0YW5jZUtleSI6IjIyMjIyIiwiY3VycmVuY3kiOiJHQlAiLCJhbW91bnQiOjEsImRlZmF1bHRBbW91bnQiOm51bGwsImFsdEtleSI6IjEyMzQiLCJvcmRlckRlc2MiOiJBYm91dCB0aGUgcGF5bWVudCIsImFjY291bnRLZXkiOm51bGwsImNybSI6eyJhZGRyZXNzIjp7ImNvdW50cnkiOiJHQiIsImxpbmUxIjoiMSBIaWdoIFN0IiwibGluZTIiOm51bGwsImxpbmUzIjpudWxsLCJsaW5lNCI6IkxvbmRvbiIsImxpbmU1IjoiVzEwIDZSVSIsImxpbmU2IjpudWxsfSwidGFnIjpudWxsLCJmaXJzdG5hbWUiOiJKYW1lcyIsImxhc3RuYW1lIjoiQm9lciIsImNvbXBhbnkiOiJKaW5reSdzIExvbGxpZSBTaG9wIiwiZW1haWwiOiJqYW1lc0BqaW5raWVzLmNvbSIsInBob25lIjoiMDcxMjM0NTY3OCJ9LCJvblN1Y2Nlc3NFbWFpbCI6Imh0dHBzOlwvXC9qaW5raWVzLmNvbVwvcmVjZWlwdCIsImNoYW5uZWxLZXkiOiJ3ZWIiLCJmcmVxdWVuY3kiOiJPTkVPRkYiLCJkaXNhYmxlTXlEZXRhaWxzIjoiVFJVRSIsIm5vbmNlIjoiMTIzIn0=';
    const SIGNATURE_CODE = '1186_62503-123-5c3e78cc95b16efeeef6bef257a9df4b';
    const TIMESTAMP = '123';
    const SETUP_URL = "https://payments.withbolt.com/c/web/api/doPayment?q=eyJpbnN0YW5jZUtleSI6IjIyMjIyIiwiY3VycmVuY3kiOiJHQlAiLCJhbW91bnQiOjEsImRlZmF1bHRBbW91bnQiOm51bGwsImFsdEtleSI6IjEyMzQiLCJvcmRlckRlc2MiOiJBYm91dCB0aGUgcGF5bWVudCIsImFjY291bnRLZXkiOm51bGwsImNybSI6eyJhZGRyZXNzIjp7ImNvdW50cnkiOiJHQiIsImxpbmUxIjoiMSBIaWdoIFN0IiwibGluZTIiOm51bGwsImxpbmUzIjpudWxsLCJsaW5lNCI6IkxvbmRvbiIsImxpbmU1IjoiVzEwIDZSVSIsImxpbmU2IjpudWxsfSwidGFnIjpudWxsLCJmaXJzdG5hbWUiOiJKYW1lcyIsImxhc3RuYW1lIjoiQm9lciIsImNvbXBhbnkiOiJKaW5reSdzIExvbGxpZSBTaG9wIiwiZW1haWwiOiJqYW1lc0BqaW5raWVzLmNvbSIsInBob25lIjoiMDcxMjM0NTY3OCJ9LCJvblN1Y2Nlc3NFbWFpbCI6Imh0dHBzOlwvXC9qaW5raWVzLmNvbVwvcmVjZWlwdCIsImNoYW5uZWxLZXkiOiJ3ZWIiLCJmcmVxdWVuY3kiOiJPTkVPRkYiLCJkaXNhYmxlTXlEZXRhaWxzIjoiVFJVRSIsIm5vbmNlIjoiMTIzIn0=";
    
    protected function _before()
    {
        self::$payment = new Payment(self::$settings);
        self::$refund = new Refund(self::$settings);
    }

    // tests
    public function testPaymentButton()
    {
        $this->assertEquals(self::BUTTON_CODE, self::$payment->paymentButton(self::$options));
    }

    public function testPaymentSignature()
    {
        $this->assertEquals(self::SIGNATURE_CODE, self::$payment->paymentSignature(self::$options, self::TIMESTAMP));
    }

    public function testPaymentUrl()
    {
        $arr = explode('&signature=', self::$payment->paymentUrl(self::$options));
        array_pop($arr);
        $urlWithoutSignature = implode('', $arr);
        
        $this->assertEquals(self::SETUP_URL, $urlWithoutSignature);
    }

    public function testDoPayment()
    {
        self::$options['account']['alt_key'] = 'ID1';
        self::$options['amount'] = 10;
        self::$options['payment_method'] = [
            'card_number' => "4242424242424242",
            'card_cvc' => "123",
            'card_expiry' => "10/20",
            'save_card' => true
        ];

        unset(self::$options['nonce']);
        self::$paymentObjectTmp = json_decode(self::$payment->doPayment(self::$options));
        $this->assertNotEmpty(self::$paymentObjectTmp->payment->id);
    }

    public function testGetPayment()
    {
        $getPayment = json_decode(self::$payment->getPayment(['instance_key' => '22222', 'payment_key' => self::$paymentObjectTmp->payment->id]));
        $this->assertEquals(self::$paymentObjectTmp->payment->id, $getPayment->payment->id);
    }
    
    public function testDoRefund()
    {
        self::$refundOptions['payment'] = self::$paymentObjectTmp->payment->id;
        self::$refundObjectTmp = json_decode(self::$refund->doRefund(self::$refundOptions));
                
        $this->assertEquals(self::$refundObjectTmp->refund->gateway_status, "APPROVED");
    }
    
    public function testGetRefund()
    {
        self::$refundOptions['payment'] = self::$paymentObjectTmp->payment->id;
        $refundObject = json_decode(self::$refund->getRefund(['instance_key' => '22222', 'refund_key' => self::$refundObjectTmp->refund->id]));
        
        $this->assertEquals(self::$refundObjectTmp->refund->id, $refundObject->refund->id);
    }
    
    public function testGetPaymentWebhook()
    {
        self::$paymentWebhookOptions['payment'] = self::$paymentObjectTmp->payment->id;
        self::$paymentWebhookOptions['instance_key'] = self::$options['instance_key'];
        $webhookPaymentObject = json_decode(self::$payment->getWebhook(self::$paymentWebhookOptions));
        
        $this->assertEquals($webhookPaymentObject->payment->id, self::$paymentWebhookOptions['payment']);
    }
    
    public function testGetRefundWebhook()
    {
        self::$refundWebhookOptions['transaction'] = self::$refundObjectTmp->refund->id;
        self::$refundWebhookOptions['instance_key'] = self::$options['instance_key'];
        $webhookRefundObject = json_decode(self::$payment->getWebhook(self::$refundWebhookOptions));
        
        // assert.equal(getWebhook.refund.id, refund_webhook.transaction, "getWebhook(refund) returned id");
        $this->assertEquals($webhookRefundObject->refund->id, self::$refundWebhookOptions['transaction']);
    }
}