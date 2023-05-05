<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Environment\Api\Processor;

use Oro\Bundle\ApiBundle\Exception\ActionNotAllowedException;
use Oro\Bundle\ApiBundle\Exception\ResourceNotAccessibleException;
use Oro\Bundle\ApiBundle\Processor\SingleItemContext;
use Oro\Bundle\ApiBundle\Tests\Functional\Environment\Model\TestUnaccessibleModel;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This processor is used to test that 4xx responses for storefront visitors are correct.
 * @see \Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi\NotAccessibleResourceForVisitorTest
 */
class HandleSpecialIdentifiers implements ProcessorInterface
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var SingleItemContext $context */

        switch ($context->getId()) {
            case 'access_granted':
                $token = $this->tokenStorage->getToken();
                $model = new TestUnaccessibleModel();
                $model->setId(1);
                $model->setName(sprintf(
                    'Access granted. Token: %s',
                    null === $token ? 'null' : get_class($token)
                ));
                $context->setResult($model);
                break;
            case 'access_denied':
                throw new AccessDeniedException('The access for this entity is denied.');
            case 'not_found':
                throw new NotFoundHttpException('The resource does not exist.');
            case 'not_accessible':
                throw new ResourceNotAccessibleException();
            case 'not_allowed':
                throw new ActionNotAllowedException();
            default:
                throw new \RuntimeException(sprintf('The identifier "%s" is not supported', $context->getId()));
        }
    }
}
