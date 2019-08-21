<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Async\Topics;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Model\OwnerTreeMessageFactory;
use Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider;
use Oro\Bundle\EntityBundle\Tools\DatabaseChecker;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessageProducer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FrontendOwnerTreeProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|MessageProducer
     */
    protected $messageProducer;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OwnerTreeMessageFactory
     */
    protected $ownerTreeMessageFactory;

    /**
     * @var FrontendOwnerTreeProvider
     */
    protected $treeProvider;

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->messageProducer = $this->createMock(MessageProducer::class);
        $this->ownerTreeMessageFactory = $this->createMock(OwnerTreeMessageFactory::class);

        $this->treeProvider = new FrontendOwnerTreeProvider(
            $this->createMock(ManagerRegistry::class),
            $this->createMock(DatabaseChecker::class),
            $this->createMock(CacheProvider::class),
            $this->createMock(OwnershipMetadataProviderInterface::class),
            $this->tokenStorage
        );
    }

    public function testSupportsForSupportedUser(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::atLeastOnce())
            ->method('getUser')
            ->willReturn(new CustomerUser());

        $this->assertTrue($this->treeProvider->supports());
    }

    public function testSupportsForNotSupportedUser(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn(new User());

        $this->assertFalse($this->treeProvider->supports());
    }

    public function testSupportsWhenNoSecurityToken(): void
    {
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertFalse($this->treeProvider->supports());
    }

    public function testWarmUpCache(): void
    {
        $cacheTtl = 100000;
        $data = ['cache_ttl' => $cacheTtl];

        $this->ownerTreeMessageFactory
            ->expects(self::once())
            ->method('createMessage')
            ->with($cacheTtl)
            ->willReturn($data);

        $this->messageProducer
            ->expects(self::once())
            ->method('send')
            ->with(Topics::CALCULATE_OWNER_TREE_CACHE, new Message($data));

        $this->treeProvider->setMessageProducer($this->messageProducer);
        $this->treeProvider->setOwnerTreeMessageFactory($this->ownerTreeMessageFactory);
        $this->treeProvider->setCacheTtl($cacheTtl);
        $this->treeProvider->warmUpCache();
    }
}
