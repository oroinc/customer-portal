<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\CustomerBundle\Acl\Voter\CustomerUserRoleVoter;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserRoleVoterTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper */
    private $doctrineHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ContainerInterface */
    private $container;

    /** @var CustomerUserRoleVoter */
    private $voter;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->container = $this->createMock(ContainerInterface::class);

        $this->voter = new CustomerUserRoleVoter($this->doctrineHelper);
        $this->voter->setContainer($this->container);
    }

    /**
     * @param string $attribute
     * @param bool $isCustomerGranted
     * @param bool $withCustomer
     * @param int $expected
     *
     * @dataProvider attributesDataProvider
     */
    public function testVoteAttribute($attribute, $isCustomerGranted, $withCustomer, $expected)
    {
        $object = new CustomerUserRole('');

        $customer = new Customer();
        if ($withCustomer) {
            $object->setCustomer($customer);
        }

        $this->getMocksForVote($object);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->with(CustomerUserRoleVoter::ATTRIBUTE_VIEW, $customer)
            ->willReturn($isCustomerGranted);

        $this->container->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [
                    'security.authorization_checker',
                    ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                    $authorizationChecker
                ]
            ]);

        $token = $this->createMock(TokenInterface::class);
        $this->assertEquals(
            $expected,
            $this->voter->vote($token, $object, [$attribute])
        );
    }

    /**
     * @return array
     */
    public function attributesDataProvider()
    {
        return [
            'VIEW common role' => [
                'attribute' => CustomerUserRoleVoter::ATTRIBUTE_VIEW,
                'isCustomerGranted' => false,
                'withCustomer' => false,
                'expected' => VoterInterface::ACCESS_GRANTED,
            ],
            'VIEW common role allow customer' => [
                'attribute' => CustomerUserRoleVoter::ATTRIBUTE_VIEW,
                'isCustomerGranted' => true,
                'withCustomer' => true,
                'expected' => VoterInterface::ACCESS_GRANTED,
            ],
            'VIEW common role disallow customer' => [
                'attribute' => CustomerUserRoleVoter::ATTRIBUTE_VIEW,
                'isCustomerGranted' => false,
                'withCustomer' => true,
                'expected' => VoterInterface::ACCESS_DENIED,
            ],
            'EDIT common role' => [
                'attribute' => CustomerUserRoleVoter::ATTRIBUTE_EDIT,
                'isCustomerGranted' => false,
                'withCustomer' => false,
                'expected' => VoterInterface::ACCESS_GRANTED,
            ],
            'EDIT common role allow customer' => [
                'attribute' => CustomerUserRoleVoter::ATTRIBUTE_EDIT,
                'isCustomerGranted' => true,
                'withCustomer' => true,
                'expected' => VoterInterface::ACCESS_GRANTED,
            ],
            'EDIT common role disallow customer' => [
                'attribute' => CustomerUserRoleVoter::ATTRIBUTE_EDIT,
                'isCustomerGranted' => false,
                'withCustomer' => true,
                'expected' => VoterInterface::ACCESS_DENIED,
            ],
            'ASSIGN common role' => [
                'attribute' => CustomerUserRoleVoter::ATTRIBUTE_ASSIGN,
                'isCustomerGranted' => false,
                'withCustomer' => false,
                'expected' => VoterInterface::ACCESS_GRANTED,
            ],
            'ASSIGN common role allow customer' => [
                'attribute' => CustomerUserRoleVoter::ATTRIBUTE_ASSIGN,
                'isCustomerGranted' => true,
                'withCustomer' => true,
                'expected' => VoterInterface::ACCESS_GRANTED,
            ],
            'ASSIGN common role disallow customer' => [
                'attribute' => CustomerUserRoleVoter::ATTRIBUTE_ASSIGN,
                'isCustomerGranted' => false,
                'withCustomer' => true,
                'expected' => VoterInterface::ACCESS_DENIED,
            ],
        ];
    }

    /**
     * @param bool $isDefaultWebsiteRole
     * @param bool $hasUsers
     * @param bool $isCustomerGranted
     * @param bool $withCustomer
     * @param int $expected
     *
     * @dataProvider attributeDeleteDataProvider
     */
    public function testVoteDelete($isDefaultWebsiteRole, $hasUsers, $isCustomerGranted, $withCustomer, $expected)
    {
        $object = new CustomerUserRole('');

        $customer = new Customer();
        if ($withCustomer) {
            $object->setCustomer($customer);
        }

        $this->getMocksForVote($object);

        $entityRepository = $this->createMock(CustomerUserRoleRepository::class);
        $entityRepository->expects($this->any())
            ->method('isDefaultOrGuestForWebsite')
            ->willReturn($isDefaultWebsiteRole);
        $entityRepository->expects($this->any())
            ->method('hasAssignedUsers')
            ->willReturn($hasUsers);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->with(CustomerUserRoleVoter::ATTRIBUTE_VIEW, $customer)
            ->willReturn($isCustomerGranted);

        $this->container->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [
                    'security.authorization_checker',
                    ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                    $authorizationChecker
                ]
            ]);

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->with('OroCustomerBundle:CustomerUserRole')
            ->willReturn($entityRepository);

        $token = $this->createMock(TokenInterface::class);

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
            'common role' => [
                'isDefaultWebsiteRole' => false,
                'hasUsers' => false,
                'isCustomerGranted' => false,
                'withCustomer' => false,
                'expected' => VoterInterface::ACCESS_GRANTED,
            ],
            'common role allow customer' => [
                'isDefaultWebsiteRole' => false,
                'hasUsers' => false,
                'isCustomerGranted' => true,
                'withCustomer' => true,
                'expected' => VoterInterface::ACCESS_GRANTED,
            ],
            'common role disallow customer' => [
                'isDefaultWebsiteRole' => false,
                'hasUsers' => false,
                'isCustomerGranted' => false,
                'withCustomer' => true,
                'expected' => VoterInterface::ACCESS_DENIED,
            ],
            'default website role' => [
                'isDefaultWebsiteRole' => true,
                'hasUsers' => false,
                'isCustomerGranted' => false,
                'withCustomer' => false,
                'expected' => VoterInterface::ACCESS_DENIED,
            ],
            'role wit users' => [
                'isDefaultWebsiteRole' => false,
                'hasUsers' => true,
                'isCustomerGranted' => false,
                'withCustomer' => false,
                'expected' => VoterInterface::ACCESS_DENIED,
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
        $roleCustomer = $this->getCustomer($customerId);
        $userCustomer = $this->getCustomer($loggedUserCustomerId);

        if ($failCustomerUserRole) {
            $customerUserRole = new \stdClass();
        } else {
            $customerUserRole = new CustomerUserRole('');
            $customerUserRole->setCustomer($roleCustomer);
        }

        if ($customerUser) {
            $customerUser->setCustomer($userCustomer);
        }

        $this->getMocksForVote($customerUserRole);

        if (!$failCustomerUserRole) {
            $this->getMockForUpdateAndView($customerUser, $customerUserRole, $isGranted, 'EDIT');
        }

        $token = $this->createMock(TokenInterface::class);

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
        $roleCustomer = $this->getCustomer($customerId);
        $userCustomer = $this->getCustomer($loggedUserCustomerId);

        if ($failCustomerUserRole) {
            $customerUserRole = new \stdClass();
        } else {
            $customerUserRole = new CustomerUserRole('');
            $customerUserRole->setCustomer($roleCustomer);
        }

        if ($customerUser) {
            $customerUser->setCustomer($userCustomer);
        }

        $this->getMocksForVote($customerUserRole);

        if (!$failCustomerUserRole) {
            $this->getMockForUpdateAndView($customerUser, $customerUserRole, $isGranted, 'VIEW');
        }

        $token = $this->createMock(TokenInterface::class);

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
    private function getMocksForVote($customerUserRole)
    {
        $this->voter->setClassName(get_class($customerUserRole));

        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->with($customerUserRole, false)
            ->willReturn(1);
    }

    private function getCustomer(int $id = null): Customer
    {
        $entity = new Customer();
        ReflectionUtil::setId($entity, $id);

        return $entity;
    }

    /**
     * @param CustomerUser|null $customerUser
     * @param CustomerUserRole $customerUserRole
     * @param bool             $isGranted
     * @param string           $attribute
     */
    private function getMockForUpdateAndView($customerUser, $customerUserRole, $isGranted, $attribute)
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
