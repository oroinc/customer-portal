<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Behat;

use Doctrine\Persistence\ManagerRegistry;
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
    public function init(ManagerRegistry $doctrine, Collection $referenceRepository): void
    {
        $translationKey = $doctrine->getRepository(TranslationKey::class)
            ->findOneBy(['key' => 'oro.commercemenu.frontend.navigation.items.information.label']);
        $referenceRepository->set('informationTitleTranslationKey', $translationKey);
    }
}
