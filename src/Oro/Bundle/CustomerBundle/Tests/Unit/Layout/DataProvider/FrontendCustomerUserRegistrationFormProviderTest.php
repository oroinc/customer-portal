<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRegistrationType;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserRegistrationFormProvider;
use Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub\WebsiteStub;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontendCustomerUserRegistrationFormProviderTest extends TestCase
{
    use EntityTrait;

    private FrontendCustomerUserRegistrationFormProvider $dataProvider;
    private FormFactoryInterface&MockObject $formFactory;
    private EntityManagerInterface&MockObject $em;
    private ConfigManager&MockObject $configManager;
    private WebsiteManager&MockObject $websiteManager;
    private UrlGeneratorInterface&MockObject $router;

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);

        $this->configManager = $this->createMock(ConfigManager::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->em = $this->createMock(EntityManagerInterface::class);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->with(User::class)
            ->willReturn($this->em);

        $this->router = $this->createMock(UrlGeneratorInterface::class);

        $this->dataProvider = new FrontendCustomerUserRegistrationFormProvider(
            $this->formFactory,
            $this->configManager,
            $this->websiteManager,
            $doctrine,
            $this->router
        );
    }

    public function testGetRegisterFormView(): void
    {
        $action = 'form_action';

        $defaultRole = new CustomerUserRole('');

        $organization = $this->getEntity(Organization::class);
        $website = $this->getEntity(WebsiteStub::class, ['organization' => $organization]);
        $owner = $this->getEntity(User::class);

        $formView = $this->createMock(FormView::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $this->prepare(1, $website, $defaultRole, $form, $action, $owner);

        $actual = $this->dataProvider->getRegisterFormView();

        $this->assertInstanceOf(FormView::class, $actual);
    }

    public function testGetRegisterFormViewOwnerEmpty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Application Owner is empty');

        $this->prepare(null, false);

        $this->dataProvider->getRegisterFormView();
    }

    public function testGetRegisterFormViewWebsiteEmpty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Website is empty');

        $this->prepare(1);

        $this->dataProvider->getRegisterFormView();
    }

    public function testGetRegisterFormViewOrganizationEmpty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Website organization is empty');

        $this->prepare(1, $this->getEntity(WebsiteStub::class));

        $this->dataProvider->getRegisterFormView();
    }

    public function testGetRegisterFormViewEmptyRole(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Role "ROLE_USER" was not found');

        $organization = $this->getEntity(Organization::class);
        $website = $this->getEntity(WebsiteStub::class, ['organization' => $organization]);

        $this->prepare(1, $website, false);

        $this->dataProvider->getRegisterFormView();
    }

    public function testGetRegisterForm(): void
    {
        $action = 'form_action';

        $defaultRole = new CustomerUserRole('');

        $organization = $this->getEntity(Organization::class);
        $website = $this->getEntity(WebsiteStub::class, ['organization' => $organization]);
        $owner = $this->getEntity(User::class);

        $form = $this->createMock(FormInterface::class);

        $this->prepare(1, $website, $defaultRole, $form, $action, $owner);

        $actual = $this->dataProvider->getRegisterForm();

        $this->assertInstanceOf(FormInterface::class, $actual);
    }

    public function testGetRegisterFormOwnerEmpty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Application Owner is empty');

        $this->prepare(false, false);

        $this->dataProvider->getRegisterForm();
    }

    public function testGetRegisterFormWebsiteEmpty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Website is empty');

        $this->prepare(1);

        $this->dataProvider->getRegisterForm();
    }

    public function testGetRegisterFormOrganizationEmpty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Website organization is empty');

        $this->prepare(1, $this->getEntity(WebsiteStub::class));

        $this->dataProvider->getRegisterForm();
    }

    public function testGetRegisterFormEmptyRole(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Role "ROLE_USER" was not found');

        $organization = $this->getEntity(Organization::class);
        $website = $this->getEntity(WebsiteStub::class, ['organization' => $organization]);

        $this->prepare(1, $website, false);

        $this->dataProvider->getRegisterForm();
    }

    private function prepare(
        int|false|null $defaultOwnerId,
        Website|false|null $website = null,
        CustomerUserRole|false|null $defaultRole = null,
        ?FormInterface $form = null,
        ?string $routerAction = null,
        ?User $owner = null
    ): void {
        $this->configureDefaultOwner($defaultOwnerId);
        $this->configureCurrentWebsite($website);
        if ($defaultRole) {
            $website->setDefaultRole($defaultRole);
        }
        $this->configureCreateForm($form);
        $this->configureRouterGenerator($routerAction);
        $this->configureUserRepoFind($owner, $defaultOwnerId);
    }

    private function configureDefaultOwner(?int $ownerId): void
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.default_customer_owner')
            ->willReturn($ownerId);
    }

    private function configureCurrentWebsite(Website|false|null $website = null): void
    {
        if (false !== $website) {
            $this->websiteManager->expects($this->once())
                ->method('getCurrentWebsite')
                ->willReturn($website);
        } else {
            $this->websiteManager->expects($this->never())
                ->method('getCurrentWebsite');
        }
    }

    private function configureUserRepoFind(?User $owner = null, ?int $ownerId = null): void
    {
        if (null === $owner) {
            $this->em->expects($this->never())
                ->method('find');
        } else {
            $this->em->expects($this->once())
                ->method('find')
                ->with(User::class, $ownerId)
                ->willReturn($owner);
        }
    }

    private function configureCreateForm(?FormInterface $formToCreate = null): void
    {
        if (null === $formToCreate) {
            $this->formFactory->expects($this->never())
                ->method('create');
        } else {
            $this->formFactory->expects($this->once())
                ->method('create')
                ->with(FrontendCustomerUserRegistrationType::class)
                ->willReturn($formToCreate);
        }
    }

    private function configureRouterGenerator(?string $action = null): void
    {
        if (null === $action) {
            $this->router->expects($this->never())
                ->method('generate');
        } else {
            $this->router->expects($this->once())
                ->method('generate')
                ->with(FrontendCustomerUserRegistrationFormProvider::ACCOUNT_USER_REGISTER_ROUTE_NAME, [])
                ->willReturn($action);
        }
    }
}
