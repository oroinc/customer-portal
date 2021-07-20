<?php

namespace Oro\Bundle\StyleBookBundle\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checks if it is allowed to show Style Book
 */
class AccessHelper
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return bool
     */
    public function isAllowStyleBook()
    {
        return (bool)$this->container->getParameter('kernel.debug');
    }
}
