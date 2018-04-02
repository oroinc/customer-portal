<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\CustomerBundle\Acl\Voter\CustomerUserRoleVoter;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class CustomerUserRoleVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerUserRoleVoter
     */
    protected $voter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    protected $container;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->voter = new CustomerUserRoleVoter($this->doctrineHelper);
        $this->voter->setContainer($this->container);
    }

    protected function tearDown()
    {
        unset($this->voter, $this->doctrineHelper, $this->container);
    }

    /**
     * @param bool $isDefaultWebsiteRole
     * @param bool $hasUsers
     * @param int  $expected
     *
     * @dataProvider attributeDeleteDataProvider
     */
    public function testVoteDelete($isDefaultWebsiteRole, $hasUsers, $expected)
    {
        $object = new CustomerUserRole();

        $this->getMocksForVote($object);

        $entityRepository = $this
            ->getMockBuilder('Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entityRepository->expects($this->at(0))
            ->method('isDefaultForWebsite')
            ->will($this->returnValue($isDefaultWebsiteRole));

        $entityRepository->expects($this->at(1))
            ->method('hasAssignedUsers')
            ->will($this->returnValue($hasUsers));

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->with('OroCustomerBundle:CustomerUserRole')
            ->will($this->returnValue($entityRepository));

        /** @var \PHPUnit_Framework_MockObject_MockObject|TokenInterface $token */
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->assertEquals(
            $expected,
            $this->voter->vote($token, $object, [CustomerUserRoleVoter::ATTRIBUTE_DELETE])
        );
    }

    /**
     * @return array
     */
    public function attributeDeleteDataProvider()
    {
        return [
            'common role'          => [
                'isDefaultWebsiteRole' => false,
                'hasUsers'             => false,
                'expected'             => VoterInterface::ACCESS_GRANTED,
            ],
            'default website role' => [
                'isDefaultWebsiteRole' => true,
                'hasUsers'             => false,
                'expected'             => VoterInterface::ACCESS_DENIED,
            ],
            'role wit users'       => [
                'isDefaultWebsiteRole' => false,
                'hasUsers'             => true,
                'expected'             => VoterInterface::ACCESS_DENIED,
            ],
        ];
    }

    /**
     * @param CustomerUser|null $customerUser
     * @param bool             $isGranted
     * @param int              $customerId
     * @param int              $loggedUserCustomerId
     * @param int              $expected
     * @param bool             $failCustomerUserRole
     *
     * @dataProvider attributeFrontendUpdateViewDataProvider
     */
    public function testVoteFrontendUpdate(
        $customerUser,
        $isGranted,
        $customerId,
        $loggedUserCustomerId,
        $expected,
        $failCustomerUserRole = false
    ) {
        /** @var Customer $roleCustomer */
        $roleCustomer = $this->createEntity('Oro\Bundle\CustomerBundle\Entity\Customer', $customerId);

        /** @var Customer $userCustomer */
        $userCustomer = $this->createEntity('Oro\Bundle\CustomerBundle\Entity\Customer', $loggedUserCustomerId);

        if ($failCustomerUserRole) {
            $customerUserRole = new \stdClass();
        } else {
            $customerUserRole = new CustomerUserRole();
            $customerUserRole->setCustomer($roleCustomer);
        }

        if ($customerUser) {
            $customerUser->setCustomer($userCustomer);
        }

        $this->getMocksForVote($customerUserRole);

        if (!$failCustomerUserRole) {
            $this->getMockForUpdateAndView($customerUser, $customerUserRole, $isGranted, 'EDIT');
        }

        /** @var \PHPUnit_Framework_MockObject_MockObject|TokenInterface $token */
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $this->assertEquals(
            $expected,
            $this->voter
                ->vote($token, $customerUserRole, [CustomerUserRoleVoter::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_UPDATE])
        );
    }

    /**
     * @param CustomerUser|null $customerUser
     * @param bool             $isGranted
     * @param int              $customerId
     * @param int              $loggedUserCustomerId
     * @param int              $expected
     * @param bool             $failCustomerUserRole
     * @dataProvider attributeFrontendUpdateViewDataProvider
     */
    public function testVoteFrontendView(
        $customerUser,
        $isGranted,
        $customerId,
        $loggedUserCustomerId,
        $expected,
        $failCustomerUserRole = false
    ) {
        /** @var Customer $roleCustomer */
        $roleCustomer = $this->createEntity('Oro\Bundle\CustomerBundle\Entity\Customer', $customerId);

        /** @var Customer $userCustomer */
        $userCustomer = $this->createEntity('Oro\Bundle\CustomerBundle\Entity\Customer', $loggedUserCustomerId);

        if ($failCustomerUserRole) {
            $customerUserRole = new \stdClass();
        } else {
            $customerUserRole = new CustomerUserRole();
            $customerUserRole->setCustomer($roleCustomer);
        }

        if ($customerUser) {
            $customerUser->setCustomer($userCustomer);
        }

        $this->getMocksForVote($customerUserRole);

        if (!$failCustomerUserRole) {
            $this->getMockForUpdateAndView($customerUser, $customerUserRole, $isGranted, 'VIEW');
        }

        /** @var \PHPUnit_Framework_MockObject_MockObject|TokenInterface $token */
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $this->assertEquals(
            $expected,
            $this->voter
                ->vote(
                    $token,
                    $customerUserRole,
                    [CustomerUserRoleVoter::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_VIEW]
                )
        );
    }

    /**
     * @return array
     */
    public function attributeFrontendUpdateViewDataProvider()
    {
        $customerUser = new CustomerUser();

        return [
            'customer with logged user the same'  => [
                'customerUser'         => $customerUser,
                'isGranted'           => true,
                'customerId'           => 1,
                'loggedUserCustomerId' => 1,
                'expected'            => VoterInterface::ACCESS_GRANTED,
            ],
            'isGranted false'                    => [
                'customerUser'         => $customerUser,
                'isGranted'           => false,
                'customerId'           => 1,
                'loggedUserCustomerId' => 1,
                'expected'            => VoterInterface::ACCESS_DENIED,
            ],
            'without customerUser'                => [
                'customerUser'         => null,
                'isGranted'           => false,
                'customerId'           => 1,
                'loggedUserCustomerId' => 1,
                'expected'            => VoterInterface::ACCESS_DENIED,
            ],
            'without customerUserRole'            => [
                'customerUser'         => null,
                'isGranted'           => false,
                'customerId'           => 1,
                'loggedUserCustomerId' => 1,
                'expected'            => VoterInterface::ACCESS_ABSTAIN,
                'failCustomerUserRole' => true,
            ],
        ];
    }

    /**
     * @param CustomerUserRole|\stdClass $customerUserRole
     */
    protected function getMocksForVote($customerUserRole)
    {
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->with($customerUserRole)
            ->will($this->returnValue(get_class($customerUserRole)));

        $this->voter->setClassName(get_class($customerUserRole));

        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->with($customerUserRole, false)
            ->will($this->returnValue(1));
    }

    /**
     * @param string   $class
     * @param int|null $id
     *
     * @return object
     */
    protected function createEntity($class, $id = null)
    {
        $entity = new $class();
        if ($id) {
            $reflection = new \ReflectionProperty($class, 'id');
            $reflection->setAccessible(true);
            $reflection->setValue($entity, $id);
        }

        return $entity;
    }

    /**
     * @param CustomerUser|null $customerUser
     * @param CustomerUserRole $customerUserRole
     * @param bool             $isGranted
     * @param string           $attribute
     */
    protected function getMockForUpdateAndView($customerUser, $customerUserRole, $isGranted, $attribute)
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->container->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [
                    'security.authorization_checker',
                    ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                    $authorizationChecker
                ],
                [
                    'oro_security.token_accessor',
                    ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                    $tokenAccessor
                ],
            ]);

        $tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);

        $authorizationChecker->expects($customerUser ? $this->once() : $this->never())
            ->method('isGranted')
            ->with($attribute, $customerUserRole)
            ->willReturn($isGranted);
    }
}
