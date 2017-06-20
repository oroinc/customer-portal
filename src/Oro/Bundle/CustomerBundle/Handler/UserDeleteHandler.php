<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\SoapBundle\Handler\DeleteHandler;

class UserDeleteHandler extends DeleteHandler
{
    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /**
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermissions($entity, ObjectManager $em)
    {
        $loggedUserId = $this->tokenAccessor->getUserId();
        if ($loggedUserId && $loggedUserId == $entity->getId()) {
            throw new ForbiddenException('self delete');
        }
    }
}
