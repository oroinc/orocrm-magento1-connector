<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

class PaymentDetailsNormalizer extends ConfigurableEntityNormalizer
{
    protected ?string $supportedClass = null;

    public function setSupportedClass(string $supportedClass): void
    {
        $this->supportedClass = $supportedClass;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (isset($data['paymentDetails'])) {
            $data['paymentDetails'] = $this->denormalizePaymentDetails($data['paymentDetails']);
        }

        return parent::denormalize($data, $type, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return $type === $this->supportedClass;
    }

    /**
     * Format payment info
     * Magento brings only CC payments info,
     * for different types information could not be taken from order info
     *
     * @param array $paymentDetails
     *
     * @return string|null
     */
    public function denormalizePaymentDetails(array $paymentDetails): ?string
    {
        $ccType = isset($paymentDetails['cc_type']) ? trim($paymentDetails['cc_type']) : null;
        $ccLast4 = isset($paymentDetails['cc_last4']) ? trim($paymentDetails['cc_last4']) : null;

        $paymentDetailsString = null;

        if (!empty($paymentDetails['cc_type']) && !empty($paymentDetails['cc_last4'])) {
            $paymentDetailsString = sprintf('Card [%s, %s]', $ccType, $ccLast4);
        }

        return $paymentDetailsString;
    }
}
