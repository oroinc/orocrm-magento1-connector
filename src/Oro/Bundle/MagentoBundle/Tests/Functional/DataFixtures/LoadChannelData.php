<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;

class LoadChannelData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = [
        'not_supports' => [
            'customerIdentity' => 'Oro\Bundle\ChannelBundle\Entity\CustomerIdentity',
            'name' => 'CustomerIdentityChannel',
            'channelType' => 'b2b',
            'status' => Channel::STATUS_ACTIVE,
            'reference' => 'Channel.CustomerIdentity'
        ],
        'supports' => [
            'customerIdentity' => 'Oro\Bundle\MagentoBundle\Entity\Customer',
            'name' => 'CustomerChannel',
            'channelType' => 'magento',
            'status' => Channel::STATUS_ACTIVE,
            'data' => [RFMAwareInterface::RFM_STATE_KEY => true],
            'reference' => 'Channel.CustomerChannel'
        ],
        'second supported' => [
            'customerIdentity' => 'Oro\Bundle\MagentoBundle\Entity\Customer',
            'name' => 'CustomerChannel2',
            'channelType' => 'magento',
            'status' => Channel::STATUS_ACTIVE,
            'data' => [RFMAwareInterface::RFM_STATE_KEY => true],
            'reference' => 'Channel.CustomerChannel2'
        ],
        'rfm disabled' => [
            'customerIdentity' => 'Oro\Bundle\MagentoBundle\Entity\Customer',
            'name' => 'CustomerChannel3',
            'channelType' => 'magento',
            'status' => Channel::STATUS_ACTIVE,
            'data' => [RFMAwareInterface::RFM_STATE_KEY => false],
            'reference' => 'Channel.CustomerChannel3'
        ],
        'notActive' => [
            'customerIdentity' => 'Oro\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface',
            'name' => 'AnalyticsAwareInterfaceChannel',
            'channelType' => 'magento',
            'status' => Channel::STATUS_INACTIVE,
            'reference' => 'Channel.AnalyticsAwareInterface'
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            $entity = new Channel();

            $excludeProperties = ['reference'];
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            foreach ($data as $property => $value) {
                if (in_array($property, $excludeProperties)) {
                    continue;
                }
                $propertyAccessor->setValue($entity, $property, $value);
            }

            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
