<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ORM\Walker;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\ORM\Walker\CustomerOwnershipConditionDataBuilder;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Fixtures\Entity\TestEntity;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Domain\OneShotIsGrantedObserver;
use Oro\Bundle\SecurityBundle\Acl\Group\AclGroupProviderInterface;
use Oro\Bundle\SecurityBundle\Acl\Voter\AclVoter;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclConditionDataBuilderInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CustomerOwnershipConditionDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    private const ENTITY_NAME = TestEntity::class;
    private const PERMISSIONS = ['EDIT'];

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var OwnershipMetadataProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $metadataProvider;

    /** @var OwnerTree */
    private $tree;

    /** @var AclVoter|\PHPUnit\Framework\MockObject\MockObject */
    private $aclVoter;

    /** @var AclConditionDataBuilderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $ownerConditionBuilder;

    /** @var CustomerOwnershipConditionDataBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $token = $this->createMock(UsernamePasswordOrganizationToken::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($this->getCustomerUser('user3', 'c3', 'org3'));

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->metadataProvider = $this->createMock(OwnershipMetadataProviderInterface::class);
        $this->metadataProvider->expects($this->any())
            ->method('getUserClass')
            ->willReturn(CustomerUser::class);

        $this->tree = new OwnerTree();

        $treeProvider = $this->createMock(OwnerTreeProviderInterface::class);
        $treeProvider->expects($this->any())
            ->method('getTree')
            ->willReturn($this->tree);

        $this->buildTestTree();

        $this->aclVoter = $this->createMock(AclVoter::class);
        $this->ownerConditionBuilder = $this->createMock(AclConditionDataBuilderInterface::class);

        $aclGroupProvider = $this->createMock(AclGroupProviderInterface::class);
        $aclGroupProvider->expects($this->any())
            ->method('getGroup')
            ->willReturn('commerce');

        $this->builder = new CustomerOwnershipConditionDataBuilder(
            $this->authorizationChecker,
            $tokenStorage,
            $this->metadataProvider,
            $treeProvider,
            $this->aclVoter,
            $this->ownerConditionBuilder,
            $aclGroupProvider
        );
    }

    /**
     * @dataProvider getAclConditionDataProvider
     */
    public function testGetAclConditionData(
        array $parentResult,
        OwnershipMetadata $metadata,
        int $accessLevel,
        bool $isGranted,
        array $expected
    ) {
        $this->ownerConditionBuilder->expects($this->any())
            ->method('getAclConditionData')
            ->with(self::ENTITY_NAME, self::PERMISSIONS)
            ->willReturn($parentResult);

        $this->metadataProvider->expects($this->any())
            ->method('getMetadata')
            ->with(self::ENTITY_NAME)
            ->willReturn($metadata);

        $this->aclVoter->expects($this->any())
            ->method('addOneShotIsGrantedObserver')
            ->with($this->isInstanceOf(OneShotIsGrantedObserver::class))
            ->willReturnCallback(
                function (OneShotIsGrantedObserver $observer) use ($accessLevel) {
                    $observer->setAccessLevel($accessLevel);
                }
            );

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->with(
                self::PERMISSIONS,
                new ObjectIdentity('entity', 'commerce@' . self::ENTITY_NAME)
            )
            ->willReturn($isGranted);

        $this->assertEquals($expected, $this->builder->getAclConditionData(self::ENTITY_NAME, self::PERMISSIONS));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getAclConditionDataProvider(): array
    {
        $constraint = [
            'owner',
            [],
            'organization',
            'org3',
            false
        ];

        $frontendMetadata = new FrontendOwnershipMetadata(
            'FRONTEND_USER',
            'customerUser',
            'customer_user_id',
            'organization',
            'organization_id',
            'customer',
            'customer_id'
        );

        return [
            'grant local level' => [
                'parentResult' => $constraint,
                'metadata' => $frontendMetadata,
                'accessLevel' => AccessLevel::LOCAL_LEVEL,
                'isGranted' => true,
                'expected' => [
                    'customer',
                    ['c3'],
                    'organization',
                    'org3',
                    false
                ]
            ],
            'grant deep level' => [
                'parentResult' => $constraint,
                'metadata' => $frontendMetadata,
                'accessLevel' => AccessLevel::DEEP_LEVEL,
                'isGranted' => true,
                'expected' => [
                    'customer',
                    ['c31', 'c32', 'c321', 'c3'],
                    'organization',
                    'org3',
                    false
                ]
            ],
            'incorrect metadata' => [
                'parentResult' => $constraint,
                'metadata' => new OwnershipMetadata('USER', 'owner', 'owner_id', 'organization', 'organization_id'),
                'accessLevel' => AccessLevel::LOCAL_LEVEL,
                'isGranted' => true,
                'expected' => $constraint
            ],
            'no customer field name in metadata' => [
                'parentResult' => $constraint,
                'metadata' => new FrontendOwnershipMetadata(
                    'FRONTEND_USER',
                    'customerUser',
                    'customer_user_id',
                    'organization',
                    'organization_id'
                ),
                'accessLevel' => AccessLevel::LOCAL_LEVEL,
                'isGranted' => true,
                'expected' => $constraint
            ],
            'already denied' => [
                'parentResult' => [
                    'owner',
                    null,
                    'organization',
                    'org3',
                    false
                ],
                'metadata' => $frontendMetadata,
                'accessLevel' => AccessLevel::LOCAL_LEVEL,
                'isGranted' => true,
                'expected' => [
                    'owner',
                    null,
                    'organization',
                    'org3',
                    false
                ]
            ],
            'grant basic level' => [
                'parentResult' => $constraint,
                'metadata' => $frontendMetadata,
                'accessLevel' => AccessLevel::BASIC_LEVEL,
                'isGranted' => true,
                'expected' => $constraint
            ],
            'denied local level' => [
                'parentResult' => $constraint,
                'metadata' => $frontendMetadata,
                'accessLevel' => AccessLevel::LOCAL_LEVEL,
                'isGranted' => false,
                'expected' => $constraint
            ],
        ];
    }

    private function getCustomerUser(string $userId, string $customerId, string $orgId): CustomerUser
    {
        $organization = $this->getEntity(Organization::class, ['id' => $orgId]);

        return $this->getEntity(
            CustomerUser::class,
            [
                'id' => $userId,
                'customer' => $this->getEntity(Customer::class, ['id' => $customerId, 'organization' => $organization]),
                'organization' => $organization
            ]
        );
    }

    private function buildTestTree()
    {
        /**
         * org3
         * |
         * +-c3
         *   |
         *   +-c31
         *   | |
         *   | +-user31
         *   |
         *   +-c32
         *   | |
         *   | +-c321
         *   +-user3
         */
        $this->tree->addBusinessUnit('c3', 'org3');
        $this->tree->addBusinessUnit('c31', 'org3');
        $this->tree->addBusinessUnit('c32', 'org3');
        $this->tree->addBusinessUnit('c321', 'org3');

        $this->buildTree();

        $this->tree->addUser('user3', 'c3');
        $this->tree->addUser('user31', 'c31');

        $this->tree->addUserOrganization('user3', 'org3');
        $this->tree->addUserOrganization('user31', 'org3');

        $this->tree->addUserBusinessUnit('user3', 'org3', 'c3');
        $this->tree->addUserBusinessUnit('user31', 'org3', 'c31');
    }

    private function buildTree()
    {
        $subordinateBusinessUnits = [
            'c3'  => ['c31', 'c32', 'c321'],
            'c32' => ['c321'],
        ];

        foreach ($subordinateBusinessUnits as $parentBuId => $buIds) {
            $this->tree->setSubordinateBusinessUnitIds($parentBuId, $buIds);
        }
    }
}
