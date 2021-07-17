<?php

namespace Oro\Bundle\CustomerBundle\Model;

use Oro\Bundle\ProductBundle\Model\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Factory for creating MQ message for owner tree cache warm up.
 */
class OwnerTreeMessageFactory
{
    private const CACHE_TTL = 'cache_ttl';

    /**
     * @var OptionsResolver
     */
    private $resolver;

    public function createMessage(int $cacheTtl): array
    {
        return $this->getResolvedData([self::CACHE_TTL => $cacheTtl]);
    }

    /**
     * @param array $data
     * @return object
     * @throws InvalidArgumentException
     */
    public function getCacheTtl($data)
    {
        $data = $this->getResolvedData($data);

        return $data[self::CACHE_TTL];
    }

    private function getOptionsResolver(): OptionsResolver
    {
        if (null === $this->resolver) {
            $resolver = new OptionsResolver();

            $resolver->setRequired([self::CACHE_TTL]);
            $resolver->setAllowedTypes(self::CACHE_TTL, ['int']);
            $resolver->setAllowedValues(self::CACHE_TTL, function ($value) {
                return $value > 0;
            });

            $this->resolver = $resolver;
        }

        return $this->resolver;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getResolvedData(array $data): array
    {
        try {
            return $this->getOptionsResolver()->resolve($data);
        } catch (ExceptionInterface $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }
}
