<?php

namespace Oro\Bundle\CustomerBundle\Acl\Voter;

use Oro\Bundle\CustomerBundle\Acl\Cache\CustomerVisitorAclCache;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Acl\Domain\DomainObjectWrapper;
use Oro\Bundle\SecurityBundle\Acl\Domain\OneShotIsGrantedObserver;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Oro\Bundle\SecurityBundle\Acl\Extension\ObjectIdentityHelper;
use Oro\Bundle\SecurityBundle\Acl\Voter\AclVoterInterface;
use Oro\Bundle\WebsiteBundle\Provider\RequestWebsiteProvider;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Util\ClassUtils;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * AclVoter decorator that uses CustomerVisitorAclCache if current user is Customer visitor.
 */
class CustomerVisitorAclVoterDecorator implements AclVoterInterface
{
    private AclVoterInterface $wrapped;
    private CustomerVisitorAclCache $visitorAclCache;
    private RequestWebsiteProvider $requestWebsiteProvider;

    public function __construct(
        AclVoterInterface $wrapped,
        CustomerVisitorAclCache $visitorAclCache,
        RequestWebsiteProvider $requestWebsiteProvider
    ) {
        $this->wrapped = $wrapped;
        $this->visitorAclCache = $visitorAclCache;
        $this->requestWebsiteProvider = $requestWebsiteProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if ($token instanceof AnonymousCustomerUserToken) {
            $subjectName = $this->getSubjectName($subject);
            if (!is_a($subjectName, CustomerVisitorOwnerAwareInterface::class, true)) {
                $websiteId = $this->requestWebsiteProvider->getWebsite()
                    ? $this->requestWebsiteProvider->getWebsite()->getId()
                    : null;
                if ($websiteId) {
                    if ($this->visitorAclCache->isVoteResultExist($websiteId, $subjectName, $attributes)) {
                        return $this->visitorAclCache->getVoteResult($websiteId, $subjectName, $attributes);
                    }

                    $voteResult = $this->wrapped->vote($token, $subject, $attributes);
                    $this->visitorAclCache->cacheAclResult($websiteId, $subjectName, $attributes, $voteResult);

                    return $voteResult;
                }
            }
        }

        return $this->wrapped->vote($token, $subject, $attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function addOneShotIsGrantedObserver(OneShotIsGrantedObserver $observer): void
    {
        $this->wrapped->addOneShotIsGrantedObserver($observer);
    }

    /**
     * {@inheritDoc}
     */
    public function getSecurityToken(): TokenInterface
    {
        return $this->wrapped->getSecurityToken();
    }

    /**
     * {@inheritDoc}
     */
    public function getAclExtension(): AclExtensionInterface
    {
        return $this->wrapped->getAclExtension();
    }

    /**
     * {@inheritDoc}
     */
    public function getObject()
    {
        return $this->wrapped->getObject();
    }

    /**
     * {@inheritDoc}
     */
    public function setTriggeredMask($mask, $accessLevel): void
    {
        $this->wrapped->setTriggeredMask($mask, $accessLevel);
    }

    private function getSubjectName($subject): ?string
    {
        if ($subject instanceof FieldVote) {
            $subject = $subject->getDomainObject();
        }

        if ($subject instanceof DomainObjectWrapper) {
            $subject = $subject->getObjectIdentity();
        }

        if ($subject instanceof ObjectIdentity) {
            $subject = $subject->getType();
        } elseif (is_object($subject)) {
            $subject = ClassUtils::getRealClass($subject);
        }

        return ObjectIdentityHelper::removeGroupName($subject);
    }
}
