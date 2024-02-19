<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\BusinessEntitiesBundle\Entity\BasePersonGroup;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Class CustomerGroup
 *
 * @package Oro\Bundle\OroMagentoBundle\Entity
 */
#[ORM\Entity]
#[Config(defaultValues: ['entity' => ['icon' => 'fa-users'], 'activity' => ['immutable' => true], 'attachment' => ['immutable' => true]])]
#[ORM\Table(name: 'orocrm_magento_customer_group')]
class CustomerGroup extends BasePersonGroup implements
    OriginAwareInterface,
    IntegrationAwareInterface,
    ExtendEntityInterface
{
    use IntegrationEntityTrait, OriginTrait, ExtendEntityTrait;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    protected ?string $name;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }
}
