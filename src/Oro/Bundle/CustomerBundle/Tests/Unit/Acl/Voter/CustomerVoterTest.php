<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\CustomerBundle\Acl\Voter\CustomerVoter;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\EntityBundle\Exception\NotManageableEntityException;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerVoterTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerVoter */
    private $voter;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var CustomerUserProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $securityProvider;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var AuthenticationTrustResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $trustResolver;

    /** @var CustomerUserRelationsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $relationsProvider;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->securityProvider = $this->createMock(CustomerUserProvider::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->trustResolver = $this->createMock(AuthenticationTrustResolverInterface::class);
        $this->relationsProvider = $this->createMock(CustomerUserRelationsProvider::class);

        $this->voter = new CustomerVoter(
            $this->doctrineHelper,
            $this->trustResolver,
            $this->authorizationChecker,
            $this->securityProvider,
            $this->relationsProvider
        );
    }

    public function testNotManageableEntityException()
    {
        $object = new \stdClass();
        $class = get_class($object);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($this->getCustomerUser(1));

        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->will($this->throwException(new NotManageableEntityException($class)));

        $this->assertEquals(
            CustomerVoter::ACCESS_ABSTAIN,
            $this->voter->vote($token, $object, [])
        );
    }

    /**
     * @param array $inputData
     * @param int $expectedResult
     *
     * @dataProvider voteProvider
     */
    public function testVote(array $inputData, $expectedResult)
    {
        $object = $inputData['object'];
        if (is_null($object) && is_array($inputData['initObjectParams'])) {
            $object = call_user_func_array([$this, 'getObject'], $inputData['initObjectParams']);
        }
        $class  = is_object($object) ? get_class($object) : null;

        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->willReturn($inputData['objectId']);

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->with($inputData['isGrantedAttr'], $inputData['isGrantedDescr'])
            ->willReturn($inputData['isGranted']);

        $this->securityProvider->expects($this->any())
            ->method('isGrantedViewBasic')
            ->with($class)
            ->willReturn($inputData['grantedViewBasic']);

        $this->securityProvider->expects($this->any())
            ->method('isGrantedViewLocal')
            ->with($class)
            ->willReturn($inputData['grantedViewLocal']);

        $this->securityProvider->expects($this->any())
            ->method('isGrantedEditBasic')
            ->with($class)
            ->willReturn($inputData['grantedEditBasic']);

        $this->securityProvider->expects($this->any())
            ->method('isGrantedEditLocal')
            ->with($class)
            ->willReturn($inputData['grantedEditLocal']);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($inputData['user']);

        $this->trustResolver->expects($this->any())
            ->method('isAnonymous')
            ->with($token)
            ->willReturn(false);

        $this->assertEquals(
            $expectedResult,
            $this->voter->vote($token, $object, $inputData['attributes'])
        );
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function voteProvider()
    {
        return [
            '!CustomerUser' => [
                'input' => [
                    'objectId'      => 2,
                    'object'        => null,
                    'initObjectParams' => null,
                    'user'          => new \stdClass(),
                    'attributes'    => [],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => null,
                    'grantedEditLocal' => null,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_ABSTAIN,
            ],
            '!Entity' => [
                'input' => [
                    'objectId'      => null,
                    'object'        => null,
                    'initObjectParams' => null,
                    'user'          => $this->getCustomerUser(1),
                    'attributes'    => [],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => null,
                    'grantedEditLocal' => null,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_ABSTAIN,
            ],
            'Entity is !object' => [
                'input' => [
                    'objectId'      => null,
                    'object'        => 'string',
                    'initObjectParams' => null,
                    'user'          => $this->getCustomerUser(1),
                    'attributes'    => [],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => null,
                    'grantedEditLocal' => null,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_ABSTAIN,
            ],
            'Entity is not supported' => [
                'input' => [
                    'objectId'      => null,
                    'object'        => new \stdClass(),
                    'initObjectParams' => null,
                    'user'          => $this->getCustomerUser(1),
                    'attributes'    => [],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => null,
                    'grantedEditLocal' => null,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_ABSTAIN,
            ],
            'Entity::VIEW_BASIC and different users' => [
                'input' => [
                    'objectId'      => 1,
                    'object'        => null,
                    'initObjectParams' => ['customerUserId' => 1],
                    'user'          => $this->getCustomerUser(2),
                    'attributes'    => ['ACCOUNT_VIEW'],
                    'grantedViewBasic' => true,
                    'grantedViewLocal' => false,
                    'grantedEditBasic' => null,
                    'grantedEditLocal' => null,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_DENIED,
            ],
            'Entity::VIEW_BASIC and equal users' => [
                'input' => [
                    'objectId'      => 2,
                    'object'        => null,
                    'initObjectParams' => ['customerUserId' => 3],
                    'user'          => $this->getCustomerUser(3),
                    'attributes'    => ['ACCOUNT_VIEW'],
                    'grantedViewBasic' => true,
                    'grantedViewLocal' => false,
                    'grantedEditBasic' => null,
                    'grantedEditLocal' => null,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_GRANTED,
            ],
            'Entity::VIEW_LOCAL, different customers and different users' => [
                'input' => [
                    'objectId'      => 4,
                    'object'        => null,
                    'initObjectParams' => ['customerUserId' => 5, 'customerId' => 6],
                    'user'          => $this->getCustomerUser(7, 8),
                    'attributes'    => ['ACCOUNT_VIEW'],
                    'grantedViewBasic' => false,
                    'grantedViewLocal' => true,
                    'grantedEditBasic' => null,
                    'grantedEditLocal' => null,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_DENIED,
            ],
            'Entity::VIEW_LOCAL, equal customers and different users' => [
                'input' => [
                    'objectId'      => 9,
                    'object'        => null,
                    'initObjectParams' => ['customerUserId' => 10, 'customerId' => 11],
                    'user'          => $this->getCustomerUser(12, 11),
                    'attributes'    => ['ACCOUNT_VIEW'],
                    'grantedViewBasic' => false,
                    'grantedViewLocal' => true,
                    'grantedEditBasic' => null,
                    'grantedEditLocal' => null,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_GRANTED,
            ],
            'Entity::VIEW_LOCAL, different customers and equal users' => [
                'input' => [
                    'objectId'      => 13,
                    'object'        => null,
                    'initObjectParams' => ['customerUserId' => 14, 'customerId' => 15],
                    'user'          => $this->getCustomerUser(14, 17),
                    'attributes'    => ['ACCOUNT_VIEW'],
                    'grantedViewBasic' => false,
                    'grantedViewLocal' => true,
                    'grantedEditBasic' => null,
                    'grantedEditLocal' => null,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_GRANTED,
            ],
            'Entity::EDIT_BASIC and different users' => [
                'input' => [
                    'objectId'      => 21,
                    'object'        => null,
                    'initObjectParams' => ['customerUserId' => 21],
                    'user'          => $this->getCustomerUser(22),
                    'attributes'    => ['ACCOUNT_EDIT'],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => true,
                    'grantedEditLocal' => false,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_DENIED,
            ],
            'Entity::EDIT_BASIC and equal users' => [
                'input' => [
                    'objectId'      => 22,
                    'object'        => null,
                    'initObjectParams' => ['customerUserId' => 23],
                    'user'          => $this->getCustomerUser(23),
                    'attributes'    => ['ACCOUNT_EDIT'],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => true,
                    'grantedEditLocal' => false,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_GRANTED,
            ],
            'Entity::EDIT_LOCAL, different customers and different users' => [
                'input' => [
                    'objectId'      => 24,
                    'object'        => null,
                    'initObjectParams' => ['customerUserId' => 25, 'customerId' => 26],
                    'user'          => $this->getCustomerUser(27, 28),
                    'attributes'    => ['ACCOUNT_EDIT'],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => false,
                    'grantedEditLocal' => true,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_DENIED,
            ],
            'Entity::EDIT_LOCAL, equal customers and different users' => [
                'input' => [
                    'objectId'      => 29,
                    'object'        => null,
                    'initObjectParams' => ['customerUserId' => 30, 'customerId' => 31],
                    'user'          => $this->getCustomerUser(32, 31),
                    'attributes'    => ['ACCOUNT_EDIT'],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => false,
                    'grantedEditLocal' => true,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_GRANTED,
            ],
            'Entity::EDIT_LOCAL, different customers and equal users' => [
                'input' => [
                    'objectId'      => 33,
                    'object'        => null,
                    'initObjectParams' => ['customerUserId' => 34, 'customerId' => 35],
                    'user'          => $this->getCustomerUser(34, 37),
                    'attributes'    => ['ACCOUNT_EDIT'],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => false,
                    'grantedEditLocal' => true,
                    'isGranted'        => null,
                    'isGrantedAttr'    => null,
                    'isGrantedDescr'   => null,
                ],
                'expected' => CustomerVoter::ACCESS_GRANTED,
            ],
            '!ident and !Entity:ACCOUNT_VIEW' => [
                'input' => [
                    'objectId'      => null,
                    'object'        => $this->getIdentity(),
                    'initObjectParams' => null,
                    'user'          => $this->getCustomerUser(38, 39),
                    'attributes'    => ['ACCOUNT_VIEW'],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => false,
                    'grantedEditLocal' => true,
                    'isGranted'        => false,
                    'isGrantedAttr'    => 'VIEW',
                    'isGrantedDescr'   => $this->getDescriptor(),
                ],
                'expected' => CustomerVoter::ACCESS_DENIED,
            ],
            '!ident and !Entity:ACCOUNT_EDIT' => [
                'input' => [
                    'objectId'      => null,
                    'object'        => $this->getIdentity(),
                    'initObjectParams' => null,
                    'user'          => $this->getCustomerUser(40, 41),
                    'attributes'    => ['ACCOUNT_EDIT'],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => false,
                    'grantedEditLocal' => true,
                    'isGranted'        => false,
                    'isGrantedAttr'    => 'EDIT',
                    'isGrantedDescr'   => $this->getDescriptor(),
                ],
                'expected' => CustomerVoter::ACCESS_DENIED,
            ],
            '!ident and Entity:ACCOUNT_VIEW' => [
                'input' => [
                    'objectId'      => null,
                    'object'        => $this->getIdentity(),
                    'initObjectParams' => null,
                    'user'          => $this->getCustomerUser(42, 43),
                    'attributes'    => ['ACCOUNT_VIEW'],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => false,
                    'grantedEditLocal' => true,
                    'isGranted'        => true,
                    'isGrantedAttr'    => 'VIEW',
                    'isGrantedDescr'   => $this->getDescriptor(),
                ],
                'expected' => CustomerVoter::ACCESS_GRANTED,
            ],
            '!ident and Entity:ACCOUNT_EDIT' => [
                'input' => [
                    'objectId'      => null,
                    'object'        => $this->getIdentity(),
                    'initObjectParams' => null,
                    'user'          => $this->getCustomerUser(44, 45),
                    'attributes'    => ['ACCOUNT_EDIT'],
                    'grantedViewBasic' => null,
                    'grantedViewLocal' => null,
                    'grantedEditBasic' => false,
                    'grantedEditLocal' => true,
                    'isGranted'        => true,
                    'isGrantedAttr'    => 'EDIT',
                    'isGrantedDescr'   => $this->getDescriptor(),
                ],
                'expected' => CustomerVoter::ACCESS_GRANTED,
            ],
        ];
    }

    /**
     * @return ObjectIdentity
     */
    private function getIdentity()
    {
        return new ObjectIdentity('entity', 'commerce@' . CustomerOwnerAwareInterface::class);
    }

    /**
     * @return string
     */
    private function getDescriptor()
    {
        return sprintf(
            'entity:%s@%s',
            CustomerUser::SECURITY_GROUP,
            CustomerOwnerAwareInterface::class
        );
    }

    /**
     * @param int $customerUserId
     * @param int $customerId
     * @return CustomerOwnerAwareInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getObject($customerUserId = null, $customerId = null)
    {
        $object = $this->createMock(CustomerOwnerAwareInterface::class);

        if ($customerUserId) {
            $object->expects($this->any())
                ->method('getCustomerUser')
                ->willReturn($this->getCustomerUser($customerUserId, $customerId));

            if ($customerId) {
                $object->expects($this->any())
                    ->method('getCustomer')
                    ->willReturn($this->getCustomer($customerId));
            }
        }

        return $object;
    }

    private function getCustomerUser(int $id, int $customerId = null): CustomerUser
    {
        $user = new CustomerUser();
        ReflectionUtil::setId($user, $id);

        if ($customerId) {
            $user->setCustomer($this->getCustomer($customerId));
        }

        return $user;
    }

    private function getCustomer(int $id): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);

        return $customer;
    }

    /**
     * @param mixed $object
     *
     * @dataProvider voteAnonymousAbstainProvider
     */
    public function testVoteAnonymousAbstain($object)
    {
        $this->authorizationChecker->expects($this->never())
            ->method('isGranted');

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn('anon.');

        $this->trustResolver->expects($this->once())
            ->method('isAnonymous')
            ->with($token)
            ->willReturn(true);

        $this->relationsProvider->expects($this->once())
            ->method('getCustomerIncludingEmpty')
            ->willReturn(new Customer());

        $this->assertEquals(
            CustomerVoter::ACCESS_ABSTAIN,
            $this->voter->vote($token, $object, [CustomerVoter::ATTRIBUTE_VIEW])
        );
    }

    /**
     * @return array
     */
    public function voteAnonymousAbstainProvider()
    {
        return [
            '!Entity' => ['object' => null],
            'Entity is !object' => ['object' => 'string']
        ];
    }

    /**
     * @dataProvider voteAnonymousProvider
     *
     * @param string $attribute
     * @param string $permissionAttribute
     * @param bool $isGranted
     * @param int $expectedResult
     */
    public function testVoteAnonymous($attribute, $permissionAttribute, $isGranted, $expectedResult)
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with($permissionAttribute, $this->getDescriptor())
            ->willReturn($isGranted);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn('anon.');

        $this->trustResolver->expects($this->once())
            ->method('isAnonymous')
            ->with($token)
            ->willReturn(true);

        $this->relationsProvider->expects($this->once())
            ->method('getCustomerIncludingEmpty')
            ->willReturn(new Customer());

        $this->assertEquals(
            $expectedResult,
            $this->voter->vote($token, $this->getIdentity(), [$attribute])
        );
    }

    /**
     * @return array
     */
    public function voteAnonymousProvider()
    {
        return [
            'view allowed' => [
                CustomerVoter::ATTRIBUTE_VIEW,
                'VIEW',
                true,
                CustomerVoter::ACCESS_GRANTED
            ],
            'view denied' => [
                CustomerVoter::ATTRIBUTE_VIEW,
                'VIEW',
                false,
                CustomerVoter::ACCESS_DENIED
            ],
            'edit allowed' => [
                CustomerVoter::ATTRIBUTE_EDIT,
                'EDIT',
                true,
                CustomerVoter::ACCESS_GRANTED
            ],
            'edit denied' => [
                CustomerVoter::ATTRIBUTE_EDIT,
                'EDIT',
                false,
                CustomerVoter::ACCESS_DENIED
            ],
        ];
    }
}
