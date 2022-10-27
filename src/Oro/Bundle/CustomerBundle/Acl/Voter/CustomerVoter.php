<?php

namespace Oro\Bundle\CustomerBundle\Acl\Voter;

use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\EntityBundle\Exception\NotManageableEntityException;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Handles custom attributes for classes which implements CustomerOwnerAwareInterface.
 */
class CustomerVoter extends AbstractEntityVoter implements ServiceSubscriberInterface
{
    const ATTRIBUTE_VIEW = 'ACCOUNT_VIEW';
    const ATTRIBUTE_EDIT = 'ACCOUNT_EDIT';

    /** {@inheritDoc} */
    protected $supportedAttributes = [self::ATTRIBUTE_VIEW, self::ATTRIBUTE_EDIT];

    private AuthorizationCheckerInterface $authorizationChecker;
    private AuthenticationTrustResolverInterface $authenticationTrustResolver;
    private ContainerInterface $container;
    private ?CustomerUserProvider $customerUserProvider = null;

    private mixed $object;
    private ?CustomerUser $user;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        AuthorizationCheckerInterface $authorizationChecker,
        AuthenticationTrustResolverInterface $authenticationTrustResolver,
        ContainerInterface $container
    ) {
        parent::__construct($doctrineHelper);
        $this->authorizationChecker = $authorizationChecker;
        $this->authenticationTrustResolver = $authenticationTrustResolver;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_customer.security.customer_user_provider' => CustomerUserProvider::class,
            'oro_customer.provider.customer_user_relations_provider' => CustomerUserRelationsProvider::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function supportsClass($class)
    {
        return is_a($class, CustomerOwnerAwareInterface::class, true);
    }

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $user = $this->getUser($token);
        if (!$user instanceof CustomerUser) {
            return self::ACCESS_ABSTAIN;
        }

        if (!$object || !\is_object($object)) {
            return self::ACCESS_ABSTAIN;
        }

        // both entity and identity objects are supported
        $class = $this->getEntityClass($object);
        if (!$this->supportsClass($class)) {
            return self::ACCESS_ABSTAIN;
        }

        try {
            $identifier = $this->getEntityIdentifier($object);
        } catch (NotManageableEntityException $e) {
            return self::ACCESS_ABSTAIN;
        }

        $this->object = $object;
        $this->user = $user;
        try {
            return $this->getPermission($class, $identifier, $attributes);
        } finally {
            $this->object = null;
            $this->user = null;
        }
    }

    private function getUser(TokenInterface $token): mixed
    {
        if ($this->authenticationTrustResolver->isAnonymous($token)) {
            $user = new CustomerUser();
            $user->setCustomer($this->getCustomerUserRelationsProvider()->getCustomerIncludingEmpty());

            return $user;
        }

        return $token->getUser();
    }

    /**
     * {@inheritDoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if (null === $identifier) {
            if ($this->isGrantedClassPermission($attribute, $class)) {
                return self::ACCESS_GRANTED;
            }

            return self::ACCESS_DENIED;
        }

        if ($this->isGrantedBasicPermission($attribute, $class) && $this->isSameUser($this->user, $this->object)) {
            return self::ACCESS_GRANTED;
        }

        if ($this->isGrantedLocalPermission($attribute, $class)) {
            if ($this->isSameCustomer($this->user, $this->object) || $this->isSameUser($this->user, $this->object)) {
                return self::ACCESS_GRANTED;
            }
        }

        return self::ACCESS_DENIED;
    }

    private function isSameCustomer(CustomerUser $user, CustomerOwnerAwareInterface $object): bool
    {
        return $object->getCustomer() && $user->getCustomer()->getId() === $object->getCustomer()->getId();
    }

    private function isSameUser(CustomerUser $user, CustomerOwnerAwareInterface $object): bool
    {
        return $object->getCustomerUser() && $user->getId() === $object->getCustomerUser()->getId();
    }

    private function isGrantedClassPermission(string $attribute, string $class): bool
    {
        $isGranted = false;
        if (self::ATTRIBUTE_VIEW === $attribute) {
            $isGranted = $this->authorizationChecker->isGranted(
                BasicPermission::VIEW,
                $this->getDescriptorByClass($class)
            );
        } elseif (self::ATTRIBUTE_EDIT === $attribute) {
            $isGranted = $this->authorizationChecker->isGranted(
                BasicPermission::EDIT,
                $this->getDescriptorByClass($class)
            );
        }

        return $isGranted;
    }

    private function isGrantedBasicPermission(string $attribute, string $class): bool
    {
        $isGranted = false;
        if (self::ATTRIBUTE_VIEW === $attribute) {
            $isGranted = $this->getCustomerUserProvider()->isGrantedViewBasic($class);
        } elseif (self::ATTRIBUTE_EDIT === $attribute) {
            $isGranted = $this->getCustomerUserProvider()->isGrantedEditBasic($class);
        }

        return $isGranted;
    }

    private function isGrantedLocalPermission(string $attribute, string $class): bool
    {
        $isGranted = false;
        if (self::ATTRIBUTE_VIEW === $attribute) {
            $isGranted = $this->getCustomerUserProvider()->isGrantedViewLocal($class);
        } elseif (self::ATTRIBUTE_EDIT === $attribute) {
            $isGranted = $this->getCustomerUserProvider()->isGrantedEditLocal($class);
        }

        return $isGranted;
    }

    private function getDescriptorByClass(string $class): string
    {
        return sprintf('entity:%s@%s', CustomerUser::SECURITY_GROUP, $class);
    }

    private function getCustomerUserProvider(): CustomerUserProvider
    {
        if (null === $this->customerUserProvider) {
            $this->customerUserProvider = $this->container->get('oro_customer.security.customer_user_provider');
        }

        return $this->customerUserProvider;
    }

    private function getCustomerUserRelationsProvider(): CustomerUserRelationsProvider
    {
        return $this->container->get('oro_customer.provider.customer_user_relations_provider');
    }
}
