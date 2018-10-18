<?php

namespace Oro\Bundle\CustomerBundle\Acl\Voter;

use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\EntityBundle\Exception\NotManageableEntityException;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CustomerVoter extends AbstractEntityVoter implements ContainerAwareInterface
{
    const ATTRIBUTE_VIEW = 'ACCOUNT_VIEW';
    const ATTRIBUTE_EDIT = 'ACCOUNT_EDIT';

    /**
     * @var array
     */
    protected $supportedAttributes = [
        self::ATTRIBUTE_VIEW,
        self::ATTRIBUTE_EDIT,
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var CustomerOwnerAwareInterface
     */
    protected $object;

    /**
     * @var CustomerUser
     */
    protected $user;

    /**
     * @var AuthenticationTrustResolverInterface
     */
    private $authenticationTrustResolver;

    /**
     * Constructor.
     *
     * @param DoctrineHelper $doctrineHelper
     * @param AuthenticationTrustResolverInterface $authenticationTrustResolver
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        AuthenticationTrustResolverInterface $authenticationTrustResolver
    ) {
        parent::__construct($doctrineHelper);
        $this->authenticationTrustResolver = $authenticationTrustResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        if (!$this->container) {
            throw new \InvalidArgumentException('ContainerInterface not injected');
        }

        return $this->container;
    }

    /**
     * {@inheritdoc}
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

        $this->object = $object;
        $this->user = $user;

        if (!$object || !is_object($object)) {
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

        return $this->getPermission($class, $identifier, $attributes);
    }

    /**
     * @param TokenInterface $token
     * @return mixed
     */
    protected function getUser(TokenInterface $token)
    {
        $trustResolver = $this->getAuthenticationTrustResolver();
        if ($trustResolver->isAnonymous($token)) {
            $user = new CustomerUser();
            $relationsProvider = $this->getRelationsProvider();
            $user->setCustomer($relationsProvider->getCustomerIncludingEmpty());

            return $user;
        }

        return $token->getUser();
    }

    /**
     * {@inheritdoc}
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

    /**
     * @param CustomerUser $user
     * @param CustomerOwnerAwareInterface $object
     * @return bool
     */
    protected function isSameCustomer(CustomerUser $user, CustomerOwnerAwareInterface $object)
    {
        return $object->getCustomer() && $user->getCustomer()->getId() === $object->getCustomer()->getId();
    }

    /**
     * @param CustomerUser $user
     * @param CustomerOwnerAwareInterface $object
     * @return bool
     */
    protected function isSameUser(CustomerUser $user, CustomerOwnerAwareInterface $object)
    {
        return $object->getCustomerUser() && $user->getId() === $object->getCustomerUser()->getId();
    }

    /**
     * @param string $attribute
     * @param string $class
     * @return bool
     */
    protected function isGrantedClassPermission($attribute, $class)
    {
        $descriptor = $this->getDescriptorByClass($class);

        switch ($attribute) {
            case self::ATTRIBUTE_VIEW:
                $isGranted = $this->getAuthorizationChecker()
                    ->isGranted(BasicPermissionMap::PERMISSION_VIEW, $descriptor);
                break;

            case self::ATTRIBUTE_EDIT:
                $isGranted = $this->getAuthorizationChecker()
                    ->isGranted(BasicPermissionMap::PERMISSION_EDIT, $descriptor);
                break;

            default:
                $isGranted = false;
        }

        return $isGranted;
    }

    /**
     * @param string $attribute
     * @param string $class
     * @return bool
     */
    protected function isGrantedBasicPermission($attribute, $class)
    {
        $securityProvider = $this->getSecurityProvider();

        switch ($attribute) {
            case self::ATTRIBUTE_VIEW:
                $isGranted = $securityProvider->isGrantedViewBasic($class);
                break;

            case self::ATTRIBUTE_EDIT:
                $isGranted = $securityProvider->isGrantedEditBasic($class);
                break;

            default:
                $isGranted = false;
        }

        return $isGranted;
    }

    /**
     * @param string $attribute
     * @param string $class
     * @return bool
     */
    protected function isGrantedLocalPermission($attribute, $class)
    {
        $securityProvider = $this->getSecurityProvider();

        switch ($attribute) {
            case self::ATTRIBUTE_VIEW:
                $isGranted = $securityProvider->isGrantedViewLocal($class);
                break;

            case self::ATTRIBUTE_EDIT:
                $isGranted = $securityProvider->isGrantedEditLocal($class);
                break;

            default:
                $isGranted = false;
        }

        return $isGranted;
    }

    /**
     * @return CustomerUserProvider
     */
    protected function getSecurityProvider()
    {
        return $this->getContainer()->get('oro_customer.security.customer_user_provider');
    }

    /**
     * @return AuthenticationTrustResolverInterface
     */
    protected function getAuthenticationTrustResolver()
    {
        return $this->authenticationTrustResolver;
    }

    /**
     * @return AuthorizationCheckerInterface
     */
    protected function getAuthorizationChecker()
    {
        return $this->getContainer()->get('security.authorization_checker');
    }

    /**
     * @return CustomerUserRelationsProvider
     */
    protected function getRelationsProvider()
    {
        return $this->getContainer()->get('oro_customer.provider.customer_user_relations_provider');
    }

    /**
     * @param string $class
     * @return string
     */
    protected function getDescriptorByClass($class)
    {
        return sprintf('entity:%s@%s', CustomerUser::SECURITY_GROUP, $class);
    }
}
