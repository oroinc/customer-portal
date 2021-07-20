<?php

namespace Oro\Bundle\FrontendImportExportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Entity\Ownership\AuditableFrontendCustomerUserAwareTrait;
use Oro\Bundle\EntityBundle\EntityProperty\CreatedAtAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\CreatedAtAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\OrganizationAwareTrait;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Entity stores information about import/export operations
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\FrontendImportExportBundle\Entity\Repository\FrontendImportExportResultRepository")
 * @ORM\Table(name="oro_frontend_import_export_result")
 * @Config(
 *     defaultValues={
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id",
 *              "frontend_owner_type"="FRONTEND_USER",
 *              "frontend_owner_field_name"="customerUser",
 *              "frontend_owner_column_name"="customer_user_id",
 *              "frontend_customer_field_name"="customer",
 *              "frontend_customer_column_name"="customer_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="commerce",
 *              "category"="importexport"
 *          }
 *     }
 * )
 *
 * @ORM\HasLifecycleCallbacks()
 */
class FrontendImportExportResult implements CreatedAtAwareInterface
{
    use AuditableFrontendCustomerUserAwareTrait;
    use CreatedAtAwareTrait;
    use OrganizationAwareTrait;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, name="filename", unique=true, nullable=true)
     */
    protected $filename;

    /**
     * @var integer
     *
     * @ORM\Column(name="job_id", type="integer", unique=true, nullable=false)
     */
    protected $jobId;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, name="type", unique=false, nullable=false)
     */
    protected $type;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, name="entity", unique=false, nullable=false)
     */
    protected $entity;

    /**
     * @var array
     *
     * @ORM\Column(name="options", type="array", nullable=true)
     */
    protected $options = [];

    /**
     * @var boolean
     *
     * @ORM\Column(name="expired", type="boolean", options={"default"=false})
     */
    protected $expired = false;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User|null $owner
     *
     * @return self
     */
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
    public function setFilename(string $filename = null): FrontendImportExportResult
    {
        $this->filename = $filename;

        return $this;
    }

    public function getJobId(): ?int
    {
        return $this->jobId;
    }

    /**
     * @param int $jobId
     *
     * @return self
     */
    public function setJobId(int $jobId): FrontendImportExportResult
    {
        $this->jobId = $jobId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExpired(): ?bool
    {
        return $this->expired;
    }

    /**
     * @param bool $expired
     *
     * @return self
     */
    public function setExpired(bool $expired): FrontendImportExportResult
    {
        $this->expired = $expired;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type): FrontendImportExportResult
    {
        $this->type = $type;

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     *
     * @return self
     */
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
