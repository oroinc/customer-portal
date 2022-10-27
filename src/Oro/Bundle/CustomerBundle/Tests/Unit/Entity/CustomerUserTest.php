<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserApi;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserSettings;
use Oro\Bundle\CustomerBundle\Tests\Unit\Traits\AddressEntityTestTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Tests\Unit\Entity\AbstractUserTest;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\ReflectionUtil;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserTest extends AbstractUserTest
{
    use AddressEntityTestTrait;

    public function getUser(): CustomerUser
    {
        return new CustomerUser();
    }

    public function createAddressEntity(): CustomerUserAddress
    {
        return new CustomerUserAddress();
    }

    protected function createTestedEntity(): CustomerUser
    {
        return $this->getUser();
    }

    public function testCollections(): void
    {
        self::assertPropertyCollections(new CustomerUser(), [
            ['addresses', $this->createAddressEntity()],
            ['salesRepresentatives', new User()],
        ]);
    }

    public function getWebsite(int $id): Website
    {
        $website = new Website();
        ReflectionUtil::setId($website, $id);

        return $website;
    }

    public function testCreateCustomer(): void
    {
        $organization = new Organization();
        $organization->setName('test');

        $user = $this->getUser();
        $user->setOrganization($organization)
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setOwner(new User());
        self::assertEmpty($user->getCustomer());
        $address = new CustomerAddress();
        $user->addAddress($address);
        self::assertContains($address, $user->getAddresses());
        $backendUser = new User();
        $user->setOwner($backendUser);
        self::assertEquals($user->getOwner(), $backendUser);

        // createCustomer is triggered on prePersist event
        $user->createCustomer();
        $customer = $user->getCustomer();
        self::assertInstanceOf(Customer::class, $customer);
        self::assertEquals($organization, $customer->getOrganization());
        self::assertEquals('John Doe', $customer->getName());

        // new customer created only if it not defined
        $user->setFirstName('Jane');
        $user->createCustomer();
        self::assertEquals('John Doe', $user->getCustomer()->getName());

        //Creating an customer with company name parameter instead of use first and last name
        $user->setCustomer(null);
        $user->createCustomer('test company');
        self::assertEquals('test company', $user->getCustomer()->getName());
    }

    public function testSerializing(): void
    {
        $user = $this->getUser();
        $data = $user->__serialize();

        self::assertNotEmpty($data);

        $user
            ->setPassword('new-pass')
            ->setConfirmationToken('token')
            ->setUsername('new-name');

        $user->__unserialize($data);

        self::assertEmpty($user->getPassword());
        self::assertEmpty($user->getConfirmationToken());
        self::assertEmpty($user->getUsername());
        self::assertEquals('new-name', $user->getEmail());
    }

    public function provider(): array
    {
        return [
            ['customer', new Customer()],
            ['username', 'test'],
            ['email', 'test'],
            ['nameprefix', 'test'],
            ['firstName', 'test'],
            ['middleName', 'test'],
            ['lastName', 'test'],
            ['nameSuffix', 'test'],
            ['birthday', new \DateTime()],
            ['password', 'test'],
            ['plainPassword', 'test'],
            ['confirmationToken', 'test'],
            ['passwordRequestedAt', new \DateTime()],
            ['passwordChangedAt', new \DateTime()],
            ['lastLogin', new \DateTime()],
            ['loginCount', 11],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['website', new Website()],
            ['salt', md5('user')],
            ['isGuest', true],
        ];
    }

    public function testPrePersist(): void
    {
        $user = $this->getUser();
        $user->prePersist();
        self::assertInstanceOf(\DateTime::class, $user->getCreatedAt());
        self::assertInstanceOf(\DateTime::class, $user->getUpdatedAt());
        self::assertEquals(0, $user->getLoginCount());
        self::assertNotEmpty($user->getCustomer());
    }

    public function testPreUpdateUnChanged(): void
    {
        $changeSet = [
            'lastLogin' => null,
            'loginCount' => null
        ];

        $user = $this->getUser();
        $updatedAt = new \DateTime('2015-01-01');
        $user->setUpdatedAt($updatedAt)
            ->setConfirmationToken('test_token')
            ->setPasswordRequestedAt(new \DateTime());

        $event = $this->createMock(PreUpdateEventArgs::class);
        $event->expects(self::any())
            ->method('getEntityChangeSet')
            ->willReturn($changeSet);

        self::assertEquals($updatedAt, $user->getUpdatedAt());
        self::assertNotNull($user->getConfirmationToken());
        self::assertNotNull($user->getPasswordRequestedAt());
        $user->preUpdate($event);
        self::assertEquals($updatedAt, $user->getUpdatedAt());
        self::assertNotNull($user->getConfirmationToken());
        self::assertNotNull($user->getPasswordRequestedAt());
    }

    /**
     * @dataProvider preUpdateDataProvider
     */
    public function testPreUpdateChanged(array $changeSet): void
    {
        $user = $this->getUser();
        $updatedAt = new \DateTime('2015-01-01');
        $user->setUpdatedAt($updatedAt)
            ->setConfirmationToken('test_token')
            ->setPasswordRequestedAt(new \DateTime());

        $event = $this->createMock(PreUpdateEventArgs::class);
        $event->expects(self::any())
            ->method('getEntityChangeSet')
            ->willReturn($changeSet);

        self::assertEquals($updatedAt, $user->getUpdatedAt());
        self::assertNotNull($user->getConfirmationToken());
        self::assertNotNull($user->getPasswordRequestedAt());
        $user->preUpdate($event);
        self::assertNotEquals($updatedAt, $user->getUpdatedAt());
        self::assertNull($user->getConfirmationToken());
        self::assertNull($user->getPasswordRequestedAt());
    }

    public function preUpdateDataProvider(): array
    {
        return [
            [
                'changeSet' => array_flip(['username'])
            ],
            [
                'changeSet' => array_flip(['email'])
            ],
            [
                'changeSet' => array_flip(['password'])
            ],
            [
                'changeSet' => array_flip(['username', 'email'])
            ],
            [
                'changeSet' => array_flip(['email', 'password'])
            ],
            [
                'changeSet' => array_flip(['username', 'password'])
            ],
            [
                'changeSet' => array_flip(['username', 'email', 'password'])
            ],
        ];
    }

    public function testUnserialize(): void
    {
        $user = $this->getUser();
        $serialized = [
            'password',
            'salt',
            'username',
            true,
            false,
            'confirmation_token',
            10
        ];
        $user->__unserialize($serialized);

        self::assertEquals($serialized[0], $user->getPassword());
        self::assertEquals($serialized[1], $user->getSalt());
        self::assertEquals($serialized[2], $user->getUsername());
        self::assertEquals($serialized[3], $user->isEnabled());
        self::assertEquals($serialized[4], $user->isConfirmed());
        self::assertEquals($serialized[5], $user->getConfirmationToken());
        self::assertEquals($serialized[6], $user->getId());
    }

    public function testIsEnabled(): void
    {
        $user = $this->getUser();

        self::assertTrue($user->isEnabled());

        $user->setEnabled(false);

        self::assertFalse($user->isEnabled());
    }

    public function testIsConfirmed(): void
    {
        $user = $this->getUser();

        self::assertTrue($user->isConfirmed());

        $user->setConfirmed(false);

        self::assertFalse($user->isConfirmed());
    }

    public function testGetFullName(): void
    {
        $user = $this->getUser();
        $user
            ->setFirstName('FirstName')
            ->setLastName('LastName');

        self::assertSame('FirstName LastName', $user->getFullName());
    }

    public function testSettingsAccessors(): void
    {
        $user = $this->getUser();
        $website = $this->getWebsite(1);

        $user->setWebsiteSettings(new CustomerUserSettings($this->getWebsite(2)))
            ->setWebsiteSettings(new CustomerUserSettings($website))
            ->setWebsiteSettings((new CustomerUserSettings($website))->setCurrency('USD'))
            ->setWebsiteSettings(new CustomerUserSettings(new Website()));

        self::assertSame('USD', $user->getWebsiteSettings($website)->getCurrency());
    }

    public function testSettingGetter(): void
    {
        $user = $this->getUser();
        $website1 = $this->getWebsite(1);
        $website2 = $this->getWebsite(2);
        $website3 = $this->getWebsite(3);

        self::assertEquals(0, $user->getSettings()->count());

        $firstSetting = new CustomerUserSettings($website2);
        $user->setWebsiteSettings($firstSetting);
        self::assertEquals(1, $user->getSettings()->count());
        self::assertTrue($user->getSettings()->contains($firstSetting));

        $secondSetting = new CustomerUserSettings($website1);
        $user->setWebsiteSettings($secondSetting);
        self::assertEquals(2, $user->getSettings()->count());
        self::assertTrue($user->getSettings()->contains($secondSetting));

        $thirdSetting = new CustomerUserSettings($website1);
        $user->setWebsiteSettings($thirdSetting);
        self::assertEquals(2, $user->getSettings()->count());
        self::assertFalse($user->getSettings()->contains($secondSetting));
        self::assertTrue($user->getSettings()->contains($thirdSetting));

        $fourthSetting = new CustomerUserSettings($website3);
        $user->setWebsiteSettings($fourthSetting);
        self::assertEquals(3, $user->getSettings()->count());
        self::assertTrue($user->getSettings()->contains($fourthSetting));
    }

    public function testApiKeys(): void
    {
        $user = $this->getUser();

        self::assertInstanceOf(ArrayCollection::class, $user->getApiKeys());
        self::assertCount(0, $user->getApiKeys());

        $apiKey1 = new CustomerUserApi();
        $apiKey1->setApiKey('key1');
        $apiKey2 = new CustomerUserApi();
        $apiKey2->setApiKey('key1');

        $user->addApiKey($apiKey1);
        $user->addApiKey($apiKey2);
        self::assertCount(2, $user->getApiKeys());
        self::assertSame($apiKey1, $user->getApiKeys()->first());
        self::assertSame($apiKey2, $user->getApiKeys()->last());
        self::assertSame($user, $apiKey1->getUser());
        self::assertSame($user, $apiKey2->getUser());

        $user->removeApiKey($apiKey1);
        self::assertCount(1, $user->getApiKeys());
        self::assertSame($apiKey2, $user->getApiKeys()->first());
    }

    public function testSetEmailGetEmailLowercase(): void
    {
        $user = $this->getUser();
        $user->setEmail('John.Doe@example.org');

        self::assertEquals('john.doe@example.org', $user->getEmailLowercase());
    }

    public function testSetUsernameGetEmailLowercase(): void
    {
        $user = $this->getUser();
        $user->setUsername('John.Doe@example.org');

        self::assertEquals('john.doe@example.org', $user->getEmailLowercase());
    }
}
