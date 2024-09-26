<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\Create\CreateContext;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Api\Model\Login;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserApi;
use Oro\Bundle\SecurityBundle\Authentication\Guesser\OrganizationGuesserInterface;
use Oro\Bundle\UserBundle\Exception\BadCredentialsException;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Checks whether the login credentials are valid,
 * and if so, sets API access key of authenticated customer user to the model.
 */
class HandleLogin implements ProcessorInterface
{
    public function __construct(
        private string $firewallName,
        private AuthenticatorInterface $authenticator,
        private UserProviderInterface $userProvider,
        private OrganizationGuesserInterface $organizationGuesser,
        private UserCheckerInterface $userChecker,
        private EventDispatcherInterface $eventDispatcher,
        private ConfigManager $configManager,
        private DoctrineHelper $doctrineHelper,
        private TranslatorInterface $translator
    ) {
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var CreateContext $context */

        $model = $context->getResult();
        if (!$model instanceof Login || $model->getApiKey()) {
            // the request is already handled
            return;
        }

        $authenticatedUser = $this->authenticate($model)->getUser();
        if (!$authenticatedUser instanceof CustomerUser) {
            throw new AccessDeniedException('The login via API is not supported for this user.');
        }

        $apiKey = $this->getApiKey($authenticatedUser);
        if (!$apiKey) {
            if (!$this->isApiKeyGenerationEnabled()) {
                throw new AccessDeniedException('The API access key was not generated for this user.');
            }
            $apiKey = $this->generateApiKey($authenticatedUser);
        }

        $model->setApiKey($apiKey);
    }

    private function authenticate(Login $model): TokenInterface
    {
        $passport = new Passport(
            new UserBadge($model->getEmail(), [$this->userProvider, 'loadUserByIdentifier']),
            new PasswordCredentials($model->getPassword()),
        );
        try {
            $user = $passport->getUser();
            $this->userChecker->checkPreAuth($user);
            $organization = $this->organizationGuesser->guess($user);
            $passport->setAttribute('organization', $organization);
            // check the passport (e.g. password checking)
            $event = new CheckPassportEvent($this->authenticator, $passport);
            $this->eventDispatcher->dispatch($event);
            $this->userChecker->checkPostAuth($user);

            return $this->authenticator->createToken($passport, $this->firewallName);
        } catch (AuthenticationException $exception) {
            $exception = new BadCredentialsException('Bad credentials.', 0, $exception);
            $exception->setMessageKey('Invalid credentials.');

            throw new AccessDeniedException(sprintf(
                'The user authentication fails. Reason: %s',
                $this->translator->trans($exception->getMessageKey(), $exception->getMessageData(), 'security')
            ));
        }
    }

    private function isApiKeyGenerationEnabled(): bool
    {
        return (bool)$this->configManager->get('oro_customer.api_key_generation_enabled');
    }

    private function getApiKey(CustomerUser $user): ?string
    {
        $apiKey = $user->getApiKeys()->first();
        if (!$apiKey) {
            return null;
        }

        return $apiKey->getApiKey();
    }

    private function generateApiKey(CustomerUser $user): string
    {
        $apiKey = new CustomerUserApi();
        $apiKey->setApiKey($apiKey->generateKey());

        $user->addApiKey($apiKey);

        $em = $this->doctrineHelper->getEntityManager($user);
        $em->persist($apiKey);
        $em->flush();

        return $apiKey->getApiKey();
    }
}
