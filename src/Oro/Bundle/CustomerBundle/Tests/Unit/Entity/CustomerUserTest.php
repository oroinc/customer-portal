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
use Oro\Component\Testing\Unit\EntityTrait;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserTest extends AbstractUserTest
{
    use AddressEntityTestTrait;
    use EntityTrait;

    /**
     * @return CustomerUser
     */
    public function getUser()
    {
        return new CustomerUser();
    }

    /**
     * @return CustomerUserAddress
     */
    public function createAddressEntity()
    {
        return new CustomerUserAddress();
    }

    /**
     * @return CustomerUser
     */
    protected function createTestedEntity()
    {
        return $this->getUser();
    }

    public function testCollections()
    {
        $this->assertPropertyCollections(new CustomerUser(), [
            ['addresses', $this->createAddressEntity()],
            ['salesRepresentatives', new User()],
        ]);
    }

    public function testCreateCustomer()
    {
        $organization = new Organization();
        $organization->setName('test');

        $user = $this->getUser();
        $user->setOrganization($organization)
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setOwner(new User());
        $this->assertEmpty($user->getCustomer());
        $address = new CustomerAddress();
        $user->addAddress($address);
        $this->assertContains($address, $user->getAddresses());
        $backendUser = new User();
        $user->setOwner($backendUser);
        $this->assertEquals($user->getOwner(), $backendUser);

        // createCustomer is triggered on prePersist event
        $user->createCustomer();
        $customer = $user->getCustomer();
        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals($organization, $customer->getOrganization());
        $this->assertEquals('John Doe', $customer->getName());

        // new customer created only if it not defined
        $user->setFirstName('Jane');
        $user->createCustomer();
        $this->assertEquals('John Doe', $user->getCustomer()->getName());

        //Creating an customer with company name parameter instead of use first and last name
        $user->setCustomer(null);
        $user->createCustomer('test company');
        $this->assertEquals('test company', $user->getCustomer()->getName());
    }

    public function testSerializing()
    {
        $user = $this->getUser();
        $data = $user->serialize();

        $this->assertNotEmpty($data);

        $user
            ->setPassword('new-pass')
            ->setConfirmationToken('token')
            ->setUsername('new-name');

        $user->unserialize($data);

        $this->assertEmpty($user->getPassword());
        $this->assertEmpty($user->getConfirmationToken());
        $this->assertEmpty($user->getUsername());
        $this->assertEquals('new-name', $user->getEmail());
    }

    /**
     * @return array
     */
    public function provider()
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

    public function testPrePersist()
    {
        $user = $this->getUser();
        $user->prePersist();
        $this->assertInstanceOf('\DateTime', $user->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $user->getUpdatedAt());
        $this->assertEquals(0, $user->getLoginCount());
        $this->assertNotEmpty($user->getCustomer());
    }

    public function testPreUpdateUnChanged()
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

        /** @var \PHPUnit\Framework\MockObject\MockObject|PreUpdateEventArgs $event */
        $event = $this->getMockBuilder('Doctrine\ORM\Event\PreUpdateEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getEntityChangeSet')
            ->will($this->returnValue($changeSet));

        $this->assertEquals($updatedAt, $user->getUpdatedAt());
        $this->assertNotNull($user->getConfirmationToken());
        $this->assertNotNull($user->getPasswordRequestedAt());
        $user->preUpdate($event);
        $this->assertEquals($updatedAt, $user->getUpdatedAt());
        $this->assertNotNull($user->getConfirmationToken());
        $this->assertNotNull($user->getPasswordRequestedAt());
    }

    /**
     * @dataProvider preUpdateDataProvider
     */
    public function testPreUpdateChanged(array $changeSet)
    {
        $user = $this->getUser();
        $updatedAt = new \DateTime('2015-01-01');
        $user->setUpdatedAt($updatedAt)
            ->setConfirmationToken('test_token')
            ->setPasswordRequestedAt(new \DateTime());

        /** @var \PHPUnit\Framework\MockObject\MockObject|PreUpdateEventArgs $event */
        $event = $this->getMockBuilder('Doctrine\ORM\Event\PreUpdateEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getEntityChangeSet')
            ->will($this->returnValue($changeSet));

        $this->assertEquals($updatedAt, $user->getUpdatedAt());
        $this->assertNotNull($user->getConfirmationToken());
        $this->assertNotNull($user->getPasswordRequestedAt());
        $user->preUpdate($event);
        $this->assertNotEquals($updatedAt, $user->getUpdatedAt());
        $this->assertNull($user->getConfirmationToken());
        $this->assertNull($user->getPasswordRequestedAt());
    }

    /**
     * @return array
     */
    public function preUpdateDataProvider()
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

    public function testUnserialize()
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
        $user->unserialize(serialize($serialized));

        $this->assertEquals($serialized[0], $user->getPassword());
        $this->assertEquals($serialized[1], $user->getSalt());
        $this->assertEquals($serialized[2], $user->getUsername());
        $this->assertEquals($serialized[3], $user->isEnabled());
        $this->assertEquals($serialized[4], $user->isConfirmed());
        $this->assertEquals($serialized[5], $user->getConfirmationToken());
        $this->assertEquals($serialized[6], $user->getId());
    }

    public function testIsEnabledAndIsConfirmed()
    {
        $user = $this->getUser();

        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isConfirmed());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertTrue($user->isAccountNonLocked());

        $user->setEnabled(false);

        $this->assertFalse($user->isEnabled());
        $this->assertFalse($user->isAccountNonLocked());

        $user->setEnabled(true);
        $user->setConfirmed(false);

        $this->assertFalse($user->isConfirmed());
        $this->assertFalse($user->isAccountNonLocked());
    }

    public function testGetFullName()
    {
        $user = $this->getUser();
        $user
            ->setFirstName('FirstName')
            ->setLastName('LastName');

        $this->assertSame('FirstName LastName', $user->getFullName());
    }

    public function testSettingsAccessors()
    {
        $user = $this->getUser();
        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 1]);

        $user->setWebsiteSettings(new CustomerUserSettings($this->getEntity(Website::class, ['id' => 2])))
            ->setWebsiteSettings(new CustomerUserSettings($website))
            ->setWebsiteSettings((new CustomerUserSettings($website))->setCurrency('USD'))
            ->setWebsiteSettings(new CustomerUserSettings(new Website()));

        $this->assertSame('USD', $user->getWebsiteSettings($website)->getCurrency());
    }

    public function testSettingGetter()
    {
        $user = $this->getUser();
        /** @var Website $website1 */
        $website1 = $this->getEntity(Website::class, ['id' => 1]);
        /** @var Website $website2 */
        $website2 = $this->getEntity(Website::class, ['id' => 2]);
        /** @var Website $website3 */
        $website3 = $this->getEntity(Website::class, ['id' => 3]);

        $this->assertEquals(0, $user->getSettings()->count());

        $firstSetting = new CustomerUserSettings($website2);
        $user->setWebsiteSettings($firstSetting);
        $this->assertEquals(1, $user->getSettings()->count());
        $this->assertTrue($user->getSettings()->contains($firstSetting));

        $secondSetting = new CustomerUserSettings($website1);
        $user->setWebsiteSettings($secondSetting);
        $this->assertEquals(2, $user->getSettings()->count());
        $this->assertTrue($user->getSettings()->contains($secondSetting));

        $thirdSetting = new CustomerUserSettings($website1);
        $user->setWebsiteSettings($thirdSetting);
        $this->assertEquals(2, $user->getSettings()->count());
        $this->assertFalse($user->getSettings()->contains($secondSetting));
        $this->assertTrue($user->getSettings()->contains($thirdSetting));

        $fourthSetting = new CustomerUserSettings($website3);
        $user->setWebsiteSettings($fourthSetting);
        $this->assertEquals(3, $user->getSettings()->count());
        $this->assertTrue($user->getSettings()->contains($fourthSetting));
    }

    public function testApiKeys()
    {
        $user = $this->getUser();

        $this->assertInstanceOf(ArrayCollection::class, $user->getApiKeys());
        $this->assertCount(0, $user->getApiKeys());

        $apiKey1 = new CustomerUserApi();
        $apiKey1->setApiKey('key1');
        $apiKey2 = new CustomerUserApi();
        $apiKey2->setApiKey('key1');

        $user->addApiKey($apiKey1);
        $user->addApiKey($apiKey2);
        $this->assertCount(2, $user->getApiKeys());
        $this->assertSame($apiKey1, $user->getApiKeys()->first());
        $this->assertSame($apiKey2, $user->getApiKeys()->last());
        $this->assertSame($user, $apiKey1->getUser());
        $this->assertSame($user, $apiKey2->getUser());

        $user->removeApiKey($apiKey1);
        $this->assertCount(1, $user->getApiKeys());
        $this->assertSame($apiKey2, $user->getApiKeys()->first());
    }

    public function testSetEmailGetEmailLowercase()
    {
        $user = $this->getUser();
        $user->setEmail('John.Doe@example.org');

        $this->assertEquals('john.doe@example.org', $user->getEmailLowercase());
    }

    public function testSetUsernameGetEmailLowercase()
    {
        $user = $this->getUser();
        $user->setUsername('John.Doe@example.org');

        $this->assertEquals('john.doe@example.org', $user->getEmailLowercase());
    }
}
