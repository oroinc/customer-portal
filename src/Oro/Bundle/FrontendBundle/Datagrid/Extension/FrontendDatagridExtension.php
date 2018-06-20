<?php

namespace Oro\Bundle\FrontendBundle\Datagrid\Extension;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Exception\LogicException;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * Denies access to the backend grids from the frontend.
 * To make a grid available on the frontend it should be marked by "frontend" option:
 * <code>
 *      options:
 *          frontend: true
 * </code>
 */
class FrontendDatagridExtension extends AbstractExtension
{
    const FRONTEND_OPTION_PATH = '[options][frontend]';

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return parent::isApplicable($config) && !$this->isFrontendGrid($config);
    }

    /**
     * {@inheritdoc}
     */
    public function processConfigs(DatagridConfiguration $config)
    {
        if (!$this->isFrontendGrid($config) && $this->isFrontendRequest()) {
            throw new LogicException(
                sprintf(
                    'The datagrid "%s" is not allowed to be displayed on the frontend.'
                    . ' Check that the "frontend" option for this datagrid is set to true.',
                    $config->getName()
                )
            );
        }
    }

    /**
     * @param DatagridConfiguration $config
     *
     * @return bool
     */
    private function isFrontendGrid(DatagridConfiguration $config)
    {
        return (bool)$config->offsetGetByPath(self::FRONTEND_OPTION_PATH, false);
    }

    /**
     * @return bool
     */
    private function isFrontendRequest()
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return false;
        }

        return
            $token instanceof AnonymousCustomerUserToken
            || $token->getUser() instanceof CustomerUser;
    }
}
