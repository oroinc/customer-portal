<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\EventListener\EmailBodyAddListener;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailRecipient;
use Oro\Bundle\EmailBundle\Event\EmailBodyAdded;
use Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures\EmailAddress;

class EmailBodyAddListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerUserRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    /** @var ActivityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $activityManager;

    /** @var EmailBodyAddListener */
    private $listener;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CustomerUserRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->activityManager = $this->createMock(ActivityManager::class);

        $this->em->expects(self::any())
            ->method('getRepository')
            ->with(CustomerUser::class)
            ->willReturn($this->repository);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::any())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($this->em);

        $this->listener = new EmailBodyAddListener($doctrine, $this->activityManager);
    }

    private function getRecipient(string $type, string $email = null): EmailRecipient
    {
        $recipient = new EmailRecipient();
        $recipient->setType($type);

        if ($email) {
            $address = new EmailAddress();
            $address->setEmail($email);

            $recipient->setEmailAddress($address);
        }

        return $recipient;
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

        $this->repository->expects(self::once())
            ->method('findBy')
            ->with(['email' => [$email1, $email2, $email3]])
            ->willReturn([$user]);

        $this->activityManager->expects(self::once())
            ->method('addActivityTargets')
            ->with($entity, [$user])
            ->willReturn(true);

        $this->em->expects(self::once())
            ->method('flush');

        $this->listener->linkToCustomerUser(new EmailBodyAdded($entity));
    }

    public function testLinkToCustomerUserWhenNoRecipients(): void
    {
        $entity = new Email();

        $user = new CustomerUser();
        $user->setFirstName('Amanda');
        $user->setLastName('Cole');

        $this->repository->expects(self::never())
            ->method('findBy');

        $this->activityManager->expects(self::never())
            ->method('addActivityTargets');

        $this->em->expects(self::never())
            ->method('flush');

        $this->listener->linkToCustomerUser(new EmailBodyAdded($entity));
    }

    public function testLinkToCustomerUserWhenNoValidUsers(): void
    {
        $email = 'test1@example.com';

        $entity = new Email();
        $entity->addRecipient($this->getRecipient(EmailRecipient::TO, $email));

        $this->repository->expects(self::once())
            ->method('findBy')
            ->with(['email' => [$email]])
            ->willReturn([]);

        $this->activityManager->expects(self::never())
            ->method('addActivityTargets');

        $this->em->expects(self::never())
            ->method('flush');

        $this->listener->linkToCustomerUser(new EmailBodyAdded($entity));
    }
}
