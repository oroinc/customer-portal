<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadInternalRating extends AbstractFixture
{
    private const DATA = [
        'internal_rating.1_of_5' => 'internal_rating.1 of 5',
        'internal_rating.2_of_5' => 'internal_rating.2 of 5'
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository(ExtendHelper::buildEnumValueClassName(Customer::INTERNAL_RATING_CODE));
        $priority = 1;
        foreach (self::DATA as $id => $name) {
            $enumValue = $enumRepo->createEnumValue($name, $priority++, false, $id);
            $manager->persist($enumValue);
        }
        $manager->flush();

        /** @var AbstractEnumValue[] $enumData */
        $enumData = $enumRepo->findAll();
        foreach ($enumData as $enumItem) {
            $this->addReference($enumItem->getName(), $enumItem);
        }
    }
}
