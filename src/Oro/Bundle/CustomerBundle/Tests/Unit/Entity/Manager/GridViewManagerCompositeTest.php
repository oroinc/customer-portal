<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Manager;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Manager\GridViewManagerComposite;
use Oro\Bundle\DataGridBundle\Entity\Manager\GridViewManager;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\DataGridBundle\Extension\GridViews\ViewInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserBundle\Entity\User;

class GridViewManagerCompositeTest extends \PHPUnit\Framework\TestCase
{
    /** @var GridViewManager|\PHPUnit\Framework\MockObject\MockObject */
    private $defaultGridViewManager;

    /** @var GridViewManager|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendGridViewManager;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var GridViewManagerComposite */
    private $manager;

    protected function setUp(): void
    {
        $this->defaultGridViewManager = $this->createMock(GridViewManager::class);
        $this->frontendGridViewManager = $this->createMock(GridViewManager::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->manager = new GridViewManagerComposite(
            $this->defaultGridViewManager,
            $this->frontendGridViewManager,
            $this->tokenAccessor
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetDefaultGridView(AbstractUser|string $user, bool $isFrontend)
    {
        $view = new View('test');
        $customUser = new User();

        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->defaultGridViewManager->expects($this->exactly((int) !$isFrontend))
            ->method('setDefaultGridView')
            ->willReturnCallback(function (AbstractUser $user, ViewInterface $gridView, $default) use ($customUser) {
                $gridView->setGridName('default');
                $this->assertSame($customUser, $user);
                $this->assertFalse($default);
            });
        $this->frontendGridViewManager->expects($this->exactly((int) $isFrontend))
            ->method('setDefaultGridView')
            ->willReturnCallback(function (AbstractUser $user, ViewInterface $gridView, $default) use ($customUser) {
                $gridView->setGridName('frontend');
                $this->assertSame($customUser, $user);
                $this->assertFalse($default);
            });

        $this->manager->setDefaultGridView($customUser, $view, false);

        if ($isFrontend) {
            $this->assertEquals('frontend', $view->getGridName());
        } else {
            $this->assertEquals('default', $view->getGridName());
        }
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetSystemViews(AbstractUser|string $user, bool $isFrontend)
    {
        $gridName = 'test';

        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->defaultGridViewManager->expects($this->exactly((int) !$isFrontend))
            ->method('getSystemViews')
            ->with($gridName)
            ->willReturn('default');
        $this->frontendGridViewManager->expects($this->exactly((int) $isFrontend))
            ->method('getSystemViews')
            ->with($gridName)
            ->willReturn('frontend');

        $this->assertEquals($isFrontend ? 'frontend' : 'default', $this->manager->getSystemViews($gridName));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetAllGridViews(AbstractUser|string $user, bool $isFrontend)
    {
        $customUser = new User();
        $gridName = 'test';

        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->defaultGridViewManager->expects($this->exactly((int) !$isFrontend))
            ->method('getAllGridViews')
            ->with($customUser, $gridName)
            ->willReturn('default');
        $this->frontendGridViewManager->expects($this->exactly((int) $isFrontend))
            ->method('getAllGridViews')
            ->with($customUser, $gridName)
            ->willReturn('frontend');

        $this->assertEquals(
            $isFrontend ? 'frontend' : 'default',
            $this->manager->getAllGridViews($customUser, $gridName)
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetDefaultView(AbstractUser|string $user, bool $isFrontend)
    {
        $customUser = new User();
        $gridName = 'test';

        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->defaultGridViewManager->expects($this->exactly((int) !$isFrontend))
            ->method('getDefaultView')
            ->with($customUser, $gridName)
            ->willReturn('default');
        $this->frontendGridViewManager->expects($this->exactly((int) $isFrontend))
            ->method('getDefaultView')
            ->with($customUser, $gridName)
            ->willReturn('frontend');

        $this->assertEquals(
            $isFrontend ? 'frontend' : 'default',
            $this->manager->getDefaultView($customUser, $gridName)
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetView(AbstractUser|string $user, bool $isFrontend)
    {
        $id = 'id';
        $default = true;
        $gridName = 'test';

        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->defaultGridViewManager->expects($this->exactly((int) !$isFrontend))
            ->method('getView')
            ->with($id, $default, $gridName)
            ->willReturn('default');
        $this->frontendGridViewManager->expects($this->exactly((int) $isFrontend))
            ->method('getView')
            ->with($id, $default, $gridName)
            ->willReturn('frontend');

        $this->assertEquals($isFrontend ? 'frontend' : 'default', $this->manager->getView($id, $default, $gridName));
    }

    public function dataProvider(): array
    {
        return [
            'anonymous' => [
                'user' => 'anonymous',
                'isFrontend' => false
            ],
            'instance of User' => [
                'user' => new User(),
                'isFrontend' => false
            ],
            'instance of CustomerUser' => [
                'user' => new CustomerUser(),
                'isFrontend' => true
            ]
        ];
    }
}
