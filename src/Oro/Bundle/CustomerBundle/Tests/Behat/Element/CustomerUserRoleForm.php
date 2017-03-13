<?php

namespace Oro\Bundle\CustomerBundle\Tests\Behat\Element;

use Behat\Mink\Element\NodeElement;
use Oro\Bundle\UserBundle\Tests\Behat\Element\UserRoleForm;

class CustomerUserRoleForm extends UserRoleForm
{
    /**
     * {@inheritdoc}
     */
    protected function getEntityRow($entity)
    {
        $entityTrs = $this->findAll('css', 'div[id*="-customer-user-role-permission-grid"] table.grid tbody tr');
        self::assertNotCount(0, $entityTrs, 'Can\'t find table with permissions on the page');

        /** @var NodeElement $entityTr */
        foreach ($entityTrs as $entityTr) {
            if (false !== strpos($entityTr->find('css', 'td div.entity-name')->getText(), $entity)) {
                return $entityTr;
            }
        }

        self::fail(sprintf('There is no "%s" entity row', $entity));
    }
}
