<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Autocomplete;

use Oro\Bundle\CustomerBundle\Form\Autocomplete\FrontendAutocompleteSecurityChecker;
use Oro\Bundle\FormBundle\Autocomplete\AutocompleteSecurityCheckerInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FrontendAutocompleteSecurityCheckerTest extends TestCase
{
    private AutocompleteSecurityCheckerInterface&MockObject $innerChecker;
    private FrontendHelper&MockObject $frontendRequestHelper;
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;
    private FrontendAutocompleteSecurityChecker $securityChecker;

    #[\Override]
    protected function setUp(): void
    {
        $this->innerChecker = $this->createMock(AutocompleteSecurityCheckerInterface::class);
        $this->frontendRequestHelper = $this->createMock(FrontendHelper::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->securityChecker = new FrontendAutocompleteSecurityChecker(
            $this->innerChecker,
            $this->frontendRequestHelper,
            $this->authorizationChecker
        );
        $this->securityChecker->setAutocompleteAclResources(['frontend_handler' => 'frontend_acl_resource']);
    }

    public function testGetAutocompleteAclResourceForFrontendRequest(): void
    {
        $this->frontendRequestHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->innerChecker->expects(self::never())
            ->method('getAutocompleteAclResource');

        self::assertSame(
            'frontend_acl_resource',
            $this->securityChecker->getAutocompleteAclResource('frontend_handler')
        );
    }

    public function testGetAutocompleteAclResourceReturnsNullForUnknownFrontendHandler(): void
    {
        $this->frontendRequestHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        self::assertNull($this->securityChecker->getAutocompleteAclResource('unknown_handler'));
    }

    public function testGetAutocompleteAclResourceDelegatesForBackofficeRequest(): void
    {
        $this->frontendRequestHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);
        $this->innerChecker->expects(self::once())
            ->method('getAutocompleteAclResource')
            ->with('backoffice_handler')
            ->willReturn('backoffice_acl_resource');

        self::assertSame(
            'backoffice_acl_resource',
            $this->securityChecker->getAutocompleteAclResource('backoffice_handler')
        );
    }

    public function testIsAutocompleteGrantedForFrontendHandlerWithoutAclResource(): void
    {
        $this->frontendRequestHelper->expects(self::exactly(2))
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        self::assertTrue($this->securityChecker->isAutocompleteGranted('unknown_handler'));
    }

    public function testIsAutocompleteGrantedForFrontendHandlerWithAclResource(): void
    {
        $this->frontendRequestHelper->expects(self::exactly(2))
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('frontend_acl_resource')
            ->willReturn(false);

        self::assertFalse($this->securityChecker->isAutocompleteGranted('frontend_handler'));
    }

    public function testIsAutocompleteGrantedDelegatesForBackofficeRequest(): void
    {
        $this->frontendRequestHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);
        $this->innerChecker->expects(self::once())
            ->method('isAutocompleteGranted')
            ->with('backoffice_handler')
            ->willReturn(true);
        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        self::assertTrue($this->securityChecker->isAutocompleteGranted('backoffice_handler'));
    }
}
