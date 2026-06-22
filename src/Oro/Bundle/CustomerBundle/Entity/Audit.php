<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\DataAuditBundle\Entity\AbstractAudit;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
 * Entity that represents Audit
 *
 * @ORM\Entity()
 * @Config(
 *      defaultValues={
 *          "security"={}
 *     }
 * )
 */
class Audit extends AbstractAudit
{
    /**
     * @var CustomerUser $user
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUser", cascade={"persist"})
     * @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "email"={
     *              "available_in_template"=false,
     *              "immutable"=true
     *          }
     *      }
     * )
     */
    protected $customerUser;

    /**
     * {@inheritdoc}
     */
    public function setUser(AbstractUser $user = null)
    {
        $this->customerUser = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->customerUser;
    }
}
