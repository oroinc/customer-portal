<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Throws AuthenticationException if response status code is 403 (Forbidden)
 * and current security context is a customer visitor.
 * It allows exception listeners build correct 401 (Unauthorized) response in this case.
 */
class HandleAccessDeniedForVisitors implements ProcessorInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var string[] */
    private $exclusions;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param string[]              $exclusions
     */
    public function __construct(TokenStorageInterface $tokenStorage, array $exclusions)
    {
        $this->tokenStorage = $tokenStorage;
        $this->exclusions = $exclusions;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */

        if (Response::HTTP_FORBIDDEN === $context->getResponseStatusCode()
            && $this->tokenStorage->getToken() instanceof AnonymousCustomerUserToken
            && !in_array($context->getClassName(), $this->exclusions, true)
        ) {
            throw new AuthenticationException('Access Denied');
        }
    }
}
