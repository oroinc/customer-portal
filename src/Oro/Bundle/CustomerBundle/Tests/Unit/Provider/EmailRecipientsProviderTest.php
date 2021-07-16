<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Provider\EmailRecipientsProvider;
use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EmailRecipientsProviderTest extends TestCase
{
    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var EmailRecipientsHelper|MockObject
     */
    protected $emailRecipientsHelper;

    /**
     * @var EmailRecipientsProvider|MockObject
     */
    protected $emailRecipientsProvider;

    protected function setUp(): void
    {
        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailRecipientsHelper = $this->getMockBuilder(EmailRecipientsHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailRecipientsProvider = new EmailRecipientsProvider(
            $this->registry,
            $this->emailRecipientsHelper
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetRecipients(EmailRecipientsProviderArgs $args, array $recipients)
    {
        $customerUserRepository = $this->getMockBuilder(CustomerUserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with(CustomerUser::class)
            ->willReturn($customerUserRepository);

        $this->emailRecipientsHelper->expects($this->once())
            ->method('getRecipients')
            ->with($args, $customerUserRepository, 'cu', CustomerUser::class)
            ->willReturn($recipients);

        $this->assertEquals($recipients, $this->emailRecipientsProvider->getRecipients($args));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [
                new EmailRecipientsProviderArgs(null, null, 1),
                [
                    'recipient@example.com'  => 'Recipient <recipient@example.com>',
                ],
            ],
        ];
    }
}
