<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Manager;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
use Oro\Bundle\CustomerBundle\Manager\CustomerUserInvitationManager;
use Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub\CustomerUserInvitationStub;
use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Generator\RandomTokenGeneratorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserInvitationManagerTest extends TestCase
{
    private const TOKEN = 'plain-invitation-token';
    private const TTL = 3600;

    private ManagerRegistry&MockObject $doctrine;
    private EntityManagerInterface&MockObject $entityManager;
    private EntityRepository&MockObject $invitationRepository;
    private CustomerUserRepository&MockObject $customerUserRepository;
    private CustomerUserManager&MockObject $customerUserManager;
    private ConfigManager&MockObject $configManager;
    private FeatureChecker&MockObject $featureChecker;
    private RandomTokenGeneratorInterface&MockObject $tokenGenerator;
    private Processor&MockObject $mailer;
    private EnumOptionsProvider&MockObject $enumOptionsProvider;
    private LoggerInterface&MockObject $logger;
    private CustomerUserInvitationManager $manager;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRepository', 'persist', 'flush', 'find', 'wrapInTransaction'])
            ->getMock();
        $this->invitationRepository = $this->createMock(EntityRepository::class);
        $this->customerUserRepository = $this->createMock(CustomerUserRepository::class);
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->featureChecker = $this->createMock(FeatureChecker::class);
        $this->tokenGenerator = $this->createMock(RandomTokenGeneratorInterface::class);
        $this->mailer = $this->createMock(Processor::class);
        $this->enumOptionsProvider = $this->createMock(EnumOptionsProvider::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->doctrine->method('getManagerForClass')
            ->willReturn($this->entityManager);
        $this->entityManager->method('getRepository')
            ->willReturnCallback(fn (string $className) => match ($className) {
                CustomerUserInvitation::class => $this->invitationRepository,
                CustomerUser::class => $this->customerUserRepository,
            });
        $this->enumOptionsProvider->method('getEnumOptionByCode')
            ->willReturnCallback(
                static fn (string $enumCode, string $status) => new TestEnumValue(
                    $enumCode,
                    ucfirst($status),
                    $status
                )
            );

        $this->manager = new CustomerUserInvitationManager(
            $this->doctrine,
            $this->customerUserManager,
            $this->configManager,
            $this->featureChecker,
            $this->tokenGenerator,
            $this->mailer,
            $this->enumOptionsProvider,
            $this->logger,
            self::TTL
        );
    }

    /**
     * @dataProvider caseInsensitiveEmailAddressesDataProvider
     */
    public function testInviteNewCustomerUser(bool $caseInsensitiveEmailAddressesEnabled): void
    {
        $invitation = $this->getValidInvitation();
        $this->configManager->expects(self::exactly(2))
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);
        $this->expectNoExistingCustomerUser($invitation, $caseInsensitiveEmailAddressesEnabled);
        $this->invitationRepository->expects(self::once())
            ->method('findOneBy')
            ->with(
                [
                    'organization' => $invitation->getOrganization(),
                    $caseInsensitiveEmailAddressesEnabled ? 'emailLowercase' : 'email' =>
                        $caseInsensitiveEmailAddressesEnabled
                            ? $invitation->getEmailLowercase()
                            : $invitation->getEmail()
                ],
                ['id' => 'DESC']
            )
            ->willReturn(null);
        $this->tokenGenerator->expects(self::once())
            ->method('generateToken')
            ->willReturn(self::TOKEN);
        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with($invitation);
        $this->entityManager->expects(self::exactly(2))
            ->method('flush');
        $this->mailer->expects(self::once())
            ->method('sendInvitation')
            ->with($invitation, self::TOKEN)
            ->willReturn(1);

        self::assertEquals($invitation, $this->manager->invite($invitation));
        self::assertEquals(CustomerUserInvitation::STATUS_PENDING, $invitation->getStatus()->getInternalId());
        self::assertEquals(hash('sha256', self::TOKEN), $invitation->getTokenHash());
        self::assertNotNull($invitation->getSentAt());
        self::assertGreaterThan($invitation->getSentAt(), $invitation->getExpiresAt());
        self::assertNull($invitation->getAcceptedAt());
        self::assertNull($invitation->getRevokedAt());
    }

    /**
     * @dataProvider caseInsensitiveEmailAddressesDataProvider
     */
    public function testInviteRejectsExistingCustomerUserAccordingToEmailCaseSensitivity(
        bool $caseInsensitiveEmailAddressesEnabled
    ): void {
        $invitation = $this->getValidInvitation();
        $invitation->setEmail('Invitee@Example.com');
        $existingCustomerUser = new CustomerUser();
        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);
        $this->customerUserRepository->expects(self::once())
            ->method('findUserByEmailAndOrganization')
            ->with(
                $invitation->getEmail(),
                $invitation->getOrganization(),
                $caseInsensitiveEmailAddressesEnabled
            )
            ->willReturn($existingCustomerUser);
        $this->invitationRepository->expects(self::never())
            ->method('findOneBy');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A customer user with this email already exists.');

        $this->manager->invite($invitation);
    }

    public static function caseInsensitiveEmailAddressesDataProvider(): array
    {
        return [
            'case-sensitive' => [false],
            'case-insensitive' => [true]
        ];
    }

    public function testInviteReusesInvitationInTheSameScope(): void
    {
        $invitation = $this->getValidInvitation();
        $invitation->setEmail('Invitee@Example.com');
        $invitation->getCustomer()->setName('Submitted Customer');
        $newInviter = new CustomerUser();
        $invitation->setInvitedBy($newInviter);
        $storedInvitation = $this->getValidInvitation(CustomerUserInvitation::STATUS_PENDING)
            ->setTokenHash('token-hash')
            ->setExpiresAt(new \DateTime('+1 hour', new \DateTimeZone('UTC')));
        $storedInvitation->getCustomer()->setName('Stored Customer');
        $this->expectNoExistingCustomerUser($invitation);
        $this->invitationRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($storedInvitation);
        $this->tokenGenerator->expects(self::once())
            ->method('generateToken')
            ->willReturn(self::TOKEN);
        $this->entityManager->expects(self::never())
            ->method('persist');
        $this->entityManager->expects(self::exactly(2))
            ->method('flush');
        $this->mailer->expects(self::once())
            ->method('sendInvitation')
            ->with($storedInvitation, self::TOKEN)
            ->willReturn(1);

        self::assertEquals($storedInvitation, $this->manager->invite($invitation));
        self::assertEquals('Invitee@Example.com', $storedInvitation->getEmail());
        self::assertEquals('Stored Customer', $storedInvitation->getCustomer()->getName());
        self::assertEquals($newInviter, $storedInvitation->getInvitedBy());
        self::assertEquals($invitation->getRoles()->toArray(), $storedInvitation->getRoles()->toArray());
    }

    public function testInviteRejectsStoredInvitationFromAnotherScope(): void
    {
        $invitation = $this->getValidInvitation();
        $storedInvitation = $this->getValidInvitation(CustomerUserInvitation::STATUS_PENDING)
            ->setTokenHash('token-hash')
            ->setExpiresAt(new \DateTime('+1 hour', new \DateTimeZone('UTC')));
        ReflectionUtil::setId($storedInvitation->getCustomer(), 99);
        $this->expectNoExistingCustomerUser($invitation);
        $this->invitationRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($storedInvitation);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('This email address already has an invitation in another scope.');

        $this->manager->invite($invitation);
    }

    /**
     * @dataProvider inactiveInvitationDataProvider
     */
    public function testInviteCreatesNewInvitationWhenStoredInvitationIsInactive(
        string $status,
        string $expiresAt
    ): void {
        $invitation = $this->getValidInvitation();
        $storedInvitation = $this->getValidInvitation($status)
            ->setTokenHash('old-token-hash')
            ->setExpiresAt(new \DateTime($expiresAt, new \DateTimeZone('UTC')));
        ReflectionUtil::setId($storedInvitation->getCustomer(), 99);
        $this->expectNoExistingCustomerUser($invitation);
        $this->invitationRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($storedInvitation);
        $this->tokenGenerator->expects(self::once())
            ->method('generateToken')
            ->willReturn(self::TOKEN);
        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with($invitation);
        $this->entityManager->expects(self::exactly(2))
            ->method('flush');
        $this->mailer->expects(self::once())
            ->method('sendInvitation')
            ->with($invitation, self::TOKEN)
            ->willReturn(1);

        self::assertEquals($invitation, $this->manager->invite($invitation));
    }

    public static function inactiveInvitationDataProvider(): array
    {
        return [
            'expired' => [CustomerUserInvitation::STATUS_PENDING, '-1 second'],
            'revoked' => [CustomerUserInvitation::STATUS_REVOKED, '+1 hour']
        ];
    }

    public function testInviteKeepsStoredInvitationWhenEmailCannotBeSent(): void
    {
        $invitation = $this->getValidInvitation();
        $this->expectNoExistingCustomerUser($invitation);
        $this->invitationRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null);
        $this->tokenGenerator->expects(self::once())
            ->method('generateToken')
            ->willReturn(self::TOKEN);
        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with($invitation);
        $this->entityManager->expects(self::once())
            ->method('flush');
        $this->mailer->expects(self::once())
            ->method('sendInvitation')
            ->willReturn(0);
        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                'Unable to send a customer user invitation to "{email}".',
                ['email' => $invitation->getEmail()]
            );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The customer user invitation email could not be sent.');

        $this->manager->invite($invitation);
    }

    public function testResendLogsMailerException(): void
    {
        $invitation = $this->getValidInvitation(CustomerUserInvitation::STATUS_PENDING);
        ReflectionUtil::setId($invitation, 55);
        $this->expectNoExistingCustomerUser($invitation);
        $this->tokenGenerator->method('generateToken')->willReturn(self::TOKEN);
        $this->entityManager->expects(self::once())->method('flush');
        $exception = new \RuntimeException('Email service failed.');
        $this->mailer->method('sendInvitation')->willThrowException($exception);
        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                'Unable to send a customer user invitation.',
                ['exception' => $exception, 'invitation_id' => 55]
            );

        $this->expectExceptionObject($exception);

        $this->manager->resend($invitation);
    }

    public function testResendRejectsNonPendingInvitation(): void
    {
        $invitation = $this->getValidInvitation(CustomerUserInvitation::STATUS_ACCEPTED);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Only pending customer user invitations can be resent.');

        $this->manager->resend($invitation);
    }

    public function testRevokePendingInvitation(): void
    {
        $invitation = $this->getValidInvitation(CustomerUserInvitation::STATUS_PENDING)
            ->setTokenHash('token-hash');
        $this->entityManager->expects(self::once())
            ->method('flush');

        $this->manager->revoke($invitation);

        self::assertEquals(CustomerUserInvitation::STATUS_REVOKED, $invitation->getStatus()->getInternalId());
        self::assertNull($invitation->getTokenHash());
        self::assertNotNull($invitation->getRevokedAt());
    }

    public function testRevokeDoesNothingForNonPendingInvitation(): void
    {
        $invitation = $this->getValidInvitation(CustomerUserInvitation::STATUS_ACCEPTED);
        $this->entityManager->expects(self::never())
            ->method('flush');

        $this->manager->revoke($invitation);

        self::assertEquals(CustomerUserInvitation::STATUS_ACCEPTED, $invitation->getStatus()->getInternalId());
    }

    /**
     * @dataProvider invalidTokenDataProvider
     */
    public function testFindByTokenRejectsInvalidToken(string $token): void
    {
        $this->invitationRepository->expects(self::never())
            ->method('findOneBy');

        self::assertNull($this->manager->findByToken($token));
    }

    public static function invalidTokenDataProvider(): array
    {
        return [
            'empty' => [''],
            'too short' => [str_repeat('a', 63)],
            'too long' => [str_repeat('a', 65)],
            'not hexadecimal' => [str_repeat('z', 64)],
        ];
    }

    public function testFindByToken(): void
    {
        $token = str_repeat('a', 64);
        $invitation = $this->getValidInvitation();
        $this->invitationRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['tokenHash' => hash('sha256', $token)])
            ->willReturn($invitation);

        self::assertEquals($invitation, $this->manager->findByToken($token));
    }

    public function testIsUsable(): void
    {
        $invitation = $this->getValidInvitation(CustomerUserInvitation::STATUS_PENDING)
            ->setTokenHash('token-hash')
            ->setExpiresAt(new \DateTime('+1 hour', new \DateTimeZone('UTC')));
        self::assertTrue($this->manager->isUsable($invitation));

        $invitation->setExpiresAt(new \DateTime('-1 second', new \DateTimeZone('UTC')));
        self::assertFalse($this->manager->isUsable($invitation));

        $invitation->setExpiresAt(new \DateTime('+1 hour', new \DateTimeZone('UTC')))
            ->setTokenHash(null);
        self::assertFalse($this->manager->isUsable($invitation));

        $invitation->setTokenHash('token-hash')
            ->setStatus($this->createStatus(CustomerUserInvitation::STATUS_REVOKED));
        self::assertFalse($this->manager->isUsable($invitation));
    }

    public function testCreateCustomerUser(): void
    {
        $invitation = $this->getValidInvitation(CustomerUserInvitation::STATUS_PENDING)
            ->setTokenHash('token-hash')
            ->setExpiresAt(new \DateTime('+1 hour', new \DateTimeZone('UTC')));
        $owner = new User();
        $this->expectNoExistingCustomerUser($invitation);
        $this->configManager->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(static fn (string $name) => match ($name) {
                'oro_customer.case_insensitive_email_addresses_enabled' => false,
                'oro_customer.default_customer_owner' => 42
            });
        $this->entityManager->expects(self::once())
            ->method('find')
            ->with(User::class, 42)
            ->willReturn($owner);

        $customerUser = $this->manager->createCustomerUser($invitation);

        self::assertEquals($invitation->getEmail(), $customerUser->getEmail());
        self::assertEquals($owner, $customerUser->getOwner());
        self::assertEquals($invitation->getOrganization(), $customerUser->getOrganization());
        self::assertEquals($invitation->getWebsite(), $customerUser->getWebsite());
        self::assertEquals($invitation->getCustomer(), $customerUser->getCustomer());
        self::assertTrue($customerUser->isEnabled());
        self::assertTrue($customerUser->isConfirmed());
        self::assertEquals($invitation->getRoles()->toArray(), $customerUser->getUserRoles());
    }

    public function testCreateCustomerUserRequiresRoles(): void
    {
        $invitation = $this->getValidInvitation(CustomerUserInvitation::STATUS_PENDING)
            ->setRoles([])
            ->setTokenHash('token-hash')
            ->setExpiresAt(new \DateTime('+1 hour', new \DateTimeZone('UTC')));
        $this->expectNoExistingCustomerUser($invitation);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('At least one customer user role is required.');

        $this->manager->createCustomerUser($invitation);
    }

    public function testAcceptInvitation(): void
    {
        $token = str_repeat('a', 64);
        $invitation = $this->getValidInvitation(CustomerUserInvitation::STATUS_PENDING)
            ->setTokenHash(hash('sha256', $token))
            ->setExpiresAt(new \DateTime('+1 hour', new \DateTimeZone('UTC')));
        ReflectionUtil::setId($invitation, 55);
        $customerUser = new CustomerUser();
        $owner = new User();

        $this->entityManager->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $callback) => $callback());
        $this->entityManager->expects(self::exactly(2))
            ->method('find')
            ->willReturnCallback(
                static function (string $className, int $id, ?int $lockMode = null) use ($invitation, $owner) {
                    if ($className === CustomerUserInvitation::class) {
                        self::assertEquals(55, $id);
                        self::assertEquals(LockMode::PESSIMISTIC_WRITE, $lockMode);

                        return $invitation;
                    }

                    self::assertEquals(User::class, $className);
                    self::assertEquals(42, $id);

                    return $owner;
                }
            );
        $this->expectNoExistingCustomerUser($invitation);
        $this->configManager->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(static fn (string $name) => match ($name) {
                'oro_customer.case_insensitive_email_addresses_enabled' => false,
                'oro_customer.default_customer_owner' => 42
            });
        $this->featureChecker->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('customer_user_login_password')
            ->willReturn(false);
        $this->customerUserManager->expects(self::once())
            ->method('generatePassword')
            ->with(32)
            ->willReturn('generated-password');
        $this->customerUserManager->expects(self::once())
            ->method('updateWebsiteSettings')
            ->with($customerUser);
        $this->customerUserManager->expects(self::once())
            ->method('setAuthStatus')
            ->with($customerUser, CustomerUserManager::STATUS_ACTIVE);
        $this->customerUserManager->expects(self::once())
            ->method('updateUser')
            ->with($customerUser, false);
        $this->entityManager->expects(self::once())
            ->method('flush');

        self::assertEquals($customerUser, $this->manager->accept($invitation, $customerUser, $token));
        self::assertEquals($invitation->getEmail(), $customerUser->getEmail());
        self::assertEquals('generated-password', $customerUser->getPlainPassword());
        self::assertEquals($customerUser, $invitation->getAcceptedUser());
        self::assertNotNull($invitation->getAcceptedAt());
        self::assertNull($invitation->getTokenHash());
        self::assertEquals(CustomerUserInvitation::STATUS_ACCEPTED, $invitation->getStatus()->getInternalId());
    }

    private function getValidInvitation(?string $status = null): CustomerUserInvitation
    {
        $organization = new Organization();
        $organization->setId(1);
        $customer = new Customer();
        ReflectionUtil::setId($customer, 2);
        $website = new Website();
        ReflectionUtil::setId($website, 3);
        $role = (new CustomerUserRole('ROLE_BUYER'))
            ->setOrganization($organization);

        $invitation = (new CustomerUserInvitationStub())
            ->setOrganization($organization)
            ->setWebsite($website)
            ->setCustomer($customer)
            ->setEmail('invitee@example.com')
            ->addRole($role);

        if ($status !== null) {
            $invitation->setStatus($this->createStatus($status));
        }

        return $invitation;
    }

    private function createStatus(string $status): TestEnumValue
    {
        return new TestEnumValue(CustomerUserInvitation::INTERNAL_STATUS_CODE, ucfirst($status), $status);
    }

    private function expectNoExistingCustomerUser(
        CustomerUserInvitation $invitation,
        bool $caseInsensitiveEmailAddressesEnabled = false
    ): void {
        $this->customerUserRepository->expects(self::once())
            ->method('findUserByEmailAndOrganization')
            ->with(
                $invitation->getEmail(),
                $invitation->getOrganization(),
                $caseInsensitiveEmailAddressesEnabled
            )
            ->willReturn(null);
    }
}
