<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;

trait ChannelEntityTrait
{
    /**
     * @var Channel
     */
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    #[ORM\ManyToOne(targetEntity: 'Oro\Bundle\ChannelBundle\Entity\Channel')]
    #[ORM\JoinColumn(name: 'data_channel_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected $dataChannel;

    /**
     * @param Channel|null $channel
     * @return self
     *
     * Remove null after BAP-5248
     */
    public function setDataChannel(Channel $channel = null)
    {
        $this->dataChannel = $channel;

        return $this;
    }

    /**
     * @return Channel
     */
    public function getDataChannel()
    {
        return $this->dataChannel;
    }
}
