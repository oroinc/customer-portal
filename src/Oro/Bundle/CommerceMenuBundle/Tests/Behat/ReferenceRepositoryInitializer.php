<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Behat;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Nelmio\Alice\Instances\Collection as AliceCollection;
use Oro\Bundle\TestFrameworkBundle\Behat\Isolation\ReferenceRepositoryInitializerInterface;
use Oro\Bundle\TranslationBundle\Entity\TranslationKey;

/**
 * Create a reference from "translation key"
 */
class ReferenceRepositoryInitializer implements ReferenceRepositoryInitializerInterface
{
    public function init(Registry $doctrine, AliceCollection $referenceRepository)
    {
        $translationKeyRepository = $doctrine->getRepository(TranslationKey::class);

        $translationKey = $translationKeyRepository
            ->findOneBy(['key' => 'oro.commercemenu.frontend.navigation.items.information.label']);

        $referenceRepository->set('informationTitleTranslationKey', $translationKey);
    }
}
