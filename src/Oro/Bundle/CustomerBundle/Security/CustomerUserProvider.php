<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityMaskBuilder;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Security\Acl\Domain\Entry;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\PermissionGrantingStrategy;
use Symfony\Component\Security\Acl\Util\ClassUtils;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * This provider is responsible for providing current customer user and checking its access.
 */
class CustomerUserProvider
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var AclManager */
    protected $aclManager;

    /** @var string */
    protected $customerUserClass;

    /** @var EntityMaskBuilder[] */
    protected $maskBuilders = [];

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenAccessorInterface $tokenAccessor,
        AclManager $aclManager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenAccessor = $tokenAccessor;
        $this->aclManager = $aclManager;
    }

    /**
     * @param string $class
     */
    public function setCustomerUserClass($class)
    {
        $this->customerUserClass = $class;
    }

    /**
     * @param bool $allowGuest
     * @return CustomerUser|null
     */
    public function getLoggedUser($allowGuest = false)
    {
        $token = $this->tokenAccessor->getToken();
        if (!$token instanceof TokenInterface) {
            return null;
        }

        $user = $token->getUser();
        if ($user instanceof CustomerUser) {
            return $user;
        }

        if ($allowGuest && $token instanceof AnonymousCustomerUserToken) {
            $visitor = $token->getVisitor();
            if ($visitor) {
                return $visitor->getCustomerUser();
            }
        }

        return null;
    }

    /**
     * @param string $class
     * @return boolean
     */
    public function isGrantedViewBasic($class)
    {
        return $this->isGranted($class, 'VIEW', 'BASIC');
    }

    /**
     * @param string $class
     * @return boolean
     */
    public function isGrantedViewLocal($class)
    {
        return $this->isGranted($class, 'VIEW', 'LOCAL');
    }

    /**
     * @param string $class
     * @return boolean
     */
    public function isGrantedViewDeep($class)
    {
        return $this->isGranted($class, 'VIEW', 'DEEP');
    }

    /**
     * @param string $class
     * @return boolean
     */
    public function isGrantedViewSystem($class)
    {
        return $this->isGranted($class, 'VIEW', 'SYSTEM');
    }

    /**
     * @param string $class
     * @return boolean
     */
    public function isGrantedEditBasic($class)
    {
        return $this->isGranted($class, 'EDIT', 'BASIC');
    }

    /**
     * @param string $class
     * @return boolean
     */
    public function isGrantedEditLocal($class)
    {
        return $this->isGranted($class, 'EDIT', 'LOCAL');
    }

    /**
     * @param string $class
     * @return boolean
     */
    public function isGrantedViewCustomerUser($class)
    {
        $descriptor = sprintf('entity:%s@%s', CustomerUser::SECURITY_GROUP, $this->customerUserClass);
        if (!$this->authorizationChecker->isGranted(BasicPermission::VIEW, $descriptor)) {
            return false;
        }

        if ($this->isGrantedViewLocal($class) ||
            $this->isGrantedViewDeep($class) ||
            $this->isGrantedViewSystem($class)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $class
     * @param string $permission
     * @param string $level
     * @return bool
     */
    private function isGranted($class, $permission, $level)
    {
        return $this->isGrantedEntityMask(
            $class,
            $this->getMaskBuilderForPermission($permission)->getMaskForPermission($permission . '_' . $level)
        );
    }

    /**
     * @param string $class
     * @param int $mask
     * @return boolean
     */
    protected function isGrantedEntityMask($class, $mask)
    {
        if (!$class) {
            return false;
        }

        $descriptor = sprintf('entity:%s', ClassUtils::getRealClass($class));
        $oid = $this->aclManager->getOid($descriptor);

        return $this->isGrantedOidMask($oid, $class, $mask);
    }

    /**
     * @param ObjectIdentity $oid
     * @param string $class
     * @param int $requiredMask
     * @return bool
     *
     * @see \Oro\Bundle\SecurityBundle\Acl\Domain\PermissionGrantingStrategy::isAceApplicable
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function isGrantedOidMask(ObjectIdentity $oid, $class, $requiredMask)
    {
        if (null === ($loggedUser = $this->getLoggedUser())) {
            return false;
        }

        $extension = $this->aclManager->getExtensionSelector()->select($oid);
        foreach ($loggedUser->getUserRoles() as $role) {
            $sid = $this->aclManager->getSid($role);

            $aces = array_filter(
                $this->aclManager->getAces($sid, $oid),
                function (Entry $ace) use ($extension, $requiredMask) {
                    if ($extension->getServiceBits($requiredMask) !== $extension->getServiceBits($ace->getMask())) {
                        return false;
                    }

                    if ($ace->getAcl()->getObjectIdentity()->getIdentifier() !== $extension->getExtensionKey()) {
                        return false;
                    }

                    return true;
                }
            );

            if (!$aces && $oid->getType() !== ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
                $rootOid = $this->aclManager->getRootOid($oid);

                return $this->isGrantedOidMask(
                    $rootOid,
                    $class,
                    $this->getMaskBuilderForMask($requiredMask)->getMaskForGroup('DEEP')
                );
            }

            foreach ($aces as $ace) {
                $aceMask = $ace->getMask();
                if ($oid->getType() === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
                    $aceMask = $extension->adaptRootMask($aceMask, new $class);
                }

                $requiredMask = $extension->removeServiceBits($requiredMask);
                $aceMask = $extension->removeServiceBits($aceMask);
                $strategy = $ace->getStrategy();
                if (PermissionGrantingStrategy::ALL === $strategy) {
                    return $requiredMask === ($aceMask & $requiredMask);
                } elseif (PermissionGrantingStrategy::ANY === $strategy) {
                    return 0 !== ($aceMask & $requiredMask);
                } elseif (PermissionGrantingStrategy::EQUAL === $strategy) {
                    return $requiredMask === $aceMask;
                }
            }
        }

        return false;
    }

    /**
     * @return EntityAclExtension
     */
    protected function getEntityAclExtension()
    {
        return $this->aclManager->getExtensionSelector()->select('entity:(root)');
    }

    /**
     * @param string $permission
     * @return EntityMaskBuilder
     */
    protected function getMaskBuilderForPermission($permission)
    {
        if (!array_key_exists($permission, $this->maskBuilders)) {
            $this->maskBuilders[$permission] = $this->getEntityAclExtension()->getMaskBuilder($permission);
        }

        return $this->maskBuilders[$permission];
    }

    /**
     * @param int $requiredMask
     * @return EntityMaskBuilder
     */
    protected function getMaskBuilderForMask($requiredMask)
    {
        $extension = $this->getEntityAclExtension();

        $serviceBits = $extension->getServiceBits($requiredMask);

        /** @var EntityMaskBuilder[] $maskBuilders */
        $maskBuilders = $extension->getAllMaskBuilders();

        foreach ($maskBuilders as $maskBuilder) {
            if ($serviceBits === $maskBuilder->getIdentity()) {
                return $maskBuilder;
            }
        }

        throw new \RuntimeException('MaskBuilder could not found.');
    }
}
