<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Provider\EmailOwnerProvider;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;

class EmailOwnerProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetEmailOwnerClass()
    {
        $provider = new EmailOwnerProvider();

        $this->assertEquals(CustomerUser::class, $provider->getEmailOwnerClass());
    }

    public function testFindEmailOwner()
    {
        $repository = $this->createMock(CustomerUserRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'customer@example.com']);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(CustomerUser::class)
            ->willReturn($repository);

        $provider = new EmailOwnerProvider();
        $provider->findEmailOwner($entityManager, 'customer@example.com');
    }
}
