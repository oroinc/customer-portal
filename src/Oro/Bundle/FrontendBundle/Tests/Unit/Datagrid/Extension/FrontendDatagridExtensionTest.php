<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Extension;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\FrontendBundle\Datagrid\Extension\FrontendDatagridExtension;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FrontendDatagridExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var FrontendDatagridExtension */
    private $extension;

    public function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->extension = new FrontendDatagridExtension($this->tokenStorage);
        $this->extension->setParameters(new ParameterBag());
    }

    public function testShouldBeIsApplicableIfFrontendOptionIsNotSet()
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', []);

        self::assertTrue($this->extension->isApplicable($datagridConfig));
    }

    public function testShouldBeIsApplicableForBackendGrid()
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', ['options' => ['frontend' => false]]);

        self::assertTrue($this->extension->isApplicable($datagridConfig));
    }

    public function testShouldNotBeIsApplicableForFrontendGrid()
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', ['options' => ['frontend' => true]]);

        self::assertFalse($this->extension->isApplicable($datagridConfig));
    }

    public function testShouldGrantAccessForFrontendGrid()
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', ['options' => ['frontend' => true]]);

        $this->extension->processConfigs($datagridConfig);
    }

    public function testShouldGrantAccessForBackendGridIfSecurityTokenIsNotSet()
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', []);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->extension->processConfigs($datagridConfig);
    }

    /**
     * @expectedException \Oro\Bundle\DataGridBundle\Exception\LogicException
     */
    public function testShouldDenyAccessForBackendGridToAnonymousUser()
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', []);
        $token = $this->createMock(AnonymousCustomerUserToken::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->extension->processConfigs($datagridConfig);
    }

    /**
     * @expectedException \Oro\Bundle\DataGridBundle\Exception\LogicException
     */
    public function testShouldDenyAccessForBackendGridToFrontendUser()
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', []);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(CustomerUser::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->extension->processConfigs($datagridConfig);
    }

    public function testShouldGrantAccessForBackendGridToBackendUser()
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', []);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(User::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->extension->processConfigs($datagridConfig);
    }
}
