<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepositoryInterface;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\Repository\CartRepository;
use Oro\Bundle\MagentoBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\MagentoBundle\Entity\Repository\OrderRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Calculates various metrics for Magento channel.
 */
class MagentoBigNumberProvider
{
    use DateFilterTrait;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /** @var WebsiteVisitProviderInterface */
    protected $websiteVisitProvider;

    /**
     * @param ManagerRegistry               $doctrine
     * @param AclHelper                     $aclHelper
     * @param BigNumberDateHelper           $dateHelper
     * @param WebsiteVisitProviderInterface $websiteVisitProvider
     */
    public function __construct(
        ManagerRegistry $doctrine,
        AclHelper $aclHelper,
        BigNumberDateHelper $dateHelper,
        WebsiteVisitProviderInterface $websiteVisitProvider
    ) {
        $this->doctrine   = $doctrine;
        $this->aclHelper  = $aclHelper;
        $this->dateHelper = $dateHelper;
        $this->websiteVisitProvider = $websiteVisitProvider;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getRevenueValues($dateRange)
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Order::class, 'createdAt');

        $qb = $this->getOrderRepository()->getRevenueValueQB();
        $this->applyDateFiltering($qb, 'orders.createdAt', $start, $end);
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getOrdersNumberValues($dateRange)
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Order::class, 'createdAt');
        $qb = $this->getOrderRepository()->getOrdersNumberValueQB();
        $this->applyDateFiltering($qb, 'o.createdAt', $start, $end);
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getAOVValues($dateRange)
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Order::class, 'createdAt');

        $qb = $this->getOrderRepository()->getAOVValueQB();
        $this->applyDateFiltering($qb, 'o.createdAt', $start, $end);
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['ordersCount'] ? $value['revenue'] / $value['ordersCount'] : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return float
     */
    public function getDiscountedOrdersPercentValues($dateRange)
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Order::class, 'createdAt');
        $qb = $this->getOrderRepository()->getDiscountedOrdersPercentQB();
        $this->applyDateFiltering($qb, 'o.createdAt', $start, $end);
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['allOrders'] ? $value['discounted'] / $value['allOrders'] : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getNewCustomersCountValues($dateRange)
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Customer::class, 'createdAt');
        $qb = $this->getCustomerRepository()->getNewCustomersNumberWhoMadeOrderQB();
        $this->applyDateFiltering($qb, 'orders.createdAt', $start, $end);
        $this->applyDateFiltering($qb, 'customer.createdAt', $start, $end);
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getReturningCustomersCountValues($dateRange)
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Customer::class, 'createdAt');
        $qb = $this->getCustomerRepository()->getReturningCustomersWhoMadeOrderQB();
        $this->applyDateFiltering($qb, 'orders.createdAt', $start, $end);
        if ($start) {
            $qb
                ->andWhere('customer.createdAt < :start')
                ->setParameter('start', $start, Types::DATETIME_MUTABLE);
        }
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getAbandonedRevenueValues($dateRange)
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Cart::class, 'createdAt');

        $qb = $this->getCartRepository()->getAbandonedRevenueQB();
        $this->applyDateFiltering($qb, 'cart.createdAt', $start, $end);
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getAbandonedCountValues($dateRange)
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Cart::class, 'createdAt');

        $qb    = $this->getCartRepository()->getAbandonedCountQB();
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();
        $this->applyDateFiltering($qb, 'cart.createdAt', $start, $end);

        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return float|null
     */
    public function getAbandonRateValues($dateRange)
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Cart::class, 'createdAt');
        $qb = $this->getCartRepository()->getGrandTotalSumQB();
        $this->applyDateFiltering($qb, 'cart.createdAt', $start, $end);
        $allCards = $this->aclHelper->apply($qb)->getOneOrNullResult();
        $allCards = (int)$allCards['val'];
        $result   = 0;
        if (0 !== $allCards) {
            $abandonedCartsCount = $this->getAbandonedCountValues($dateRange);
            $result              = $abandonedCartsCount / $allCards;
        }

        return $result;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getSiteVisitsValues($dateRange)
    {
        return $this->websiteVisitProvider->getSiteVisitsValues($dateRange);
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getOrderConversionValues($dateRange)
    {
        $result = 0;
        /**
         * Remove dependency on exact magento channel type in CRM-8153
         */
        $visitsQb = $this->getChannelRepository()->getVisitsCountForChannelTypeQB(MagentoChannelType::TYPE);
        if (!$visitsQb instanceof QueryBuilder) {
            return $result;
        }

        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Order::class, 'createdAt');
        $this->applyDateFiltering($visitsQb, 'visit.firstActionTime', $start, $end);
        $visits = (int)$this->aclHelper->apply($visitsQb)->getSingleScalarResult();
        if ($visits != 0) {
            $ordersCount = $this->getOrdersNumberValues($dateRange);
            $result      = $ordersCount / $visits;
        }

        return $result;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getCustomerConversionValues($dateRange)
    {
        $result = 0;

        /**
         * Remove dependency on exact magento channel type in CRM-8153
         */
        $visitsQb = $this->getChannelRepository()->getVisitsCountForChannelTypeQB(MagentoChannelType::TYPE);
        if (!$visitsQb instanceof QueryBuilder) {
            return $result;
        }

        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Customer::class, 'createdAt');
        $this->applyDateFiltering($visitsQb, 'visit.firstActionTime', $start, $end);
        $visits = (int)$this->aclHelper->apply($visitsQb)->getSingleScalarResult();
        if ($visits !== 0) {
            $customers = $this->getNewCustomersCountValues($dateRange);
            $result    = $customers / $visits;
        }

        return $result;
    }

    /**
     * @return OrderRepository
     */
    protected function getOrderRepository()
    {
        return $this->doctrine->getRepository(Order::class);
    }

    /**
     * @return CustomerRepository
     */
    protected function getCustomerRepository()
    {
        return $this->doctrine->getRepository(Customer::class);
    }

    /**
     * @return CartRepository
     */
    protected function getCartRepository()
    {
        return $this->doctrine->getRepository(Cart::class);
    }

    /**
     * @return ChannelRepositoryInterface
     */
    protected function getChannelRepository()
    {
        return $this->doctrine->getRepository(Channel::class);
    }
}
