<?php

namespace Oro\Bundle\WebsiteBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\LocaleBundle\Twig\DateTimeExtension;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\WebsiteBundle\Entity\WebsiteAwareInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Provides a Twig filters to format date and time for specific entity timezone:
 *   - oro_format_datetime_by_entity
 *   - oro_format_date_by_entity
 *   - oro_format_day_by_entity
 *   - oro_format_time_by_entity
 */
class EntityDateTimeExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var DateTimeExtension */
    protected $dateTimeExtension;

    public function __construct(ContainerInterface $container, DateTimeExtension $dateTimeExtension)
    {
        $this->container = $container;
        $this->dateTimeExtension = $dateTimeExtension;
    }

    protected function getConfigManager(): ConfigManager
    {
        return $this->container->get('oro_config.manager');
    }

    protected function getSystemConfigManager(): ConfigManager
    {
        return $this->container->get('oro_config.global');
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'oro_format_datetime_by_entity',
                [$this, 'formatDateTimeByEntity']
            ),
            new TwigFilter(
                'oro_format_date_by_entity',
                [$this, 'formatDateByEntity']
            ),
            new TwigFilter(
                'oro_format_day_by_entity',
                [$this, 'formatDayByEntity']
            ),
            new TwigFilter(
                'oro_format_time_by_entity',
                [$this, 'formatTimeByEntity']
            ),
        ];
    }

    /**
     * @param \DateTime|string|int $date
     * @param object $entity
     * @param array $options
     * @return string
     */
    public function formatDateTimeByEntity($date, $entity, array $options = []): string
    {
        $options['timeZone'] = $this->getEntityTimezone($entity);

        return $this->dateTimeExtension->formatDateTime($date, $options);
    }

    /**
     * @param \DateTime|string|int $date
     * @param object $entity
     * @param array $options
     * @return string
     */
    public function formatDateByEntity($date, $entity, array $options = []): string
    {
        $options['timeZone'] = $this->getEntityTimezone($entity);

        return $this->dateTimeExtension->formatDate($date, $options);
    }

    /**
     * @param \DateTime|string|int $date
     * @param object $entity
     * @param array $options
     * @return string
     */
    public function formatDayByEntity($date, $entity, array $options = []): string
    {
        $options['timeZone'] = $this->getEntityTimezone($entity);

        return $this->dateTimeExtension->formatDay($date, $options);
    }

    /**
     * @param \DateTime|string|int $date
     * @param object $entity
     * @param array $options
     * @return string
     */
    public function formatTimeByEntity($date, $entity, array $options = []): string
    {
        $options['timeZone'] = $this->getEntityTimezone($entity);

        return $this->dateTimeExtension->formatTime($date, $options);
    }

    /**
     * @param object $entity
     * @return mixed
     */
    protected function getEntityTimezone($entity)
    {
        $organization = null;
        if ($entity instanceof WebsiteAwareInterface && $entity->getWebsite()) {
            $organization = $entity->getWebsite()->getOrganization();
        } elseif ($entity instanceof OrganizationAwareInterface && $entity->getOrganization()) {
            $organization = $entity->getOrganization();
        }

        if ($organization) {
            return $this->getConfigManager()->get(
                Configuration::getConfigKeyByName('timezone'),
                false,
                false,
                $organization
            );
        }

        return $this->getSystemConfigManager()->get(Configuration::getConfigKeyByName('timezone'));
    }

    /**
     * {@inheritdoc]
     */
    public static function getSubscribedServices(): array
    {
        return [
            'oro_config.global' => ConfigManager::class,
            'oro_config.manager' => ConfigManager::class,
        ];
    }
}
