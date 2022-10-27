<?php

namespace Oro\Bundle\CustomerBundle\Entity\Manager;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Entity\Manager\GridViewManager;
use Oro\Bundle\DataGridBundle\Entity\Manager\GridViewManager as BaseGridViewManager;
use Oro\Bundle\DataGridBundle\Extension\GridViews\ViewInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

class GridViewManagerComposite extends BaseGridViewManager
{
    /** @var GridViewManager */
    protected $defaultGridViewManager;

    /** @var GridViewManager */
    protected $frontendGridViewManager;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    public function __construct(
        GridViewManager $defaultGridViewManager,
        GridViewManager $frontendGridViewManager,
        TokenAccessorInterface $tokenAccessor
    ) {
        $this->defaultGridViewManager = $defaultGridViewManager;
        $this->frontendGridViewManager = $frontendGridViewManager;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultGridView(AbstractUser $user, ViewInterface $gridView, $default = true)
    {
        $this->isFrontend()
            ? $this->frontendGridViewManager->setDefaultGridView($user, $gridView, $default)
            : $this->defaultGridViewManager->setDefaultGridView($user, $gridView, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getSystemViews($gridName)
    {
        return $this->isFrontend()
            ? $this->frontendGridViewManager->getSystemViews($gridName)
            : $this->defaultGridViewManager->getSystemViews($gridName);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllGridViews(AbstractUser $user = null, $gridName = null)
    {
        return $this->isFrontend()
            ? $this->frontendGridViewManager->getAllGridViews($user, $gridName)
            : $this->defaultGridViewManager->getAllGridViews($user, $gridName);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultView(AbstractUser $user, $gridName)
    {
        return $this->isFrontend()
            ? $this->frontendGridViewManager->getDefaultView($user, $gridName)
            : $this->defaultGridViewManager->getDefaultView($user, $gridName);
    }

    /**
     * {@inheritdoc}
     */
    public function getView($id, $default, $gridName)
    {
        return $this->isFrontend()
            ? $this->frontendGridViewManager->getView($id, $default, $gridName)
            : $this->defaultGridViewManager->getView($id, $default, $gridName);
    }

    /**
     * @return bool
     */
    protected function isFrontend()
    {
        return $this->tokenAccessor->getUser() instanceof CustomerUser;
    }
}
