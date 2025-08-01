<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid\Extension;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Datagrid\Extension\GridViewsExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Entity\Manager\GridViewManager;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\DependencyInjection\ServiceLink;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GridViewsExtensionTest extends TestCase
{
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;
    private TokenAccessorInterface&MockObject $tokenAccessor;
    private TranslatorInterface&MockObject $translator;
    private ManagerRegistry&MockObject $registry;
    private AclHelper&MockObject $aclHelper;
    private GridViewManager&MockObject $gridViewManager;
    private GridViewsExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->gridViewManager = $this->createMock(GridViewManager::class);

        $gridViewManagerLink = $this->createMock(ServiceLink::class);
        $gridViewManagerLink->expects($this->any())
            ->method('getService')
            ->willReturn($this->gridViewManager);

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

    public function testVisitMetadataPermissions(): void
    {
        $data = MetadataObject::create([]);
        $config = DatagridConfiguration::create([DatagridConfiguration::NAME_KEY => 'grid']);

        $this->authorizationChecker->expects($this->exactly(6))
            ->method('isGranted')
            ->willReturnCallback(function ($attribute) {
                return in_array(
                    $attribute,
                    [
                        'oro_customer_frontend_gridview_create',
                        'oro_customer_frontend_gridview_delete',
                        'oro_customer_frontend_gridview_update_public'
                    ],
                    true
                );
            });

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
