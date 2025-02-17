<?php


namespace Box\Mod\Hook;


class ServiceTest extends \BBTestCase {

    /**
     * @var \Box\Mod\Hook\Service
     */
    protected $service = null;

    public function setup(): void
    {
        $this->service= new \Box\Mod\Hook\Service();
    }


    public function testgetDi()
    {
        $di = new \Pimple\Container();
        $this->service->setDi($di);
        $getDi = $this->service->getDi();
        $this->assertEquals($di, $getDi);
    }

    public function testgetSearchQuery()
    {
        [$sql, $params] = $this->service->getSearchQuery(array());

        $this->assertIsString($sql);
        $this->assertIsArray($params);

        $this->assertTrue(str_contains($sql, 'SELECT id, rel_type, rel_id, meta_value as event, created_at, updated_at'));
        $this->assertEquals($params, array());

    }

    public function testtoApiArray()
    {
        $arrMock = array('testing' => 'okey');
        $result = $this->service->toApiArray($arrMock);
        $this->assertEquals($arrMock, $result);
    }

    public function testonAfterAdminActivateExtension()
    {
        $eventParams = array(
            'id' => 1,
        );

        $eventMock = $this->getMockBuilder('\Box_Event')
            ->onlyMethods(array('getParameters', 'getDi'))
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->atLeastOnce())
            ->method('getParameters')
            ->will($this->returnValue($eventParams));

        $model = new \Model_Extension();
        $model->loadBean(new \DummyBean());
        $model->id = 1;
        $model->type = 'mod';


        $dbMock =$this->getMockBuilder('\Box_Database')->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('load')
            ->will($this->returnValue($model));

        $hookService = $this->getMockBuilder('\\' . \Box\Mod\Hook\Service::class)->getMock();
        $hookService->expects($this->atLeastOnce())
            ->method('batchConnect');

        $di = new \Pimple\Container();
        $di['db'] = $dbMock;
        $di['mod_service'] = $di->protect(fn($name) => $hookService);

        $eventMock->expects($this->atLeastOnce())
            ->method('getDi')
            ->will($this->returnValue($di));

        $this->service->setDi($di);
        $this->service->onAfterAdminActivateExtension($eventMock);
        $result = $eventMock->getReturnValue();
        $this->assertTrue($result);
    }

    public function testonAfterAdminActivateExtensionMissingId()
    {
        $eventParams = array();

        $eventMock = $this->getMockBuilder('\Box_Event')
            ->onlyMethods(array('getParameters'))
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->atLeastOnce())
            ->method('getParameters')
            ->will($this->returnValue($eventParams));

        $this->service->onAfterAdminActivateExtension($eventMock);
        $result = $eventMock->getReturnValue();
        $this->assertFalse($result);
    }

    public function testonAfterAdminDeactivateExtension()
    {
        $eventParams = array(
            'type' => 'mod',
            'id' => 1,
        );

        $eventMock = $this->getMockBuilder('\Box_Event')
            ->onlyMethods(array('getParameters', 'getDi'))
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->atLeastOnce())
            ->method('getParameters')
            ->will($this->returnValue($eventParams));

        $dbMock =$this->getMockBuilder('\Box_Database')->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('exec');

        $di = new \Pimple\Container();
        $di['db'] = $dbMock;

        $eventMock->expects($this->atLeastOnce())
            ->method('getDi')
            ->will($this->returnValue($di));

        $this->service->setDi($di);
        $this->service->onAfterAdminDeactivateExtension($eventMock);
        $result = $eventMock->getReturnValue();
        $this->assertTrue($result);
    }

    public function testbatchConnect()
    {
        $mod = 'activity';

        $data['mods'] = array($mod);

        $dbMock =$this->getMockBuilder('\Box_Database')->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('getCell')
            ->will($this->returnValue(false));

        $extensionModel = new \Model_ExtensionMeta();
        $extensionModel->loadBean(new \DummyBean());

        $dbMock->expects($this->atLeastOnce())
            ->method('dispense')
            ->will($this->returnValue($extensionModel));

        $dbMock->expects($this->atLeastOnce())
            ->method('store');

        $dbMock->expects($this->atLeastOnce())
            ->method('findOne');

        $returnArr = array(
            array(
                'id' => 2,
                'rel_id' => 1,
                'meta_value' => 'testValue',
            ),
        );
        $dbMock->expects($this->atLeastOnce())
            ->method('getAll')
            ->will($this->returnValue($returnArr));


        $activityServiceMock = $this->getMockBuilder('\\' . \Box\Mod\Activity\Service::class)->getMock();

        $boxModMock = $this->getMockBuilder('\Box_Mod')->disableOriginalConstructor()->getMock();
        $boxModMock->expects($this->atLeastOnce())
            ->method('hasService')
            ->will($this->returnValue(true));
        $boxModMock->expects($this->any())
            ->method('getService')
            ->will($this->returnValue($activityServiceMock));
        $boxModMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('activity'));

        $extensionServiceMock = $this->getMockBuilder('\\' . \Box\Mod\Extension\Service::class)->getMock();
        $extensionServiceMock->expects(($this->atLeastOnce()))
            ->method('isCoreModule')
            ->will($this->returnValue(false));

        $di = new \Pimple\Container();
        $di['db'] = $dbMock;
        $di['mod'] = $di->protect(fn() => $boxModMock);
        $di['mod_service'] = $di->protect(function ($name) use($extensionServiceMock){
            if ($name == 'extension'){
                return $extensionServiceMock;
            }
        });
        $validatorMock = $this->getMockBuilder('\\' . \FOSSBilling\Validate::class)->disableOriginalConstructor()->getMock();
        $validatorMock->expects($this->atLeastOnce())
            ->method('checkRequiredParamsForArray')
            ->will($this->returnValue(null));
        $di['validator'] = $validatorMock;
        $this->service->setDi($di);
        $result = $this->service->batchConnect($mod);
        $this->assertTrue($result);
    }
}
