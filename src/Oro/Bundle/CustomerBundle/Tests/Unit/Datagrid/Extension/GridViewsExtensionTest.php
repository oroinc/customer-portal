<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid\Extension;

use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\CustomerBundle\Datagrid\Extension\GridViewsExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Entity\Manager\GridViewManager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Oro\Component\DependencyInjection\ServiceLink;

class GridViewsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventDispatcher;

    /** @var SecurityFacade|\PHPUnit_Framework_MockObject_MockObject */
    protected $securityFacade;

    /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /** @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /** @var AclHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $aclHelper;

    /** @var GridViewManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $gridViewManager;

    /** @var GridViewsExtension */
    protected $extension;

    protected function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->securityFacade = $this->createMock(SecurityFacade::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->gridViewManager = $this->createMock(GridViewManager::class);

        /** @var ServiceLink|\PHPUnit_Framework_MockObject_MockObject $gridViewManagerLink */
        $gridViewManagerLink = $this->createMock(ServiceLink::class);
        $gridViewManagerLink->expects($this->any())->method('getService')->willReturn($this->gridViewManager);

        $this->extension = new GridViewsExtension(
            $this->eventDispatcher,
            $this->securityFacade,
            $this->translator,
            $this->registry,
            $this->aclHelper,
            $gridViewManagerLink
        );
        $this->extension->setParameters(new ParameterBag());
    }

    public function testVisitMetadataPermissions()
    {
        $data = MetadataObject::create([]);
        $config = DatagridConfiguration::create([DatagridConfiguration::NAME_KEY => 'grid']);

        $this->securityFacade->expects($this->exactly(6))
            ->method('isGranted')
            ->willReturnCallback(
                function ($attribute) {
                    return in_array(
                        $attribute,
                        [
                            'oro_customer_frontend_gridview_create',
                            'oro_customer_frontend_gridview_delete',
                            'oro_customer_frontend_gridview_update_public'
                        ],
                        true
                    );
                }
            );

        $this->assertFalse($data->offsetExists('gridViews'));
        $this->extension->visitMetadata($config, $data);
        $this->assertTrue($data->offsetExists('gridViews'));
        $this->assertEquals(
            [
                'views' => null,
                'permissions' => [
                    'VIEW' => false,
                    'CREATE' => true,
                    'EDIT' => false,
                    'DELETE' => true,
                    'SHARE' => false,
                    'EDIT_SHARED' => true
                ],
                'gridName' => 'grid',
            ],
            $data->offsetGet('gridViews')
        );
    }
}
