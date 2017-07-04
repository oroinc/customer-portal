<?php

namespace Oro\Bundle\WebsiteBundle\Api\Processor;

use Oro\Bundle\WebsiteBundle\Entity\WebsiteAwareInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class SetDefaultWebsiteProcessor implements ProcessorInterface
{
    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    /**
     * @param WebsiteManager $websiteManager
     */
    public function __construct(WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ContextInterface $context)
    {
        $websiteAwareEntity = $context->getResult();
        if (!$websiteAwareEntity instanceof WebsiteAwareInterface) {
            return;
        }

        $websiteAwareEntity->setWebsite($this->websiteManager->getDefaultWebsite());
    }
}
