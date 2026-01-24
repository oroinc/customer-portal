<?php

namespace Oro\Bundle\CustomerBundle\JsTree;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Component\Tree\Handler\AbstractTreeHandler;

/**
 * Handles tree structure operations for customer hierarchies.
 *
 * This handler manages the tree representation of customers with parent-child relationships,
 * providing functionality to retrieve and format customer nodes for tree visualization.
 * It prevents customer moving operations as the customer hierarchy is managed through
 * parent-child relationships rather than tree node repositioning.
 */
class CustomerTreeHandler extends AbstractTreeHandler
{
    /**
     * @param Customer $root
     * @param bool $includeRoot
     * @return array
     */
    #[\Override]
    protected function getNodes($root, $includeRoot)
    {
        $entities = [];
        if ($includeRoot) {
            $entities[] = $root;
        }
        return array_merge($entities, $this->buildTreeRecursive($root));
    }

    /**
     * @param Customer $entity
     * @return array
     */
    #[\Override]
    protected function formatEntity($entity)
    {
        return [
            'id'     => $entity->getId(),
            'parent' => $entity->getParent() ? $entity->getParent()->getId() : null,
            'text'   => $entity->getName(),
            'state'  => [
                'opened' => !$entity->getChildren()->isEmpty()
            ]
        ];
    }

    /**
     * @param Customer $entity
     * @return array
     */
    protected function buildTreeRecursive(Customer $entity)
    {
        $entities = [];

        $children = $entity->getChildren();

        foreach ($children->toArray() as $child) {
            $entities[] = $child;

            $entities = array_merge($entities, $this->buildTreeRecursive($child));
        }

        return $entities;
    }

    #[\Override]
    protected function moveProcessing($entityId, $parentId, $position)
    {
        throw new \LogicException('Customer moving is not supported');
    }
}
