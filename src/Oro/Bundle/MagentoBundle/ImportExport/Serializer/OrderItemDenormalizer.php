<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;
use Oro\Bundle\MagentoBundle\Entity\OrderItem;
use Oro\Bundle\MagentoBundle\Provider\Connector\MagentoConnectorInterface;

class OrderItemDenormalizer extends ConfigurableEntityNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, $format = null, array $context = [])
    {
        /** @var OrderItem $object */
        $object = parent::denormalize($data, $type, $format, $context);

        if ($object->getDiscountPercent()) {
            $object->setDiscountPercent($object->getDiscountPercent() / 100);
        }
        if ($object->getTaxPercent()) {
            $object->setTaxPercent($object->getTaxPercent() / 100);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return $type === MagentoConnectorInterface::ORDER_ITEM_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return false;
    }
}
