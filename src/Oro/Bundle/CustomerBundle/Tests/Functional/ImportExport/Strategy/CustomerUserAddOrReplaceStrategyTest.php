<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\ImportExport\Strategy\CustomerUserAddOrReplaceStrategy;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUsersForImport;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadDuplicatedCustomer;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\Unit\EntityTrait;

class CustomerUserAddOrReplaceStrategyTest extends WebTestCase
{
    use EntityTrait;

    /**
     * @var CustomerUserAddOrReplaceStrategy
     */
    protected $strategy;

    /**
     * @var StepExecutionProxyContext
     */
    protected $context;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    protected function setUp()
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures(
            [
                LoadDuplicatedCustomer::class,
                LoadCustomerUsersForImport::class,
            ]
        );

        $container = static::getContainer();

        $this->strategy = new CustomerUserAddOrReplaceStrategy(
            $container->get('event_dispatcher'),
            $container->get('oro_importexport.strategy.import.helper'),
            $container->get('oro_entity.helper.field_helper'),
            $container->get('oro_importexport.field.database_helper'),
            $container->get('oro_entity.entity_class_name_provider'),
            $container->get('translator'),
            $container->get('oro_importexport.strategy.new_entities_helper'),
            $container->get('oro_entity.doctrine_helper')
        );

        $this->stepExecution = new StepExecution('step', new JobExecution());
        $this->context = new StepExecutionProxyContext($this->stepExecution);
        $this->strategy->setImportExportContext($this->context);
        $this->strategy->setEntityName(
            $container->getParameter('oro_customer.entity.customer_user.class')
        );
    }

    public function testProcessNonDuplicatedCustomer()
    {
        $this->createAdminUserToken();

        $customer = new Customer();
        $customer->setName(LoadDuplicatedCustomer::DEFAULT_ACCOUNT_NAME);

        $customerUser = $this->createCustomerUserEntity();
        $customerUser->setCustomer($customer);

        $this->assertInstanceOf(CustomerUser::class, $this->strategy->process($customerUser));
    }

    public function testProcessDuplicatedCustomer()
    {
        $customer = new Customer();
        $customer->setName(LoadDuplicatedCustomer::DUPLICATED_CUSTOMER_NAME);

        $customerUser = $this->createCustomerUserEntity();
        $customerUser->setCustomer($customer);

        $this->assertNull($this->strategy->process($customerUser));
    }

    public function testEmailWillNotBeDeletedFromExistingEntityWhenIsSetToNullDuringImport()
    {
        $existingCustomerUser = $this->getReference(LoadCustomerUsersForImport::EMAIL);

        $customer = new Customer();
        $customer->setName(LoadDuplicatedCustomer::DEFAULT_ACCOUNT_NAME);

        $customerUser = $this->createCustomerUserEntity();
        $customerUser->setEmail(null);
        $customerUser->setCustomer($customer);
        $this->setValue($customerUser, 'id', $existingCustomerUser->getId());

        /** @var CustomerUser $processedCustomerUser */
        $processedCustomerUser = $this->strategy->process($customerUser);

        $this->assertSame(LoadCustomerUsersForImport::EMAIL, $processedCustomerUser->getEmail());
    }

    /**
     * @param object $entity
     * @param string $property
     * @param mixed $value
     */
    protected function setValue($entity, $property, $value)
    {
        static::getContainer()->get('oro_entity.helper.field_helper')->setObjectValue($entity, $property, $value);
    }

    /**
     * @return CustomerUser
     */
    private function createCustomerUserEntity()
    {
        $customerUser = new CustomerUser();
        $customerUser->setFirstName('Tester')
            ->setLastName('Tester')
            ->setEmail('tester@oro.inc')
            ->setRoles([new CustomerUserRole()]);
        return $customerUser;
    }

    private function createAdminUserToken()
    {
        /** @var EntityManager $em */
        $em = $this->client->getContainer()->get('doctrine')->getManagerForClass('OroUserBundle:User');

        $user = $em->getRepository('OroUserBundle:User')->findOneBy(['email' => self::AUTH_USER]);
        $organization = $em->getRepository('OroOrganizationBundle:Organization')->find(self::AUTH_ORGANIZATION);

        $token = new UsernamePasswordOrganizationToken($user, $user->getUsername(), 'main', $organization);
        $this->client->getContainer()->get('security.token_storage')->setToken($token);
    }
}
