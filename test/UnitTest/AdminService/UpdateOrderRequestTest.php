<?php

$root = realpath(dirname(__FILE__));
require_once $root . '/../../../src/Includes.php';

$root = realpath(dirname(__FILE__));
require_once $root . '/../../TestUtil.php';

/**
 * @author Kristian Grossman-Madsen for Svea Webpay
 */
class UpdateOrderRequestTest extends \PHPUnit_Framework_TestCase {

    /**
     * req: SveaOrderId, ClientId
     * update: Notes, ClientOrderNumber
     */
    public function test_updateorder_clientnr() {
        $config = Svea\SveaConfig::getDefaultConfig();
        $request = WebPayAdmin::updateOrder($config)//only need clientid
              ->setCountryCode('SE') //req for config
              ->setOrderId('test')
              ->updateInvoiceOrder()
              ->prepareRequest();
      print_r($request);
        $this->assertEquals(80, $request->UpdatedOrderRows->enc_value->enc_value[0]->enc_value->PricePerUnit->enc_value);
        $this->assertFalse($request->UpdatedOrderRows->enc_value->enc_value[0]->enc_value->PriceIncludingVat->enc_value);
    }
    public function test_updateorder_addnotes() {
        $config = Svea\SveaConfig::getDefaultConfig();
        $request = WebPayAdmin::updateOrder($config)//only need clientid
              ->setCountryCode('SE') //req for config
              ->setOrderId('test')
              ->updateInvoiceOrder()
              ->prepareRequest();
      print_r($request);
        $this->assertEquals(80, $request->UpdatedOrderRows->enc_value->enc_value[0]->enc_value->PricePerUnit->enc_value);
        $this->assertFalse($request->UpdatedOrderRows->enc_value->enc_value[0]->enc_value->PriceIncludingVat->enc_value);
    }

}