<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCustomerBundle_Entity_CustomerUserSettings;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Represents Customer User settings.
 *
 * @mixin OroCustomerBundle_Entity_CustomerUserSettings
 */
#[ORM\Entity]
#[ORM\Table(name: 'oro_customer_user_settings')]
#[ORM\UniqueConstraint(name: 'unique_cus_user_website', columns: ['customer_user_id', 'website_id'])]
#[Config]
class CustomerUserSettings implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CustomerUser::class, inversedBy: 'settings')]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?CustomerUser $customerUser = null;

    #[ORM\ManyToOne(targetEntity: Website::class)]
    #[ORM\JoinColumn(name: 'website_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?Website $website = null;

    #[ORM\Column(name: 'currency', type: Types::STRING, length: 3, nullable: true)]
    protected ?string $currency = null;

    #[ORM\ManyToOne(targetEntity: Localization::class)]
    #[ORM\JoinColumn(name: 'localization_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?Localization $localization = null;

    #[ORM\Column(name: 'product_filters_sidebar_expanded', type: Types::BOOLEAN, nullable: true)]
    protected ?bool $productFiltersSidebarExpanded = null;

    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CustomerUser
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @param CustomerUser $customerUser
     * @return $this
     */
    public function setCustomerUser(CustomerUser $customerUser)
    {
        $this->customerUser = $customerUser;

        return $this;
    }

    /**
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return Localization
     */
    public function getLocalization()
    {
        return $this->localization;
    }

    /**
     * @param Localization|null $localization
     * @return $this
     */
    public function setLocalization(Localization $localization = null)
    {
        $this->localization = $localization;

        return $this;
    }

    public function isProductFiltersSidebarExpanded(): ?bool
    {
        return $this->productFiltersSidebarExpanded;
    }

    public function setProductFiltersSidebarExpanded(bool $productFiltersSidebarExpanded): self
    {
        $this->productFiltersSidebarExpanded = $productFiltersSidebarExpanded;

        return $this;
    }
}
