<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Acl\Voter;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;

/**
 * Disables content block removal if it was used in System configuration.
 */
class ContentBlockVoter extends AbstractEntityVoter
{
    private ManagerRegistry $registry;

    public function __construct(DoctrineHelper $doctrineHelper, ManagerRegistry $registry)
    {
        parent::__construct($doctrineHelper);
        $this->registry = $registry;
    }

    protected $supportedAttributes = [
        BasicPermission::DELETE,
    ];

    protected function getPermissionForAttribute($class, $identifier, $attribute): int
    {
        if (empty($identifier) || BasicPermission::DELETE !== $attribute) {
            return self::ACCESS_ABSTAIN;
        }

        $value = $this->registry
            ->getManagerForClass(ConfigValue::class)
            ->getRepository(ConfigValue::class)
            ->findOneBy([
                'name' => Configuration::PROMOTIONAL_CONTENT,
                'section' => Configuration::ROOT_NODE,
                'textValue' => $identifier,
            ]);

        return $value ? self::ACCESS_DENIED : self::ACCESS_ABSTAIN;
    }
}
