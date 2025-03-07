<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

/**
 * Loads customer internal rating enum options.
 */
class LoadCustomerInternalRatingDemoData extends AbstractEnumFixture
{
    protected static array $data = [
        '1_of_5' => '1 of 5',
        '2_of_5' => '2 of 5',
        '3_of_5' => '3 of 5',
        '4_of_5' => '4 of 5',
        '5_of_5' => '5 of 5',
    ];

    /**
     * Returns an array of possible enum values, where array key is an id and array value is an English translation
     *
     * @return array
     */
    #[\Override]
    protected function getData(): array
    {
        return self::$data;
    }

    /**
     * Returns array of data keys.
     * @return array
     */
    public static function getDataKeys(): array
    {
        return array_keys(self::$data);
    }

    /**
     * Returns an enum code of an extend entity
     *
     * @return string
     */
    #[\Override]
    protected function getEnumCode(): string
    {
        return Customer::INTERNAL_RATING_CODE;
    }
}
