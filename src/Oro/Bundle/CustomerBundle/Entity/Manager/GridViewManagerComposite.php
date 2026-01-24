<?php

namespace Oro\Bundle\CustomerBundle\Entity\Manager;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Entity\Manager\GridViewManager;
use Oro\Bundle\DataGridBundle\Entity\Manager\GridViewManager as BaseGridViewManager;
use Oro\Bundle\DataGridBundle\Extension\GridViews\ViewInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
 * Composite grid view manager that delegates to backend or frontend managers based on the current user type.
 *
 * This manager routes grid view operations to the appropriate manager (backend or frontend) depending on
 * whether the current authenticated user is a backend user or a customer user, ensuring context-appropriate
 * grid view management.
 */
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

    #[\Override]
    public function setDefaultGridView(AbstractUser $user, ViewInterface $gridView, $default = true)
    {
        $this->isFrontend()
            ? $this->frontendGridViewManager->setDefaultGridView($user, $gridView, $default)
            : $this->defaultGridViewManager->setDefaultGridView($user, $gridView, $default);
    }

    #[\Override]
    public function getSystemViews($gridName)
    {
        return $this->isFrontend()
            ? $this->frontendGridViewManager->getSystemViews($gridName)
            : $this->defaultGridViewManager->getSystemViews($gridName);
    }

    #[\Override]
    public function getAllGridViews(?AbstractUser $user = null, $gridName = null)
    {
        return $this->isFrontend()
            ? $this->frontendGridViewManager->getAllGridViews($user, $gridName)
            : $this->defaultGridViewManager->getAllGridViews($user, $gridName);
    }

    #[\Override]
    public function getDefaultView(AbstractUser $user, $gridName)
    {
        return $this->isFrontend()
            ? $this->frontendGridViewManager->getDefaultView($user, $gridName)
            : $this->defaultGridViewManager->getDefaultView($user, $gridName);
    }

    #[\Override]
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
