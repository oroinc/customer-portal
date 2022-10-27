<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\EventListener\EmailBodyAddListener;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailRecipient;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailActivityManager;
use Oro\Bundle\EmailBundle\Event\EmailBodyAdded;
use Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures\EmailAddress;

class EmailBodyAddListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerUserRepository */
    private $repository;

    /** @var ObjectManager */
    private $manager;

    /** @var EmailActivityManager */
    private $activityManager;

    /** @var EmailBodyAddListener */
    private $listener;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CustomerUserRepository::class);

        $this->manager = $this->createMock(ObjectManager::class);
        $this->manager->expects($this->any())
            ->method('getRepository')
            ->with(CustomerUser::class)
            ->willReturn($this->repository);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($this->manager);

        $this->activityManager = $this->createMock(EmailActivityManager::class);

        $this->listener = new EmailBodyAddListener($registry, $this->activityManager);
    }

    public function testLinkToCustomerUser(): void
    {
        $email1 = 'test1@example.com';
        $email2 = 'test2@example.com';
        $email3 = 'test3@example.com';

        $entity = new Email();
        $entity->addRecipient($this->getRecipient(EmailRecipient::TO, $email1));
        $entity->addRecipient($this->getRecipient(EmailRecipient::CC, $email2));
        $entity->addRecipient($this->getRecipient(EmailRecipient::BCC, $email3));

        $user = new CustomerUser();
        $user->setFirstName('Amanda');
        $user->setLastName('Cole');

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['email' => [$email1, $email2, $email3]])
            ->willReturn([$user]);

        $this->activityManager->expects($this->once())
            ->method('addAssociation')
            ->with($entity, $user)
            ->willReturn([$user]);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->listener->linkToCustomerUser(new EmailBodyAdded($entity));
    }

    public function testLinkToCustomerUserWhenNoRecipients(): void
    {
        $entity = new Email();

        $user = new CustomerUser();
        $user->setFirstName('Amanda');
        $user->setLastName('Cole');

        $this->repository->expects($this->never())
            ->method('findBy');

        $this->activityManager->expects($this->never())
            ->method('addAssociation');

        $this->manager->expects($this->never())
            ->method('flush');

        $this->listener->linkToCustomerUser(new EmailBodyAdded($entity));
    }

    public function testLinkToCustomerUserWhenNoValidUsers(): void
    {
        $email = 'test1@example.com';

        $entity = new Email();
        $entity->addRecipient($this->getRecipient(EmailRecipient::TO, $email));

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['email' => [$email]])
            ->willReturn([]);

        $this->activityManager->expects($this->never())
            ->method('addAssociation');

        $this->manager->expects($this->never())
            ->method('flush');

        $this->listener->linkToCustomerUser(new EmailBodyAdded($entity));
    }

    private function getRecipient(string $type, string $email = null): EmailRecipient
    {
        $rcpt = new EmailRecipient();
        $rcpt->setType($type);

        if ($email) {
            $address = new EmailAddress();
            $address->setEmail($email);

            $rcpt->setEmailAddress($address);
        }

        return $rcpt;
    }
}
