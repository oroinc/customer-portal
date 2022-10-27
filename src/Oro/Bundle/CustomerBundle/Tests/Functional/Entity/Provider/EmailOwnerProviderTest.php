<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class EmailOwnerProviderTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([
            '@OroCustomerBundle/Tests/Functional/Entity/Provider/DataFixtures/email_owner_provider.yml'
        ]);
    }

    private function getProvider(): EmailOwnerProviderInterface
    {
        return self::getContainer()->get('oro_customer.email.owner.provider');
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
    }

    public function caseInsensitiveSearchDataProvider(): array
    {
        return [[true], [false]];
    }

    public function testGetEmailOwnerClass(): void
    {
        self::assertEquals(CustomerUser::class, $this->getProvider()->getEmailOwnerClass());
    }

    /**
     * @dataProvider caseInsensitiveSearchDataProvider
     */
    public function testFindEmailOwner(bool $caseInsensitiveSearch): void
    {
        $email = 'jane.smith@example.com';
        if ($caseInsensitiveSearch) {
            $email = strtoupper($email);
        }

        /** @var CustomerUser $owner */
        $owner = $this->getProvider()->findEmailOwner($this->getEntityManager(), $email);
        self::assertInstanceOf(CustomerUser::class, $owner);
        self::assertSame($this->getReference('customerUser4')->getId(), $owner->getId());
    }

    public function testFindEmailOwnerWhenItDoesNotExist(): void
    {
        $owner = $this->getProvider()->findEmailOwner($this->getEntityManager(), 'another@example.com');
        self::assertNull($owner);
    }

    public function testFindEmailOwnerWhenEmailDuplicated(): void
    {
        $owner = $this->getProvider()->findEmailOwner($this->getEntityManager(), 'test@example.com');
        self::assertInstanceOf(CustomerUser::class, $owner);
        self::assertSame($this->getReference('customerUser2')->getId(), $owner->getId());
    }

    /**
     * @dataProvider caseInsensitiveSearchDataProvider
     */
    public function testGetOrganizations(bool $caseInsensitiveSearch): void
    {
        $email = 'jane.smith@example.com';
        if ($caseInsensitiveSearch) {
            $email = strtoupper($email);
        }

        $organizations = $this->getProvider()->getOrganizations($this->getEntityManager(), $email);
        self::assertSame(
            [$this->getReference('organization')->getId()],
            $organizations
        );
    }

    /**
     * @dataProvider caseInsensitiveSearchDataProvider
     */
    public function testGetOrganizationsForSeveralOrganizations(bool $caseInsensitiveSearch): void
    {
        $email = 'john.smith@example.com';
        if ($caseInsensitiveSearch) {
            $email = strtoupper($email);
        }

        $organizations = $this->getProvider()->getOrganizations($this->getEntityManager(), $email);
        sort($organizations);
        self::assertSame(
            [$this->getReference('organization')->getId(), $this->getReference('another_organization')->getId()],
            $organizations
        );
    }

    public function testGetEmails(): void
    {
        $emails = $this->getProvider()->getEmails(
            $this->getEntityManager(),
            $this->getReference('organization')->getId()
        );
        self::assertSame(
            [
                'customer_user@example.com',
                'john.smith@example.com',
                'test@example.com',
                'test@example.com',
                'jane.smith@example.com'
            ],
            iterator_to_array($emails)
        );
    }
}
