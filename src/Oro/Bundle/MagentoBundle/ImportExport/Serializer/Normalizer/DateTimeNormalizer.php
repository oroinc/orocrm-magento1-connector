<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DateTimeNormalizer as BaseNormalizer;
use Oro\Bundle\ImportExportBundle\Serializer\Serializer;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class DateTimeNormalizer implements ContextAwareNormalizerInterface, ContextAwareDenormalizerInterface
{
    private BaseNormalizer $magentoNormalizer;

    private BaseNormalizer $isoNormalizer;

    public function __construct()
    {
        $this->magentoNormalizer = new BaseNormalizer('Y-m-d H:i:s', 'Y-m-d', 'H:i:s', 'UTC');
        $this->isoNormalizer = new BaseNormalizer(\DateTime::ISO8601, 'Y-m-d', 'H:i:s', 'UTC');
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        try {
            return $this->magentoNormalizer->denormalize($data, $type, $format, $context);
        } catch (RuntimeException $e) {
            return $this->isoNormalizer->denormalize($data, $type, $format, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return $this->magentoNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return $this->magentoNormalizer->supportsDenormalization($data, $type, $format, $context)
            && !empty($context[Serializer::PROCESSOR_ALIAS_KEY])
            && strpos($context[Serializer::PROCESSOR_ALIAS_KEY], 'oro_magento') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $this->magentoNormalizer->supportsNormalization($data, $format, $context)
            && !empty($context[Serializer::PROCESSOR_ALIAS_KEY])
            && str_contains($context[Serializer::PROCESSOR_ALIAS_KEY], 'oro_magento');
    }
}
