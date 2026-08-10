<?php

namespace Oro\Bundle\CustomerBundle\Form\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\AutocompleteSecurityCheckerInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Provides a way to check whether an access to a frontend autocomplete search handler is granted.
 */
class FrontendAutocompleteSecurityChecker implements AutocompleteSecurityCheckerInterface
{
    /** @var string[] [autocomplete search handler name => acl resource, ...] */
    private array $autocompleteAclResources;

    public function __construct(
        private AutocompleteSecurityCheckerInterface $innerChecker,
        private FrontendHelper $frontendRequestHelper,
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function setAutocompleteAclResources(array $autocompleteAclResources): void
    {
        $this->autocompleteAclResources = $autocompleteAclResources;
    }

    #[\Override]
    public function getAutocompleteAclResource(string $name): ?string
    {
        if ($this->frontendRequestHelper->isFrontendRequest()) {
            return $this->autocompleteAclResources[$name] ?? null;
        }

        return $this->innerChecker->getAutocompleteAclResource($name);
    }

    #[\Override]
    public function isAutocompleteGranted(string $name): bool
    {
        if ($this->frontendRequestHelper->isFrontendRequest()) {
            $aclResource = $this->getAutocompleteAclResource($name);
            if (!$aclResource) {
                return true;
            }

            return $this->authorizationChecker->isGranted($aclResource);
        }

        return $this->innerChecker->isAutocompleteGranted($name);
    }
}
