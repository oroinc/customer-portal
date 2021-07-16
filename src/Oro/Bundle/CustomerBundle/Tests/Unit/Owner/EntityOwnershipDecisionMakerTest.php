<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\CustomerBundle\Owner\EntityOwnershipDecisionMaker;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Fixtures\Entity\TestEntity;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\Organization;
use Oro\Bundle\SecurityBundle\Tests\Unit\Owner\AbstractCommonEntityOwnershipDecisionMakerTest;
use Oro\Bundle\SecurityBundle\Tests\Unit\Stub\OwnershipMetadataProviderStub;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class EntityOwnershipDecisionMakerTest extends AbstractCommonEntityOwnershipDecisionMakerTest
{
    use EntityTrait;

    /** @var \PHPUnit\Framework\MockObject\MockObject|OwnerTreeProvider */
    protected $treeProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityOwnershipDecisionMaker */
    protected $decisionMaker;

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var Customer */
    protected $cust1;
    /** @var Customer */
    protected $cust2;
    /** @var Customer */
    protected $cust3;
    /** @var Customer */
    protected $cust31;
    /** @var Customer */
    protected $cust4;
    /** @var Customer */
    protected $cust41;
    /** @var Customer */
    protected $cust411;

    /** @var CustomerUser */
    protected $custUsr1;
    /** @var CustomerUser */
    protected $custUsr2;
    /** @var CustomerUser */
    protected $custUsr3;
    /** @var CustomerUser */
    protected $custUsr31;
    /** @var CustomerUser */
    protected $custUsr4;
    /** @var CustomerUser */
    protected $custUsr411;

    protected function setUp(): void
    {
        $this->tree = new OwnerTree();

        $this->metadataProvider = new OwnershipMetadataProviderStub($this, ['user' => CustomerUser::class]);
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getOrganizationClass(),
            new FrontendOwnershipMetadata()
        );
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getBusinessUnitClass(),
            new FrontendOwnershipMetadata('FRONTEND_CUSTOMER', 'customer', 'customer_id', 'organization')
        );
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getUserClass(),
            new FrontendOwnershipMetadata(
                'FRONTEND_USER',
                'customerUser',
                'customer_user_id',
                'organization',
                'organization_id',
                'customer',
                'customer_id'
            )
        );

        /** @var \PHPUnit\Framework\MockObject\MockObject|OwnerTreeProvider $this->treeProvider */
        $this->treeProvider = $this->createMock(OwnerTreeProvider::class);
        $this->treeProvider->expects($this->any())
            ->method('getTree')
            ->will($this->returnValue($this->tree));

        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        /** @var DoctrineHelper $doctrineHelper */
        $doctrineHelper = $this->createMock(DoctrineHelper::class);

        $repository = $this->createMock(CustomerRepository::class);
        $repository->expects($this->any())
            ->method('getChildrenIds')
            ->willReturnMap(
                [
                    ['cust1', null, []],
                    ['cust2', null, []],
                    ['cust3', null, ['cust31']],
                    ['cust31', null, []],
                    ['cust3a', null, ['cust3a1']],
                    ['cust3a1', null, []],
                    ['cust4', null, ['cust41', 'cust411']],
                    ['cust41', null, ['cust411']],
                    ['cust411', null, []],
                ]
            );

        /** @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject $manager */
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->any())
            ->method('getRepository')
            ->with(Customer::class)
            ->willReturn($repository);

        /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject $doctrine */
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->with(Customer::class)
            ->willReturn($manager);

        $this->decisionMaker = new EntityOwnershipDecisionMaker(
            $this->treeProvider,
            new ObjectIdAccessor($doctrineHelper),
            new EntityOwnerAccessor($this->metadataProvider, (new InflectorFactory())->build()),
            $this->metadataProvider,
            $this->tokenAccessor,
            $doctrine,
            new PropertyAccessor()
        );
    }

    /**
     * @dataProvider supportsDataProvider
     *
     * @param mixed $user
     * @param bool $expectedResult
     */
    public function testSupports($user, $expectedResult)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expectedResult, $this->decisionMaker->supports());
    }

    /**
     * @return array
     */
    public function supportsDataProvider()
    {
        return [
            'without user' => [
                'user' => null,
                'expectedResult' => false,
            ],
            'unsupported user' => [
                'user' => new \stdClass(),
                'expectedResult' => false,
            ],
            'supported user' => [
                'user' => new CustomerUser(),
                'expectedResult' => true,
            ],
        ];
    }

    protected function buildTestTree()
    {
        $this->org1 = new Organization('org1');
        $this->org2 = new Organization('org2');
        $this->org3 = new Organization('org3');
        $this->org4 = new Organization('org4');

        $this->cust1 = $this->getEntity(Customer::class, ['id' => 'cust1']);
        $this->cust2 = $this->getEntity(Customer::class, ['id' => 'cust2']);
        $this->cust3 = $this->getEntity(Customer::class, ['id' => 'cust3']);
        $this->cust31 = $this->getEntity(Customer::class, ['id' => 'cust31']);
        $this->cust4 = $this->getEntity(Customer::class, ['id' => 'cust4']);
        $this->cust41 = $this->getEntity(Customer::class, ['id' => 'cust41', 'parent' => $this->cust4]);
        $this->cust411 = $this->getEntity(Customer::class, ['id' => 'cust411', 'parent' => $this->cust41]);

        $this->custUsr1 = $this->getEntity(
            CustomerUser::class,
            ['id' => 'custUsr1', 'customer' => null, 'organization' => $this->org1]
        );
        $this->custUsr2 = $this->getEntity(
            CustomerUser::class,
            ['id' => 'custUsr2', 'customer' => $this->cust2, 'organization' => $this->org2]
        );
        $this->custUsr3 = $this->getEntity(
            CustomerUser::class,
            ['id' => 'custUsr3', 'customer' => $this->cust3, 'organization' => $this->org3]
        );
        $this->custUsr31 = $this->getEntity(
            CustomerUser::class,
            ['id' => 'custUsr31', 'customer' => $this->cust31, 'organization' => $this->org3]
        );
        $this->custUsr4 = $this->getEntity(
            CustomerUser::class,
            ['id' => 'custUsr4', 'customer' => $this->cust4, 'organization' => $this->org4]
        );
        $this->custUsr411 = $this->getEntity(
            CustomerUser::class,
            ['id' => 'custUsr411', 'customer' => $this->cust411, 'organization' => $this->org4]
        );

        $this->tree->addBusinessUnit('cust1', null);
        $this->tree->addBusinessUnit('cust2', null);
        $this->tree->addBusinessUnit('cust3', 'org3');
        $this->tree->addBusinessUnit('cust31', 'org3');
        $this->tree->addBusinessUnit('cust3a', 'org3');
        $this->tree->addBusinessUnit('cust3a1', 'org3');
        $this->tree->addBusinessUnit('cust4', 'org4');
        $this->tree->addBusinessUnit('cust41', 'org4');
        $this->tree->addBusinessUnit('cust411', 'org4');

        $subordinateBusinessUnits  = [
            'cust3'  => ['cust31'],
            'cust3a' => ['cust3a1'],
            'cust41' => ['cust411'],
            'cust4'  => ['cust41', 'cust411'],
        ];

        foreach ($subordinateBusinessUnits as $parentBuId => $buIds) {
            $this->tree->setSubordinateBusinessUnitIds($parentBuId, $buIds);
        }

        $this->tree->addUser('custUsr1', null);
        $this->tree->addUser('custUsr2', 'cust2');
        $this->tree->addUser('custUsr3', 'cust3');
        $this->tree->addUser('custUsr31', 'cust31');
        $this->tree->addUser('custUsr4', 'cust4');
        $this->tree->addUser('custUsr41', 'cust41');
        $this->tree->addUser('custUsr411', 'cust411');

        $this->tree->addUserOrganization('custUsr1', 'org1');
        $this->tree->addUserOrganization('custUsr1', 'org2');
        $this->tree->addUserOrganization('custUsr2', 'org2');
        $this->tree->addUserOrganization('custUsr3', 'org2');
        $this->tree->addUserOrganization('custUsr3', 'org3');
        $this->tree->addUserOrganization('custUsr31', 'org3');
        $this->tree->addUserOrganization('custUsr4', 'org4');
        $this->tree->addUserOrganization('custUsr411', 'org4');

        $this->tree->addUserBusinessUnit('custUsr1', 'org1', 'cust1');
        $this->tree->addUserBusinessUnit('custUsr1', 'org2', 'cust2');
        $this->tree->addUserBusinessUnit('custUsr2', 'org2', 'cust2');
        $this->tree->addUserBusinessUnit('custUsr3', 'org3', 'cust3');
        $this->tree->addUserBusinessUnit('custUsr3', 'org2', 'cust2');
        $this->tree->addUserBusinessUnit('custUsr31', 'org3', 'cust31');
        $this->tree->addUserBusinessUnit('custUsr4', 'org4', 'cust4');
        $this->tree->addUserBusinessUnit('custUsr411', 'org4', 'cust411');
    }

    /**
     * @dataProvider isAssociatedWithBusinessUnitDataProvider
     */
    public function testIsAssociatedWithBusinessUnitWhenNoCustomerUserForDomainObject(
        string $organization,
        string $customer,
        string $customerUser,
        bool $deep,
        bool $isAssociated
    ) {
        $this->buildTestTree();
        $this->configureMetadataProvider();

        $domainObject = new TestEntity(1, null, $this->{$organization}, null, $this->{$customer});

        $this->assertEquals(
            $isAssociated,
            $this->decisionMaker->isAssociatedWithBusinessUnit($this->{$customerUser}, $domainObject, $deep)
        );
    }

    public function isAssociatedWithBusinessUnitDataProvider(): array
    {
        return [
            'with local access and same customer' => [
                'organization' => 'org2',
                'customer' => 'cust2',
                'customerUser' => 'custUsr2',
                'deep' => false,
                'isAssociated' => true
            ],
            'with deep access and same customer' => [
                'organization' => 'org2',
                'customer' => 'cust2',
                'customerUser' => 'custUsr2',
                'deep' => true,
                'isAssociated' => true
            ],
            'with local access and subordinate customer' => [
                'organization' => 'org4',
                'customer' => 'cust411',
                'customerUser' => 'custUsr4',
                'deep' => false,
                'isAssociated' => false
            ],
            'with deep access and subordinate customer' => [
                'organization' => 'org4',
                'customer' => 'cust411',
                'customerUser' => 'custUsr4',
                'deep' => true,
                'isAssociated' => true
            ],
            'with local access and not subordinate customer' => [
                'organization' => 'org4',
                'customer' => 'cust4',
                'customerUser' => 'custUsr411',
                'deep' => false,
                'isAssociated' => false
            ],
            'with deep access and not subordinate customer' => [
                'organization' => 'org4',
                'customer' => 'cust4',
                'customerUser' => 'custUsr411',
                'deep' => true,
                'isAssociated' => false
            ],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testIsAssociatedWithBusinessUnit()
    {
        $this->buildTestTree();
        $this->configureMetadataProvider();

        $obj = new TestEntity(1, null, $this->org1);
        $obj1 = new TestEntity(1, null, $this->org1, $this->custUsr1, $this->cust1);
        $obj2 = new TestEntity(1, null, $this->org2, $this->custUsr2, $this->cust2);
        $obj3 = new TestEntity(1, null, $this->org3, $this->custUsr3, $this->cust3);
        $obj31 = new TestEntity(1, null, $this->org3, $this->custUsr31, $this->cust31);
        $obj4 = new TestEntity(1, null, $this->org4, $this->custUsr4, $this->cust41);
        $obj411 = new TestEntity(1, null, $this->org4, $this->custUsr411, $this->cust411);

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj1));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj1, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj2));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj2, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj31, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj411));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr1, $obj411, true));

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj1));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj1, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj2));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj2, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj31, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj411));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr2, $obj411, true));

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj1));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj1, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj2));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj2, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj3));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj31, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj411));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr3, $obj411, true));

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj1, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj2, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj3, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj31));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj31, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj411));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr31, $obj411, true));

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj1, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj2, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj31, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj4));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj4, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj411));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr4, $obj411, true));

        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj1));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj1, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj2));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj2, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj3));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj3, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj31));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj31, true));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj4));
        $this->assertFalse($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj4, true));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj411));
        $this->assertTrue($this->decisionMaker->isAssociatedWithBusinessUnit($this->custUsr411, $obj411, true));
    }

    private function configureMetadataProvider(): void
    {
        $this->metadataProvider->setMetadata(
            TestEntity::class,
            new FrontendOwnershipMetadata(
                'FRONTEND_USER',
                'customerUser',
                'customer_user_id',
                'organization',
                'organization_id',
                'customer',
                'customer_id'
            )
        );
    }
}
