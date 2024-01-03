<?php

namespace Oro\Bundle\MagentoBundle\Command;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ChannelBundle\Command\RecalculateLifetimeCommand as AbstractRecalculateLifetimeCommand;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;

/**
 * Recalculates lifetime value of Magento customers.
 */
class RecalculateLifetimeCommand extends AbstractRecalculateLifetimeCommand
{
    /** @var string */
    protected static $defaultName = 'oro:magento:lifetime:recalculate';

    public function configure()
    {
        parent::configure();

        $this->setDescription('Recalculates lifetime value of Magento customers.');
    }

    protected function getChannelType(): string
    {
        return MagentoChannelType::TYPE;
    }

    /**
     * @param EntityManager $em
     * @param Customer      $customer
     */
    protected function calculateCustomerLifetime(EntityManager $em, $customer): float
    {
        /** @var CustomerRepository $customerRepo */
        $customerRepo  = $em->getRepository(Customer::class);
        $lifetimeValue = $customerRepo->calculateLifetimeValue($customer);

        return $lifetimeValue;
    }
}
