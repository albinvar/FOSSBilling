<?php


namespace Box\Mod\Invoice;


class ServiceTaxTest extends \BBTestCase
{
    /**
     * @var \Box\Mod\Invoice\ServiceTax
     */
    protected $service = null;

    public function setup(): void
    {
        $this->service = new \Box\Mod\Invoice\ServiceTax();
    }

    public function testgetDi()
    {
        $di = new \Pimple\Container();
        $this->service->setDi($di);
        $getDi = $this->service->getDi();
        $this->assertEquals($di, $getDi);
    }

    public function testgetTaxRateForClientByCountryAndState()
    {
        $taxRateExpected = 0.21;
        $clientModel     = new \Model_Client();
        $clientModel->loadBean(new \DummyBean());

        $clientServiceMock = $this->getMockBuilder('\\' . \Box\Mod\Client\Service::class)
            ->getMock();
        $clientServiceMock->expects($this->atLeastOnce())
            ->method('isClientTaxable')
            ->will($this->returnValue(true));

        $taxModel = new \Model_Tax();
        $taxModel->loadBean(new \DummyBean());
        $taxModel->taxrate = $taxRateExpected;

        $dbMock = $this->getMockBuilder('\Box_Database')
            ->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('findOne')
            ->will($this->returnValue($taxModel));

        $di                = new \Pimple\Container();
        $di['mod_service'] = $di->protect(fn() => $clientServiceMock);
        $di['db']          = $dbMock;
        $this->service->setDi($di);

        $result = $this->service->getTaxRateForClient($clientModel);
        $this->assertIsFloat($result);
        $this->assertEquals($taxRateExpected, $result);
    }

    public function testgetTaxRateForClientByCountry()
    {
        $taxRateExpected = 0.21;
        $clientModel     = new \Model_Client();
        $clientModel->loadBean(new \DummyBean());

        $clientServiceMock = $this->getMockBuilder('\\' . \Box\Mod\Client\Service::class)
            ->getMock();
        $clientServiceMock->expects($this->atLeastOnce())
            ->method('isClientTaxable')
            ->will($this->returnValue(true));

        $taxModel = new \Model_Tax();
        $taxModel->loadBean(new \DummyBean());
        $taxModel->taxrate = $taxRateExpected;

        $dbMock = $this->getMockBuilder('\Box_Database')
            ->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('findOne')
            ->will($this->onConsecutiveCalls(null, $taxModel));

        $di                = new \Pimple\Container();
        $di['mod_service'] = $di->protect(fn() => $clientServiceMock);
        $di['db']          = $dbMock;
        $this->service->setDi($di);

        $result = $this->service->getTaxRateForClient($clientModel);
        $this->assertIsFloat($result);
        $this->assertEquals($taxRateExpected, $result);
    }

    public function testgetTaxRateForClient()
    {
        $taxRateExpected = 0.21;
        $clientModel     = new \Model_Client();
        $clientModel->loadBean(new \DummyBean());

        $clientServiceMock = $this->getMockBuilder('\\' . \Box\Mod\Client\Service::class)
            ->getMock();
        $clientServiceMock->expects($this->atLeastOnce())
            ->method('isClientTaxable')
            ->will($this->returnValue(true));

        $taxModel = new \Model_Tax();
        $taxModel->loadBean(new \DummyBean());
        $taxModel->taxrate = $taxRateExpected;

        $dbMock = $this->getMockBuilder('\Box_Database')
            ->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('findOne')
            ->will($this->onConsecutiveCalls(null, null, $taxModel));

        $di                = new \Pimple\Container();
        $di['mod_service'] = $di->protect(fn() => $clientServiceMock);
        $di['db']          = $dbMock;
        $this->service->setDi($di);

        $result = $this->service->getTaxRateForClient($clientModel);
        $this->assertIsFloat($result);
        $this->assertEquals($taxRateExpected, $result);
    }

    public function testgetTaxRateForClient_TaxWasNotFound()
    {
        $clientModel = new \Model_Client();
        $clientModel->loadBean(new \DummyBean());

        $clientServiceMock = $this->getMockBuilder('\\' . \Box\Mod\Client\Service::class)
            ->getMock();
        $clientServiceMock->expects($this->atLeastOnce())
            ->method('isClientTaxable')
            ->will($this->returnValue(true));

        $dbMock = $this->getMockBuilder('\Box_Database')
            ->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('findOne')
            ->will($this->onConsecutiveCalls(null, null, null));

        $di                = new \Pimple\Container();
        $di['mod_service'] = $di->protect(fn() => $clientServiceMock);
        $di['db']          = $dbMock;
        $this->service->setDi($di);

        $taxRateExpected = 0;
        $result          = $this->service->getTaxRateForClient($clientModel);
        $this->assertIsInt($result);
        $this->assertEquals($taxRateExpected, $result);
    }

    public function testgetTaxRateForClient_ClientIsNotTaxable()
    {
        $clientModel = new \Model_Client();
        $clientModel->loadBean(new \DummyBean());

        $clientServiceMock = $this->getMockBuilder('\\' . \Box\Mod\Client\Service::class)
            ->getMock();
        $clientServiceMock->expects($this->atLeastOnce())
            ->method('isClientTaxable')
            ->will($this->returnValue(false));

        $taxModel = new \Model_Tax();
        $taxModel->loadBean(new \DummyBean());

        $di                = new \Pimple\Container();
        $di['mod_service'] = $di->protect(fn() => $clientServiceMock);
        $this->service->setDi($di);

        $taxRateExpected = 0;
        $result          = $this->service->getTaxRateForClient($clientModel);
        $this->assertIsInt($result);
        $this->assertEquals($taxRateExpected, $result);
    }

    public function testgetTaxWhenTaxRateIsZero()
    {
        $invoiceModel = new \Model_Invoice();
        $invoiceModel->loadBean(new \DummyBean());
        $invoiceModel->taxrate = 0;

        $result = $this->service->getTax($invoiceModel);
        $this->assertIsInt($result);
        $this->assertEquals(0, $result);
    }

    public function testgetTax()
    {
        $invoiceModel = new \Model_Invoice();
        $invoiceModel->loadBean(new \DummyBean());
        $invoiceModel->taxrate = 15;

        $invoiceItemModel = new \Model_InvoiceItem();
        $invoiceItemModel->loadBean(new \DummyBean());
        $invoiceItemModel->quantity = 1;

        $dbMock = $this->getMockBuilder('\Box_Database')->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('find')
            ->willReturn(array($invoiceItemModel));

        $invoiceItemService = $this->getMockBuilder('\\' . \Box\Mod\Invoice\ServiceInvoiceItem::class)->getMock();
        $invoiceItemService->expects($this->atLeastOnce())
            ->method('getTax')
            ->willReturn(21);

        $di = new \Pimple\Container();
        $di['mod_service'] = $di->protect(fn() => $invoiceItemService);
        $di['db'] = $dbMock;

        $this->service->setDi($di);
        $result = $this->service->getTax($invoiceModel);
        $this->assertIsInt($result);
    }

    public function testdelete()
    {
        $taxModel = new \Model_Tax();
        $taxModel->loadBean(new \DummyBean());

        $dbMock = $this->getMockBuilder('\Box_Database')
            ->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('trash');

        $di           = new \Pimple\Container();
        $di['db']     = $dbMock;
        $di['logger'] = new \Box_Log();
        $this->service->setDi($di);


        $result = $this->service->delete($taxModel);
        $this->assertTrue($result);
    }

    public function testcreate()
    {
        $systemService = $this->getMockBuilder('\\' . \Box\Mod\System\Service::class)->getMock();
        $systemService->expects($this->atLeastOnce())
            ->method('checkLimits');

        $taxModel = new \Model_Tax();
        $taxModel->loadBean(new \DummyBean());
        $dbMock = $this->getMockBuilder('\Box_Database')
            ->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('dispense')
            ->will($this->returnValue($taxModel));
        $newId = 2;
        $dbMock->expects($this->atLeastOnce())
            ->method('store')
            ->will($this->returnValue($newId));

        $di                = new \Pimple\Container();
        $di['mod_service'] = $di->protect(fn() => $systemService);
        $di['db']          = $dbMock;
        $di['logger']      = new \Box_Log();
        $this->service->setDi($di);

        $data   = array(
            'name'    => 'tax',
            'taxrate' => '0.18',
        );
        $result = $this->service->create($data);
        $this->assertIsInt($result);
        $this->assertEquals($newId, $result);
    }

    public function testUpdate()
    {
        $taxModel = new \Model_Tax();
        $taxModel->loadBean(new \DummyBean());
        $dbMock = $this->getMockBuilder('\Box_Database')
            ->getMock();

        $dbMock->expects($this->atLeastOnce())
            ->method('store')
            ->will($this->returnValue(2));

        $di                = new \Pimple\Container();
        $di['db']          = $dbMock;
        $di['logger']      = new \Box_Log();
        $this->service->setDi($di);

        $data   = array(
            'name'    => 'tax',
            'taxrate' => '0.18',
        );
        $result = $this->service->update($taxModel, $data);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }


    public function testgetSearchQuery()
    {
        $result = $this->service->getSearchQuery(array());
        $this->assertIsString($result[0]);
        $this->assertIsArray($result[1]);
        $this->assertEquals(array(), $result[1]);
    }

    public function testtoApiArray()
    {
        $taxModel = new \Model_Tax();
        $taxModel->loadBean(new \DummyBean());

        $dbMock = $this->getMockBuilder('\Box_Database')
            ->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('toArray')
            ->with($taxModel)
            ->willReturn(array());

        $di = new \Pimple\Container();
        $di['db'] = $dbMock;
        $this->service->setDi($di);

        $result = $this->service->toApiArray($taxModel);
        $this->assertIsArray($result);
    }

}
