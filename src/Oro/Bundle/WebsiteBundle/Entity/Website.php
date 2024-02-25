<?php

namespace Oro\Bundle\WebsiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroWebsiteBundle_Entity_Website;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableBusinessUnitAwareTrait;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Component\Website\WebsiteInterface;

/**
 * Website entity class.
 *
 * @mixin OroWebsiteBundle_Entity_Website
 */
#[ORM\Entity(repositoryClass: WebsiteRepository::class)]
#[ORM\Table(name: 'oro_website')]
#[ORM\Index(columns: ['created_at'], name: 'idx_oro_website_created_at')]
#[ORM\Index(columns: ['updated_at'], name: 'idx_oro_website_updated_at')]
#[ORM\UniqueConstraint(name: 'uidx_oro_website_name_organization', columns: ['name', 'organization_id'])]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_multiwebsite_index',
    routeView: 'oro_multiwebsite_view',
    routeUpdate: 'oro_multiwebsite_update',
    defaultValues: [
        'entity' => ['icon' => 'fa-briefcase'],
        'ownership' => [
            'owner_type' => 'BUSINESS_UNIT',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'business_unit_owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id'
        ],
        'dataaudit' => ['auditable' => true],
        'security' => ['type' => 'ACL', 'group_name' => '']
    ]
)]
class Website implements OrganizationAwareInterface, WebsiteInterface, ExtendEntityInterface
{
    use DatesAwareTrait;
    use AuditableBusinessUnitAwareTrait;
    use ExtendEntityTrait;

    /**
     * @var Collection<int, Website>
     */
    #[ORM\ManyToMany(targetEntity: Website::class, mappedBy: 'relatedWebsites')]
    protected ?Collection $inversedWebsites = null;

    /**
     * @var Collection<int, Website>
     */
    #[ORM\ManyToMany(targetEntity: Website::class, inversedBy: 'inversedWebsites')]
    #[ORM\JoinTable(name: 'oro_related_website')]
    #[ORM\JoinColumn(name: 'website_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'related_website_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Collection $relatedWebsites = null;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['identity' => true]])]
    protected ?string $name = null;

    #[ORM\Column(name: 'is_default', type: Types::BOOLEAN)]
    protected ?bool $default = false;

    /**
     * Website constructor.
     */
    public function __construct()
    {
        $this->inversedWebsites = new ArrayCollection();
        $this->relatedWebsites = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Website[]
     */
    public function getRelatedWebsites()
    {
        return $this->relatedWebsites;
    }

    /**
     * @param Website $website
     * @return $this
     */
    public function addRelatedWebsite(Website $website)
    {
        if (!$this->relatedWebsites->contains($website)) {
            foreach ($this->relatedWebsites as $relatedWebsite) {
                $website->addRelatedWebsite($relatedWebsite);
            }
        }

        if (!$this->relatedWebsites->contains($website)) {
            $this->relatedWebsites->add($website);
            $website->addRelatedWebsite($this);
        }

        return $this;
    }

    /**
     * @param Website $removedWebsite
     * @return $this
     */
    public function removeRelatedWebsite(Website $removedWebsite)
    {
        if ($this->relatedWebsites->contains($removedWebsite)) {
            foreach ($removedWebsite->relatedWebsites as $website) {
                $website->relatedWebsites->removeElement($removedWebsite);
            }

            $removedWebsite->relatedWebsites->clear();
        }

        return $this;
    }

    /**
     * Pre persist event listener
     */
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler
     */
    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param boolean $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = (bool)$default;

        return $this;
    }

    /**
     * @return Collection|Website[]
     */
    public function getInversedWebsites()
    {
        return $this->inversedWebsites;
    }
}
