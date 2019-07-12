<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Behat;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\TestFrameworkBundle\Behat\Isolation\ReferenceRepositoryInitializerInterface;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\Collection;
use Oro\Bundle\TranslationBundle\Entity\TranslationKey;

/**
 * Create a reference from "translation key"
 */
class ReferenceRepositoryInitializer implements ReferenceRepositoryInitializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(Registry $doctrine, Collection $referenceRepository)
    {
        $translationKeyRepository = $doctrine->getRepository(TranslationKey::class);

        $translationKey = $translationKeyRepository
            ->findOneBy(['key' => 'oro.commercemenu.frontend.navigation.items.information.label']);

        $referenceRepository->set('informationTitleTranslationKey', $translationKey);
    }
}
