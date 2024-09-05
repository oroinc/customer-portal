<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Tests\Unit\Stub\FileProxyStub;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\EventListener\LoadMenuImagesListener;
use Oro\Bundle\NavigationBundle\Event\MenuUpdatesApplyAfterEvent;
use Oro\Bundle\NavigationBundle\MenuUpdate\Applier\Model\MenuUpdateApplierContext;
use Oro\Component\Testing\ReflectionUtil;

class LoadMenuImagesListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var LoadMenuImagesListener */
    private $listener;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);

        $this->listener = new LoadMenuImagesListener($this->doctrine);
    }

    private function getMenuItem(?File $image): ItemInterface
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem->expects(self::once())
            ->method('getExtra')
            ->with(MenuUpdate::IMAGE)
            ->willReturn($image);

        return $menuItem;
    }

    private function getImageFile(int $id): File
    {
        $image = new File();
        ReflectionUtil::setId($image, $id);

        return $image;
    }

    private function getImageFileProxy(int $id, bool $initialized): File
    {
        $image = new FileProxyStub();
        ReflectionUtil::setId($image, $id);
        if ($initialized) {
            $image->setInitialized(true);
        }

        return $image;
    }

    public function testOnMenuUpdatesApplyAfterWhenNoMenuItems(): void
    {
        $context = $this->createMock(MenuUpdateApplierContext::class);
        $context->expects(self::once())
            ->method('getMenuItemsByName')
            ->willReturn([]);

        $this->listener->onMenuUpdatesApplyAfter(new MenuUpdatesApplyAfterEvent($context));
    }

    public function testOnMenuUpdatesApplyAfterWhenAllImagesAreAlreadyLoaded(): void
    {
        $context = $this->createMock(MenuUpdateApplierContext::class);
        $context->expects(self::once())
            ->method('getMenuItemsByName')
            ->willReturn([
                $this->getMenuItem(null),
                $this->getMenuItem($this->getImageFile(1)),
                $this->getMenuItem($this->getImageFileProxy(2, true))
            ]);

        $this->doctrine->expects(self::never())
            ->method('getRepository');

        $this->listener->onMenuUpdatesApplyAfter(new MenuUpdatesApplyAfterEvent($context));
    }

    public function testOnMenuUpdatesApplyAfterWhenSomeImagesAreNotLoadedYet(): void
    {
        $notLoadedImage1 = $this->getImageFileProxy(2, false);
        $notLoadedImage2 = $this->getImageFileProxy(4, false);

        $context = $this->createMock(MenuUpdateApplierContext::class);
        $context->expects(self::once())
            ->method('getMenuItemsByName')
            ->willReturn([
                $this->getMenuItem(null),
                $this->getMenuItem($this->getImageFile(1)),
                $this->getMenuItem($notLoadedImage1),
                $this->getMenuItem($this->getImageFileProxy(3, true)),
                $this->getMenuItem($notLoadedImage2)
            ]);

        $repository = $this->createMock(EntityRepository::class);
        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(File::class)
            ->willReturn($repository);
        $repository->expects(self::once())
            ->method('findBy')
            ->with(['id' => [2, 4]])
            ->willReturn([$notLoadedImage1, $notLoadedImage2]);

        $this->listener->onMenuUpdatesApplyAfter(new MenuUpdatesApplyAfterEvent($context));
    }
}
