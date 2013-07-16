<?php

$root = realpath(dirname(__FILE__));
require_once $root . '/../../../../test/UnitTest/BuildOrder/OrderBuilderTest.php';
require_once $root . '/../../../../test/UnitTest/BuildOrder/TestRowFactory.php';

/**
 * Description of PaymentPlanTest
 *
 * @author Anneli Halld'n, Daniel Brolund for Svea Webpay
 */
class PaymentPlanTest extends PHPUnit_Framework_TestCase {

      /**
     * Use to get paymentPlanParams to be able to test PaymentPlanRequest
     * @return type
     */
    public function getGetPaymentPlanParamsForTesting() {
        $addressRequest = WebPay::getPaymentPlanParams();
        $response = $addressRequest
                //->setTestmode()()
                ->setCountryCode("SE")
                ->doRequest();
         return $response->campaignCodes[0]->campaignCode;
    }

    public function testPaymentPlanRequestObjectSpecifics() {
        $rowFactory = new TestRowFactory();
        $request = WebPay::createOrder()
                ->addOrderRow(Item::orderRow()
                    ->setArticleNumber(1)
                    ->setQuantity(2)
                    ->setAmountExVat(100.00)
                    ->setDescription("Specification")
                    ->setName('Prod')
                    ->setUnit("st")
                    ->setVatPercent(25)
                    ->setDiscountPercent(0)
                    )
                ->run($rowFactory->buildShippingFee())
                ->addCustomerDetails(Item::individualCustomer()->setNationalIdNumber(194605092222))
                ->setCountryCode("SE")
                ->setCustomerReference("33")
                ->setClientOrderNumber("nr26")
                ->setOrderDate("2012-12-12")
                ->setCurrency("SEK")
                ->usePaymentPlanPayment("camp1")// returnerar InvoiceOrder object
                    ->prepareRequest();

        $this->assertEquals('camp1', $request->request->CreateOrderInformation->CreatePaymentPlanDetails['CampaignCode']);
        $this->assertEquals(0, $request->request->CreateOrderInformation->CreatePaymentPlanDetails['SendAutomaticGiroPaymentForm']);
    }

    public function testInvoiceRequestObjectWithRelativeDiscountOnTwoProducts() {
        $request = WebPay::createOrder()
                //->setTestmode()()
                ->addOrderRow(Item::orderRow()
                    ->setArticleNumber(1)
                    ->setQuantity(2)
                    ->setAmountExVat(240.00)
                    ->setAmountIncVat(300)
                    ->setDescription("CD")
                    )
                ->addDiscount(Item::relativeDiscount()
                    ->setDiscountId("1")
                     ->setDiscountPercent(10)
                     ->setDescription("RelativeDiscount")
                    )
                ->addCustomerDetails(Item::individualCustomer()->setNationalIdNumber(194605092222))
                ->setCountryCode("SE")
                ->setCustomerReference("33")
                ->setOrderDate("2012-12-12")
                ->setCurrency("SEK")
                    ->useInvoicePayment()
                        ->prepareRequest();
        //couponrow
        $this->assertEquals('1', $request->request->CreateOrderInformation->OrderRows['OrderRow'][1]->ArticleNumber);
        $this->assertEquals('RelativeDiscount', $request->request->CreateOrderInformation->OrderRows['OrderRow'][1]->Description);
        $this->assertEquals(-48.00, $request->request->CreateOrderInformation->OrderRows['OrderRow'][1]->PricePerUnit);
        $this->assertEquals(1, $request->request->CreateOrderInformation->OrderRows['OrderRow'][1]->NumberOfUnits);
        $this->assertEquals('', $request->request->CreateOrderInformation->OrderRows['OrderRow'][1]->Unit);
        $this->assertEquals(25, $request->request->CreateOrderInformation->OrderRows['OrderRow'][1]->VatPercent);
        $this->assertEquals(0, $request->request->CreateOrderInformation->OrderRows['OrderRow'][1]->DiscountPercent);
    }
    
    public function testPaymentPlanWithPriceAsDecimal() {
        $campaign = $this->getGetPaymentPlanParamsForTesting();
        $request = WebPay::createOrder()
                //->setTestmode()()
                ->addOrderRow(Item::orderRow()
                    ->setArticleNumber(1)
                    ->setQuantity(2)
                    ->setAmountExVat(240.00)
                    ->setAmountIncVat(300)
                    ->setDescription("CD")
                    )
                ->addCustomerDetails(Item::individualCustomer()->setNationalIdNumber(194605092222))
                ->setCountryCode("SE")
                ->setCustomerReference("33")
                ->setOrderDate("2012-12-12")
                ->setCurrency("SEK")
                    ->usePaymentPlanPayment($campaign)
                        ->prepareRequest();
        //couponrow

        $this->assertEquals(240.00, $request->request->CreateOrderInformation->OrderRows['OrderRow'][0]->PricePerUnit);
        $this->assertEquals(2, $request->request->CreateOrderInformation->OrderRows['OrderRow'][0]->NumberOfUnits);
        $this->assertEquals(25, $request->request->CreateOrderInformation->OrderRows['OrderRow'][0]->VatPercent);
    }
}

?>
