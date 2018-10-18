<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Strategy;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\ImportExport\Strategy\CustomerAddOrReplaceStrategy;
use Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Strategy\DataFixtures\LoadTestUser;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTrait;

/**
 * Checks the user for the possibility of using it as the owner of the entity
 */
class CustomerAddOrReplaceStrategyTest extends WebTestCase
{
    use EntityTrait;

    /**
     * @var CustomerAddOrReplaceStrategy
     */
    private $strategy;

    /**
     * @var Context
     */
    protected $context;

    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures([LoadTestUser::class]);
        $this->createToken();
        $this->strategy = $this->getContainer()->get('oro_customer.importexport.strategy.customer.add_or_replace');

        /**
         * Strategy must know about entity name
         */
        $this->strategy->setEntityName(Customer::class);

        $this->context = new Context([]);
        $this->context->setValue('itemData', []);
        $this->strategy->setImportExportContext($this->context);
    }

    public function testWithValidOwner()
    {
        $customer = $this->createCustomer($this->getReference('user_with_main_organization_access'));

        $processedCustomer = $this->strategy->process($customer);
        $this->assertEquals([], $this->context->getErrors());
        $this->assertNotNull($processedCustomer);
        $this->assertInstanceOf(Customer::class, $processedCustomer);
    }

    public function testWithInvalidOwner()
    {
        $customer = $this->createCustomer($this->getReference('user_without_main_organization_access'));

        $processedCustomer = $this->strategy->process($customer);
        $this->assertEquals(['Error in row #. You have no access to set given owner'], $this->context->getErrors());
        $this->assertTrue(null === $processedCustomer);
    }

    /**
     * The strategy use user data to verify access to the entity modification
     */
    private function createToken()
    {
        $token = new UsernamePasswordOrganizationToken(
            $this->getReference('user_with_main_organization_access'),
            self::AUTH_PW,
            'main',
            $this->getReference('organization')
        );
        $this->client->getContainer()->get('security.token_storage')->setToken($token);
    }

    /**
     * @param User $owner
     *
     * @return Customer|object
     */
    private function createCustomer(User $owner)
    {
        return $this->getEntity(Customer::class, ['name' => 'customer', 'owner' => $owner]);
    }
}
