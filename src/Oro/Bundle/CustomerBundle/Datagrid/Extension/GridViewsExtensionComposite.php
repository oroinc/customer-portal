<?php

namespace Oro\Bundle\CustomerBundle\Datagrid\Extension;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Extension\GridViews\GridViewsExtension as BaseGridViewsExtension;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Decorate backend GridViewsExtension.
 * Adds grid views functionality to StoreFront datagrids.
 */
class GridViewsExtensionComposite extends BaseGridViewsExtension
{
    /** @var BaseGridViewsExtension */
    protected $defaultGridViewsExtension;

    /** @var BaseGridViewsExtension */
    protected $frontendGridViewsExtension;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    public function __construct(
        BaseGridViewsExtension $defaultGridViewsExtension,
        BaseGridViewsExtension $frontendGridViewsExtension,
        TokenAccessorInterface $tokenAccessor
    ) {
        $this->defaultGridViewsExtension = $defaultGridViewsExtension;
        $this->frontendGridViewsExtension = $frontendGridViewsExtension;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return $this->isFrontend()
            ? $this->frontendGridViewsExtension->isApplicable($config)
            : $this->defaultGridViewsExtension->isApplicable($config);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->isFrontend()
            ? $this->frontendGridViewsExtension->getPriority()
            : $this->defaultGridViewsExtension->getPriority();
    }

    /**
     * {@inheritdoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data)
    {
        $this->isFrontend()
            ? $this->frontendGridViewsExtension->visitMetadata($config, $data)
            : $this->defaultGridViewsExtension->visitMetadata($config, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(ParameterBag $parameters)
    {
        $this->isFrontend()
            ? $this->frontendGridViewsExtension->setParameters($parameters)
            : $this->defaultGridViewsExtension->setParameters($parameters);
    }

    /**
     * @return bool
     */
    protected function isFrontend()
    {
        return $this->tokenAccessor->getUser() instanceof CustomerUserInterface;
    }
}
