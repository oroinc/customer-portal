<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumOptionRepository;

class LoadInternalRating extends AbstractFixture
{
    private const DATA = [
        '1_of_5' => 'internal_rating.1 of 5',
        '2_of_5' => 'internal_rating.2 of 5'
    ];

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var EnumOptionRepository $enumRepo */
        $enumRepo = $manager->getRepository(EnumOption::class);
        $priority = 1;
        foreach (self::DATA as $id => $name) {
            $internalRating = $enumRepo->createEnumOption(Customer::INTERNAL_RATING_CODE, $id, $name, $priority++);
            $manager->persist($internalRating);
            $this->addReference($name, $internalRating);
        }
        $manager->flush();
    }
}
