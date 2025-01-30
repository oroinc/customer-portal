<?php

namespace Oro\Bundle\FrontendImportExportBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\Ownership\AuditableFrontendCustomerUserAwareTrait;
use Oro\Bundle\EntityBundle\EntityProperty\CreatedAtAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\CreatedAtAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\FrontendImportExportBundle\Entity\Repository\FrontendImportExportResultRepository;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\OrganizationAwareTrait;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Entity stores information about import/export operations
 */
#[ORM\Entity(repositoryClass: FrontendImportExportResultRepository::class)]
#[ORM\Table(name: 'oro_frontend_import_export_result')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    defaultValues: [
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id',
            'frontend_owner_type' => 'FRONTEND_USER',
            'frontend_owner_field_name' => 'customerUser',
            'frontend_owner_column_name' => 'customer_user_id',
            'frontend_customer_field_name' => 'customer',
            'frontend_customer_column_name' => 'customer_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => 'commerce', 'category' => 'importexport']
    ]
)]
class FrontendImportExportResult implements CreatedAtAwareInterface, CustomerOwnerAwareInterface
{
    use AuditableFrontendCustomerUserAwareTrait;
    use CreatedAtAwareTrait;
    use OrganizationAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?User $owner = null;

    #[ORM\Column(name: 'filename', type: Types::STRING, length: 255, unique: true, nullable: true)]
    protected ?string $filename = null;

    #[ORM\Column(name: 'job_id', type: Types::INTEGER, unique: true, nullable: false)]
    protected ?int $jobId = null;

    #[ORM\Column(name: 'type', type: Types::STRING, length: 255, unique: false, nullable: false)]
    protected ?string $type = null;

    #[ORM\Column(name: 'entity', type: Types::STRING, length: 255, unique: false, nullable: false)]
    protected ?string $entity = null;

    /**
     * @var array
     */
    #[ORM\Column(name: 'options', type: Types::ARRAY, nullable: true)]
    protected $options = [];

    #[ORM\Column(name: 'expired', type: Types::BOOLEAN, options: ['default' => false])]
    protected ?bool $expired = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): FrontendImportExportResult
    {
        $this->owner = $owner;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     *
     * @return self
     */
    public function setFilename(?string $filename = null): FrontendImportExportResult
    {
        $this->filename = $filename;

        return $this;
    }

    public function getJobId(): ?int
    {
        return $this->jobId;
    }

    public function setJobId(int $jobId): FrontendImportExportResult
    {
        $this->jobId = $jobId;

        return $this;
    }

    public function isExpired(): ?bool
    {
        return $this->expired;
    }

    public function setExpired(bool $expired): FrontendImportExportResult
    {
        $this->expired = $expired;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): FrontendImportExportResult
    {
        $this->type = $type;

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): FrontendImportExportResult
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options ?: [];
    }

    /**
     * @param array $options
     *
     * @return self
     */
    public function setOptions($options): FrontendImportExportResult
    {
        $this->options = $options;

        return $this;
    }
}
