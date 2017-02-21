<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Context;

use Behat\Mink\Element\NodeElement;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\PricingBundle\Tests\Behat\Context\FeatureContext as BaseFeatureContext;
use Oro\Bundle\UserBundle\Entity\Role;

/**
 * TODO: get rid of inheritance after BAP-13903 is done
 */
class FeatureContext extends BaseFeatureContext
{
    /**
     * Example: AmandaRCole@example.org customer user has Buyer role
     *
     * @Given /^(?P<username>\S+) customer user has (?P<role>[\w\s]+) role$/
     *
     * @param string $username
     * @param string $role
     */
    public function customerUserHasRole($username, $role)
    {
        $customerUser = $this->getCustomerUser($username);
        self::assertNotNull($customerUser, sprintf('Could not found customer user "%s",', $username));

        $customerUserRole = null;

        /** @var Role $item */
        foreach ($customerUser->getRoles() as $item) {
            if ($role === $item->getLabel()) {
                $customerUserRole = $item;
                break;
            }
        }

        self::assertNotNull(
            $customerUserRole,
            sprintf('Customer user "%s" was found, but without role "%s"', $username, $role)
        );
    }

    /**
     * @Then I should see that :priceListName price list is in :rowNum row on view page
     * @param string $priceListName
     * @param int $rowNum
     */
    public function assertPriceListNameInRow($priceListName, $rowNum)
    {
        --$rowNum;
        $page = $this->getPage();
        $elem = $page->find('named', ['content', $priceListName]);
        self::assertEquals('td', $elem->getTagName());
        $table = $elem->getParent()->getParent();
        self::assertEquals('tbody', $table->getTagName());
        $rows = $table->findAll('css', 'tr');
        self::assertNotEmpty($rows[$rowNum]);
        self::assertContains($priceListName, $rows[$rowNum]->getText());
    }

    /**
     * @param string $username
     * @return CustomerUser
     */
    protected function getCustomerUser($username)
    {
        return $this->getRepository(CustomerUser::class)->findOneBy(['username' => $username]);
    }

    /**
     * @param string $className
     * @return ObjectRepository
     */
    protected function getRepository($className)
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getManagerForClass($className)
            ->getRepository($className);
    }
}
