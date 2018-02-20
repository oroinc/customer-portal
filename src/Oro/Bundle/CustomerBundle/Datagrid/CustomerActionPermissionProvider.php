<?php

namespace Oro\Bundle\CustomerBundle\Datagrid;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CustomerActionPermissionProvider
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var ManagerRegistry */
    protected $doctrine;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param ManagerRegistry               $doctrine
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, ManagerRegistry $doctrine)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->doctrine = $doctrine;
    }

    /**
     * @param ResultRecordInterface $record
     * @param array $config
     * @return array
     */
    public function getActions(ResultRecordInterface $record, array $config)
    {
        $actions = [];

        foreach ($config as $action => $options) {
            $isGranted = true;

            if (isset($options['acl_permission']) && isset($options['acl_class'])) {
                $object = $this->findObject($options['acl_class'], $record->getValue('id'));

                $isGranted = $this->authorizationChecker->isGranted($options['acl_permission'], $object);
            }

            $actions[$action] = $isGranted;
        }

        return $actions;
    }

    /**
     * @param string $class
     * @param mixed $id
     * @return object
     */
    protected function findObject($class, $id)
    {
        return $this->doctrine->getManagerForClass($class)->getReference($class, $id);
    }
}
