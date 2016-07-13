<?php
namespace Svea;

use Svea\WebPay\Config\SveaConfig;
use Svea\WebPay\Constant\SystemPaymentMethod;

$root = realpath(dirname(__FILE__));
require_once $root . '/../../../../test/UnitTest/BuildOrder/OrderBuilderTest.php';

$root = realpath(dirname(__FILE__));
require_once $root . '/../../../TestUtil.php';

/**
 * @author Anneli Halld'n, Daniel Brolund for Svea Webpay
 */
class PayPagePaymentTest extends \PHPUnit_Framework_TestCase {

    public function testBuildPayPagePaymentWithExcludepaymentMethods() {
        $config = SveaConfig::getDefaultConfig();
        $rowFactory = new WebPay\Test\TestUtil();
        $form = WebPay\WebPay::createOrder($config)
            ->addOrderRow(WebPay\Test\TestUtil::createOrderRow())
            ->run($rowFactory->buildShippingFee())
            ->addDiscount(WebPay\WebPayItem::relativeDiscount()
                     ->setDiscountId("1")
                    ->setDiscountPercent(50)
                    ->setUnit("st")
                    ->setName('Relative')
                    ->setDescription("RelativeDiscount")
            )
            ->addCustomerDetails(WebPay\WebPayItem::individualCustomer()->setNationalIdNumber(194605092222))
            ->setCountryCode("SE")
            ->setClientOrderNumber("33")
            ->setOrderDate("2012-12-12")
            ->setCurrency("SEK")
            ->usePayPage()
                ->setReturnUrl("http://myurl.se")
                ->excludePaymentMethods(WebPay\Constant\PaymentMethod::INVOICE, WebPay\Constant\PaymentMethod::KORTCERT)
                ->getPaymentForm();

        $xmlMessage = new \SimpleXMLElement($form->xmlMessage);
        //test values are as expected avter transforming xml to php object
        $this->assertEquals('SEK', $xmlMessage->currency);
        $this->assertEquals('18750', $xmlMessage->amount);
        $this->assertEquals('3750', $xmlMessage->vat); //may change when we recaltulate in Cartpymentclass
        $this->assertEquals('12500', $xmlMessage->orderrows->row[0]->amount);
        $this->assertEquals('6250', $xmlMessage->orderrows->row[1]->amount);
        $this->assertEquals('-12500', $xmlMessage->orderrows->row[2]->amount);
        //  $this->assertEquals(\Svea\WebPay\Constant\PaymentMethod::KORTCERT,$xmlMessage->paymentMethod);
        $this->assertEquals(SystemPaymentMethod::INVOICE_SE, $xmlMessage->excludepaymentmethods->exclude[0]);
    }

    public function testpayPagePaymentExcludeCardPayments() {
        $config = SveaConfig::getDefaultConfig();
        $rowFactory = new WebPay\Test\TestUtil();
        $form = WebPay\WebPay::createOrder($config)
            ->addOrderRow(WebPay\Test\TestUtil::createOrderRow())
            ->run($rowFactory->buildShippingFee())
            ->addDiscount(WebPay\WebPayItem::relativeDiscount()
                    ->setDiscountId("1")
                    ->setDiscountPercent(50)
                    ->setUnit("st")
                    ->setName('Relative')
                    ->setDescription("RelativeDiscount")
            )
            ->addCustomerDetails(WebPay\WebPayItem::individualCustomer()->setNationalIdNumber(194605092222))
            ->setCountryCode("SE")
            ->setClientOrderNumber("33")
            ->setOrderDate("2012-12-12")
            ->setCurrency("SEK")
            ->usePayPage()
                ->setReturnUrl("http://myurl.se")
                ->excludeCardPaymentMethods()
                ->getPaymentForm();

        $xmlMessage = new \SimpleXMLElement($form->xmlMessage);
        $this->assertEquals(WebPay\Constant\PaymentMethod::KORTCERT, $xmlMessage->excludepaymentmethods->exclude[0]);
    }

    public function testExcludeDirectPaymentMethods() {
        $config = SveaConfig::getDefaultConfig();
        $rowFactory = new WebPay\Test\TestUtil();
        $form = WebPay\WebPay::createOrder($config)
            ->addOrderRow(WebPay\Test\TestUtil::createOrderRow())
            ->run($rowFactory->buildShippingFee())
            ->addDiscount(WebPay\WebPayItem::relativeDiscount()
                    ->setDiscountId("1")
                    ->setDiscountPercent(50)
                    ->setUnit("st")
                    ->setName('Relative')
                    ->setDescription("RelativeDiscount")
            )
            ->addCustomerDetails(WebPay\WebPayItem::individualCustomer()->setNationalIdNumber(194605092222))
            ->setCountryCode("SE")
            ->setClientOrderNumber("33")
            ->setOrderDate("2012-12-12")
            ->setCurrency("SEK")
            ->usePayPage()
                ->setReturnUrl("http://myurl.se")
                ->excludeDirectPaymentMethods()
                ->getPaymentForm();

        $xmlMessage = new \SimpleXMLElement($form->xmlMessage);
        $this->assertEquals(WebPay\Constant\PaymentMethod::BANKAXESS, $xmlMessage->excludepaymentmethods->exclude[0]);
    }

    public function testpayPagePaymentIncludePaymentMethods() {
        $config = SveaConfig::getDefaultConfig();
        $rowFactory = new WebPay\Test\TestUtil();
        $form = WebPay\WebPay::createOrder($config)
            ->addOrderRow(WebPay\Test\TestUtil::createOrderRow())
            ->run($rowFactory->buildShippingFee())
            ->addDiscount(WebPay\WebPayItem::relativeDiscount()
                    ->setDiscountId("1")
                    ->setDiscountPercent(50)
                    ->setUnit("st")
                    ->setName('Relative')
                    ->setDescription("RelativeDiscount")
            )
            ->addCustomerDetails(WebPay\WebPayItem::individualCustomer()->setNationalIdNumber(194605092222))
            ->setCountryCode("SE")
            ->setClientOrderNumber("33")
            ->setOrderDate("2012-12-12")
            ->setCurrency("SEK")
            ->usePayPage()
                ->setReturnUrl("http://myurl.se")
                ->includePaymentMethods(WebPay\Constant\PaymentMethod::KORTCERT, WebPay\Constant\PaymentMethod::SKRILL)
                ->getPaymentForm();

        $xmlMessage = new \SimpleXMLElement($form->xmlMessage);
        //check to see if the first value is one of the excluded ones
        $this->assertEquals(SystemPaymentMethod::BANKAXESS, $xmlMessage->excludepaymentmethods->exclude[0]);
    }

    public function testBuildPayPagePaymentVatIsCero() {
        $config = SveaConfig::getDefaultConfig();
        $rowFactory = new WebPay\Test\TestUtil();
         $form = WebPay\WebPay::createOrder($config)
                ->addOrderRow(WebPay\WebPayItem::orderRow()
                    ->setQuantity(2)
                    ->setAmountExVat(100.00)
                    ->setName('Prod')
                    ->setVatPercent(0)
                )
                ->setCountryCode("SE")
                ->setClientOrderNumber("33")
                ->setCurrency("SEK")
                ->usePayPage()
                    ->setReturnUrl("myurl")
                    ->getPaymentForm();


        $xmlMessage = new \SimpleXMLElement($form->xmlMessage);
        //test values are as expected avter transforming xml to php object
        $this->assertEquals('SEK', $xmlMessage->currency);
    }
    
    public function testBuildPayPagePaymentCallBackUrl() {
        $config = SveaConfig::getDefaultConfig();
        $rowFactory = new WebPay\Test\TestUtil();
         $form = WebPay\WebPay::createOrder($config)
                ->addOrderRow(WebPay\WebPayItem::orderRow()
                    ->setQuantity(2)
                    ->setAmountExVat(100.00)
                    ->setName('Prod')
                    ->setVatPercent(0)
                )
                ->setCountryCode("SE")
                ->setClientOrderNumber("33")
                ->setCurrency("SEK")
                ->usePayPage()
                    ->setReturnUrl("myurl")
                    ->setCallbackUrl("http://myurl.se")
                    ->getPaymentForm();

        $xmlMessage = new \SimpleXMLElement($form->xmlMessage);
        $this->assertEquals("http://myurl.se", $xmlMessage->callbackurl);
    }
}
