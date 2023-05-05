<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\EventListener\FileListener as PlatformFileListener;
use Oro\Bundle\AttachmentBundle\Manager\FileManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Listens on File lifecycle events to handle its owner.
 */
class FileListener extends PlatformFileListener
{
    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    public function __construct(FileManager $fileManager, TokenAccessorInterface $tokenAccessor)
    {
        parent::__construct($fileManager, $tokenAccessor);
        $this->tokenAccessor = $tokenAccessor;
    }

    public function prePersist(File $entity, LifecycleEventArgs $args)
    {
        parent::prePersist($entity, $args);

        $file = $entity->getFile();

        if (null !== $file && !$entity->getOwner()) {
            $customerUser = $this->tokenAccessor->getUser();
            if ($customerUser instanceof CustomerUser) {
                $entity->setOwner($customerUser->getOwner());
            }
        }
    }
}
