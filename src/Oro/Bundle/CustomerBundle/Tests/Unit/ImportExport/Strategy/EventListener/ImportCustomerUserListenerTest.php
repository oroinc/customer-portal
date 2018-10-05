<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ImportExport\Strategy\EventListener;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ImportExportBundle\Converter\ConfigurableTableDataConverter;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\ImportExport\Strategy\EventListener\ImportCustomerUserListener;
use Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub\WebsiteStub;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class ImportCustomerUserListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var CustomerUserManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerUserManager;

    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translation;

    /**
     * @var ImportStrategyHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $strategyHelper;

    /**
     * @var WebsiteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteRepository;

    /**
     * @var StrategyEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var ContextInterface
     */
    protected $context;

    protected function setUp()
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        $this->translation = $this->createMock(TranslatorInterface::class);

        $validatorInterface = $this->createMock(ValidatorInterface::class);
        $fieldHelper = $this->createMock(FieldHelper::class);
        $configurableDataConverter = $this->createMock(ConfigurableTableDataConverter::class);
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->strategyHelper = new ImportStrategyHelper(
            $this->registry,
            $validatorInterface,
            $this->translation,
            $fieldHelper,
            $configurableDataConverter,
            $authorizationChecker,
            $tokenAccessor
        );

        $this->websiteRepository = $this->createMock(WebsiteRepository::class);
        $this->event = $this->createMock(StrategyEvent::class);
        $this->context = new Context([]);
    }

    public function testWebsiteAndRoleExist()
    {
        $websiteName = 'WebsiteTest';
        $website = new WebsiteStub();
        $website->setName($websiteName);

        $this->websiteRepository->method('getDefaultWebsite')
            ->willReturn($website);

        $roleName = 'ROLE_FRONTEND_TEST';
        $customerUserRole = new CustomerUserRole($roleName);

        $website->setDefaultRole($customerUserRole);

        $customerUser = new CustomerUser();
        $this->updateEventMock($customerUser);

        $password = 'password';
        $this->updateCustomerManagerMock($password);
        $this->updateTranslationMock($website);
        $this->updateRegistryMock();

        $listener = new ImportCustomerUserListener(
            $this->registry,
            $this->customerUserManager,
            $this->translation,
            $this->strategyHelper
        );

        $listener->onProcessAfter($this->event);

        $this->assertEquals($websiteName, (string) $customerUser->getWebsite());
        $this->assertEquals(1, count($customerUser->getRoles()));
        $this->assertEquals($roleName, $customerUser->getRole($roleName)->getRole());
        $this->assertEquals(0, $this->context->getErrorEntriesCount());
        $this->assertEquals($password, $customerUser->getPassword());
    }

    public function testWebsiteOrWebsiteAndRoleDoesNotExist()
    {
        $customerUser = new CustomerUser();
        $this->updateEventMock($customerUser);

        $password = 'password';
        $this->updateCustomerManagerMock($password);
        $this->updateTranslationMock(null);
        $this->updateRegistryMock();

        $listener = new ImportCustomerUserListener(
            $this->registry,
            $this->customerUserManager,
            $this->translation,
            $this->strategyHelper
        );

        $listener->onProcessAfter($this->event);

        $this->assertNull($customerUser->getWebsite());
        $this->assertEquals(0, count($customerUser->getRoles()));
        $this->assertNull($customerUser->getRole('ROLE_FRONTEND_TEST'));
        $this->assertEquals(2, $this->context->getErrorEntriesCount());
        $this->assertEquals(
            [
                'Error in row #0. Default website doesn\'t exists',
                'Error in row #0. Default role for website WebsiteTest doesn\'t exists'
            ],
            $this->context->getErrors()
        );
        $this->assertEquals($password, $customerUser->getPassword());
    }

    public function testRoleDoesNotExists()
    {
        $websiteName = 'WebsiteTest';
        $website = new WebsiteStub();
        $website->setName($websiteName);

        $this->websiteRepository->method('getDefaultWebsite')
            ->willReturn($website);

        $customerUser = new CustomerUser();
        $this->updateEventMock($customerUser);

        $password = 'password';
        $this->updateCustomerManagerMock($password);
        $this->updateTranslationMock($website);
        $this->updateRegistryMock();

        $listener = new ImportCustomerUserListener(
            $this->registry,
            $this->customerUserManager,
            $this->translation,
            $this->strategyHelper
        );

        $listener->onProcessAfter($this->event);

        $this->assertEquals('WebsiteTest', (string) $customerUser->getWebsite());
        $this->assertEquals(0, count($customerUser->getRoles()));
        $this->assertNull($customerUser->getRole('ROLE_FRONTEND_TEST'));
        $this->assertEquals(1, $this->context->getErrorEntriesCount());
        $this->assertEquals(
            ['Error in row #0. Default role for website WebsiteTest doesn\'t exists'],
            $this->context->getErrors()
        );
        $this->assertEquals($password, $customerUser->getPassword());
    }

    public function testUpdateEntity()
    {
        $websiteBeforeName = 'WebsiteBefore';
        $websiteBefore = new Website();
        $websiteBefore->setName($websiteBeforeName);

        $websiteAfter = new WebsiteStub();
        $websiteAfter->setName('WebsiteAfter');

        $this->websiteRepository->method('getDefaultWebsite')
            ->willReturn($websiteAfter);

        $roleNameBefore = 'ROLE_FRONTEND_TEST_BEFORE';
        $customerUserRoleBefore = new CustomerUserRole('ROLE_FRONTEND_TEST_BEFORE');
        $customerUserRoleAfter = new CustomerUserRole('ROLE_FRONTEND_TEST_AFTER');

        $websiteAfter->setDefaultRole($customerUserRoleAfter);

        $this->updateRegistryMock();

        $customerUser = new CustomerUser();
        $customerUser->setWebsite($websiteBefore);
        $customerUser->addRole($customerUserRoleBefore);
        $passwordBefore = 'password_before';
        $passwordAfter = 'password_after';
        $customerUser->setPassword($passwordBefore);

        $this->updateEventMock($customerUser);
        $this->updateCustomerManagerMock($customerUser, $passwordAfter);

        $listener = new ImportCustomerUserListener(
            $this->registry,
            $this->customerUserManager,
            $this->translation,
            $this->strategyHelper
        );

        $listener->onProcessAfter($this->event);

        $this->assertEquals($websiteBeforeName, (string) $customerUser->getWebsite());
        $this->assertEquals($roleNameBefore, $customerUser->getRole($roleNameBefore)->getRole());
        $this->assertEquals($passwordBefore, $customerUser->getPassword());
    }

    /**
     * @param CustomerUser $customerUser
     */
    protected function updateEventMock(CustomerUser $customerUser)
    {
        $this->event->method('getEntity')
            ->willReturn($customerUser);

        $this->context->setValue('read_offset', 0);

        $this->event->method('getContext')
            ->willReturn($this->context);
    }

    /**
     * @param Website $website
     */
    protected function updateTranslationMock(Website $website = null)
    {
        $this->translation->method('trans')
            ->willReturnMap([
                [
                    'oro.customer.customeruser.import.message.default_website_does_not_exist',
                    [],
                    null,
                    null,
                    'Default website doesn\'t exists'
                ],
                [
                    'oro.customer.customeruser.import.message.default_website_role_does_not_exist',
                    ['%website%' => (string) $website],
                    null,
                    null,
                    'Default role for website WebsiteTest doesn\'t exists',
                ],
                [
                    'oro.importexport.import.error %number%',
                    ['%number%' => 0],
                    null,
                    null,
                    'Error in row #0.'
                ]
            ]);
    }

    /**
     * @param string $password
     */
    protected function updateCustomerManagerMock($password)
    {
        $this->customerUserManager->method('generatePassword')
            ->willReturn($password);

        $this->customerUserManager->method('updatePassword')
            ->willReturnCallback(function ($customerUser) use ($password) {
                $customerUser->setPassword($password);
            });
    }

    protected function updateRegistryMock()
    {
        $this->registry->method('getRepository')
            ->with(Website::class)
            ->willReturn($this->websiteRepository);
    }
}
