<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;

class DefaultConfigurableEntityDenormalizer implements ContextAwareDenormalizerInterface
{
    protected FieldHelper $fieldHelper;

    protected LoggerInterface $logger;

    /**
     * @param FieldHelper     $fieldHelper
     * @param LoggerInterface $logger
     */
    public function __construct(FieldHelper $fieldHelper, LoggerInterface $logger)
    {
        $this->fieldHelper = $fieldHelper;
        $this->logger      = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $this->logger->warning(
            sprintf('Invalid configuration for %s for mapping configurable entity attributes.', $type),
            [
                'data'    => $data,
                'context' => $context
            ]
        );

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return
            !is_array($data) &&
            class_exists($type) &&
            $this->fieldHelper->hasConfig($type) &&
            !empty($context['channelType']) &&
            $context['channelType'] == MagentoChannelType::TYPE;
    }
}
