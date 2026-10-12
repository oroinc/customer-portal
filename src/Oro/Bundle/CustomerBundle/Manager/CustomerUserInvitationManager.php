<?php

namespace Oro\Bundle\CustomerBundle\Manager;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SecurityBundle\Generator\RandomTokenGeneratorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Psr\Log\LoggerInterface;

/**
 * Manages the lifecycle of customer user invitations.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerUserInvitationManager
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly CustomerUserManager $customerUserManager,
        private readonly ConfigManager $configManager,
        private readonly FeatureChecker $featureChecker,
        private readonly RandomTokenGeneratorInterface $tokenGenerator,
        private readonly Processor $mailer,
        private readonly EnumOptionsProvider $enumOptionsProvider,
        private readonly LoggerInterface $logger,
        private readonly int $ttl
    ) {
    }

    public function invite(CustomerUserInvitation $invitation): CustomerUserInvitation
    {
        $this->assertInvitationDataIsValid($invitation);

        $manager = $this->getEntityManager();
        $invitationToSend = $this->getInvitationToSend($invitation, $manager);
        $token = $this->prepareForSending($invitationToSend);
        $manager->flush();
        $this->send($invitationToSend, $token, $manager);

        return $invitationToSend;
    }

    public function resend(CustomerUserInvitation $invitation): void
    {
        if (!$invitation->isPending()) {
            throw new \LogicException('Only pending customer user invitations can be resent.');
        }

        $this->assertInvitationDataIsValid($invitation);
        $manager = $this->getEntityManager();
        $token = $this->prepareForSending($invitation);
        $manager->flush();
        $this->send($invitation, $token, $manager);
    }

    public function revoke(CustomerUserInvitation $invitation): void
    {
        if (!$invitation->isPending()) {
            return;
        }

        $this->setStatus($invitation, CustomerUserInvitation::STATUS_REVOKED);
        $invitation
            ->setRevokedAt($this->now())
            ->setTokenHash(null);

        $this->getEntityManager()->flush();
    }

    public function findByToken(string $token): ?CustomerUserInvitation
    {
        if (strlen($token) !== 64 || !ctype_xdigit($token)) {
            return null;
        }

        return $this->getEntityManager()->getRepository(CustomerUserInvitation::class)->findOneBy([
            'tokenHash' => $this->hashToken($token)
        ]);
    }

    public function isUsable(CustomerUserInvitation $invitation): bool
    {
        return $invitation->isPending() && !$invitation->isExpired() && $invitation->getTokenHash() !== null;
    }

    public function createCustomerUser(CustomerUserInvitation $invitation): CustomerUser
    {
        $this->assertInvitationCanBeAccepted($invitation);

        $customerUser = new CustomerUser();
        $this->fillCustomerUser($customerUser, $invitation);

        return $customerUser;
    }

    public function accept(CustomerUserInvitation $invitation, CustomerUser $customerUser, string $token): CustomerUser
    {
        $manager = $this->getEntityManager();

        return $manager->wrapInTransaction(function () use ($manager, $invitation, $customerUser, $token) {
            /** @var CustomerUserInvitation|null $lockedInvitation */
            $lockedInvitation = $manager->find(
                CustomerUserInvitation::class,
                $invitation->getId(),
                LockMode::PESSIMISTIC_WRITE
            );
            if (
                !$lockedInvitation
                || !$this->isUsable($lockedInvitation)
                || !$lockedInvitation->getTokenHash()
                || !hash_equals($lockedInvitation->getTokenHash(), $this->hashToken($token))
            ) {
                throw new \LogicException('The customer user invitation is no longer valid.');
            }

            $this->assertInvitationDataIsValid($lockedInvitation);
            $this->fillCustomerUser($customerUser, $lockedInvitation);
            if (!$this->featureChecker->isFeatureEnabled('customer_user_login_password')) {
                $customerUser->setPlainPassword($this->customerUserManager->generatePassword(32));
            }

            $this->customerUserManager->updateWebsiteSettings($customerUser);
            $this->customerUserManager->setAuthStatus($customerUser, CustomerUserManager::STATUS_ACTIVE);
            $this->customerUserManager->updateUser($customerUser, false);

            $lockedInvitation
                ->setAcceptedUser($customerUser)
                ->setAcceptedAt($this->now())
                ->setTokenHash(null);
            $this->setStatus($lockedInvitation, CustomerUserInvitation::STATUS_ACCEPTED);
            $manager->flush();

            return $customerUser;
        });
    }

    private function prepareForSending(CustomerUserInvitation $invitation): string
    {
        $token = $this->tokenGenerator->generateToken();
        $now = $this->now();
        $expiresAt = (clone $now)->modify(sprintf('+%d seconds', $this->ttl));
        $this->setStatus($invitation, CustomerUserInvitation::STATUS_PENDING);
        $invitation
            ->setTokenHash($this->hashToken($token))
            ->setExpiresAt($expiresAt)
            ->setSentAt(null)
            ->setAcceptedAt(null)
            ->setAcceptedUser(null)
            ->setRevokedAt(null);

        return $token;
    }

    private function fillCustomerUser(CustomerUser $customerUser, CustomerUserInvitation $invitation): void
    {
        $customerUser
            ->setEmail($invitation->getEmail())
            ->setOwner($this->getDefaultOwner())
            ->setOrganization($invitation->getOrganization())
            ->setWebsite($invitation->getWebsite())
            ->setCustomer($invitation->getCustomer())
            ->setEnabled(true)
            ->setConfirmed(true)
            ->setUserRoles($invitation->getRoles());
    }

    private function setStatus(CustomerUserInvitation $invitation, string $status): void
    {
        $invitation->setStatus(
            $this->enumOptionsProvider->getEnumOptionByCode(
                CustomerUserInvitation::INTERNAL_STATUS_CODE,
                $status
            )
        );
    }

    private function getInvitationToSend(
        CustomerUserInvitation $invitation,
        EntityManagerInterface $manager
    ): CustomerUserInvitation {
        $caseInsensitiveEmailAddressesEnabled = (bool)$this->configManager
            ->get('oro_customer.case_insensitive_email_addresses_enabled');
        $emailField = $caseInsensitiveEmailAddressesEnabled ? 'emailLowercase' : 'email';
        $storedInvitation = $manager->getRepository(CustomerUserInvitation::class)->findOneBy(
            [
                'organization' => $invitation->getOrganization(),
                $emailField => $caseInsensitiveEmailAddressesEnabled
                    ? $invitation->getEmailLowercase()
                    : $invitation->getEmail()
            ],
            ['id' => 'DESC']
        );
        if (!$storedInvitation instanceof CustomerUserInvitation || !$this->isUsable($storedInvitation)) {
            $manager->persist($invitation);

            return $invitation;
        }
        $this->assertSameScope($storedInvitation, $invitation);

        return $storedInvitation
            ->setEmail($invitation->getEmail())
            ->setInvitedBy($invitation->getInvitedBy())
            ->setRoles($invitation->getRoles());
    }

    private function assertSameScope(
        CustomerUserInvitation $storedInvitation,
        CustomerUserInvitation $invitation
    ): void {
        if (
            $storedInvitation->getCustomer()?->getId() !== $invitation->getCustomer()?->getId()
            || $storedInvitation->getWebsite()?->getId() !== $invitation->getWebsite()?->getId()
        ) {
            throw new \DomainException('This email address already has an invitation in another scope.');
        }
    }

    private function send(CustomerUserInvitation $invitation, string $token, EntityManagerInterface $manager): void
    {
        try {
            $sent = $this->mailer->sendInvitation($invitation, $token);
        } catch (\Throwable $exception) {
            $this->logger->error('Unable to send a customer user invitation.', [
                'exception' => $exception,
                'invitation_id' => $invitation->getId()
            ]);

            throw $exception;
        }

        if (!$sent) {
            $this->logger->error(
                'Unable to send a customer user invitation to "{email}".',
                ['email' => $invitation->getEmail()]
            );

            throw new \RuntimeException('The customer user invitation email could not be sent.');
        }

        $invitation->setSentAt($this->now());
        $manager->flush();
    }

    private function assertInvitationDataIsValid(CustomerUserInvitation $invitation): void
    {
        if (!$invitation->getOrganization() || !$invitation->getWebsite() || !$invitation->getCustomer()) {
            throw new \DomainException('Organization, website and customer are required.');
        }
        if ($this->findExistingCustomerUser($invitation)) {
            throw new \DomainException('A customer user with this email already exists.');
        }

        $this->assertRolesAreValid($invitation);
    }

    private function assertInvitationCanBeAccepted(CustomerUserInvitation $invitation): void
    {
        if (!$this->isUsable($invitation)) {
            throw new \LogicException('The customer user invitation is no longer valid.');
        }

        $this->assertInvitationDataIsValid($invitation);
    }

    private function assertRolesAreValid(CustomerUserInvitation $invitation): void
    {
        if ($invitation->getRoles()->isEmpty()) {
            throw new \DomainException('At least one customer user role is required.');
        }
        $customerId = $invitation->getCustomer()?->getId();
        $organizationId = $invitation->getOrganization()?->getId();
        foreach ($invitation->getRoles() as $role) {
            $roleCustomer = $role->getCustomer();
            if ($roleCustomer && $roleCustomer->getId() !== $customerId) {
                throw new \DomainException('The selected role is not available for this customer.');
            }

            if ($role->getOrganization()?->getId() !== $organizationId) {
                throw new \DomainException('The selected role is not available for this organization.');
            }
        }
    }

    private function findExistingCustomerUser(CustomerUserInvitation $invitation): ?CustomerUser
    {
        /** @var CustomerUserRepository $repository */
        $repository = $this->doctrine
            ->getManagerForClass(CustomerUser::class)
            ->getRepository(CustomerUser::class);

        return $repository->findUserByEmailAndOrganization(
            (string)$invitation->getEmail(),
            $invitation->getOrganization(),
            (bool)$this->configManager->get('oro_customer.case_insensitive_email_addresses_enabled')
        );
    }

    private function getDefaultOwner(): User
    {
        $defaultOwnerId = $this->configManager->get('oro_customer.default_customer_owner');
        $owner = $defaultOwnerId
            ? $this->doctrine->getManagerForClass(User::class)->find(User::class, $defaultOwnerId)
            : null;
        if (!$owner instanceof User) {
            throw new \LogicException('Application Owner is empty.');
        }

        return $owner;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->doctrine->getManagerForClass(CustomerUserInvitation::class);
    }

    private function now(): \DateTime
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
