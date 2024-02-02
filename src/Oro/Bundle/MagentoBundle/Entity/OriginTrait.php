<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;

trait OriginTrait
{
    /**
     * Mage entity origin id
     * @var integer
     */
    #[ConfigField(defaultValues: ['importexport' => ['identity' => true]])]
    #[ORM\Column(name: 'origin_id', type: 'integer', options: ['unsigned' => true], nullable: true)]
    protected $originId;

    /**
     * @param int $originId
     *
     * @return $this
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOriginId()
    {
        return $this->originId;
    }
}
