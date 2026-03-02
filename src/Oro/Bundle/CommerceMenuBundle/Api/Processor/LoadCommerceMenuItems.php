<?php

namespace Oro\Bundle\CommerceMenuBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\GetList\GetListContext;
use Oro\Bundle\CommerceMenuBundle\Api\Repository\CommerceMenuItemRepository;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Loads menu data as a flat list.
 */
class LoadCommerceMenuItems implements ProcessorInterface
{
    public function __construct(
        private readonly CommerceMenuItemRepository $menuRepository
    ) {
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var GetListContext $context */

        if ($context->hasResult()) {
            return;
        }

        $context->setResult($this->menuRepository->getMenuItems($context->getCriteria()));
    }
}
