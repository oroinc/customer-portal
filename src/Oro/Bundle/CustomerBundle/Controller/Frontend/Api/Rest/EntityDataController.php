<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityBundle\Controller\Api\Rest\EntityDataController as BaseEntityDataController;
use Oro\Bundle\EntityBundle\Exception\EntityHasFieldException;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\SecurityBundle\Metadata\EntitySecurityMetadataProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * REST API controller to update storefront entity properties.
 */
class EntityDataController extends BaseEntityDataController
{
    public function __construct(
        private EntityRoutingHelper $entityRoutingHelper,
        private EntitySecurityMetadataProvider $entitySecurityMetadataProvider
    ) {
    }

    #[\Override]
    public function patchAction($className, $id)
    {
        if (!ctype_digit((string) $id)) {
            throw new NotFoundHttpException('The entity identifier must be an integer.');
        }

        try {
            $entity = $this->entityRoutingHelper->getEntity($className, $id);
        } catch (\ReflectionException $e) {
            $entityClass = $this->entityRoutingHelper->resolveEntityClass($className);

            throw new NotFoundHttpException(sprintf('Entity class "%s" is not manageable.', $entityClass), $e);
        }

        if (!$this->entitySecurityMetadataProvider->isProtectedEntity(ClassUtils::getClass($entity))) {
            throw new AccessDeniedException();
        }

        try {
            return parent::patchAction($className, $id);
        } catch (EntityHasFieldException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }
}
