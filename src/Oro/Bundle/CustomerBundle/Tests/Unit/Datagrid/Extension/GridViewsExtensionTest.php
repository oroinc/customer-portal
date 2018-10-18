<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid\Extension;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Datagrid\Extension\GridViewsExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Entity\Manager\GridViewManager;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\DependencyInjection\ServiceLink;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class GridViewsExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $eventDispatcher;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $authorizationChecker;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $tokenAccessor;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $translator;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $registry;

    /** @var AclHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $aclHelper;

    /** @var GridViewManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $gridViewManager;

    /** @var GridViewsExtension */
    protected $extension;

    protected function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->gridViewManager = $this->createMock(GridViewManager::class);

        /** @var ServiceLink|\PHPUnit\Framework\MockObject\MockObject $gridViewManagerLink */
        $gridViewManagerLink = $this->createMock(ServiceLink::class);
        $gridViewManagerLink->expects($this->any())->method('getService')->willReturn($this->gridViewManager);

        $this->extension = new GridViewsExtension(
            $this->eventDispatcher,
            $this->authorizationChecker,
            $this->tokenAccessor,
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

        $this->authorizationChecker->expects($this->exactly(6))
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
