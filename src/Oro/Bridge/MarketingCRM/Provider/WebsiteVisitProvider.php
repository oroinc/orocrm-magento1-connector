<?php

namespace Oro\Bridge\MarketingCRM\Provider;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Oro\Bundle\MagentoBundle\Provider\DateFilterTrait;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\MagentoBundle\Provider\WebsiteVisitProviderInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;

class WebsiteVisitProvider implements WebsiteVisitProviderInterface, FeatureToggleableInterface
{
    use DateFilterTrait, FeatureCheckerHolderTrait;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /**
     * @param ManagerRegistry     $doctrine
     * @param AclHelper           $aclHelper
     * @param BigNumberDateHelper $dateHelper
     */
    public function __construct(
        ManagerRegistry $doctrine,
        AclHelper $aclHelper,
        BigNumberDateHelper $dateHelper
    ) {
        $this->doctrine   = $doctrine;
        $this->aclHelper  = $aclHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @inheritdoc
     */
    public function getSiteVisitsValues($dateRange)
    {
        if (!$this->isFeaturesEnabled()) {
            return 0;
        }
        /**
         * @todo Remove dependency on exact magento channel type in CRM-8153
         */
        $visitsQb = $this->getChannelRepository()->getVisitsCountForChannelTypeQB(MagentoChannelType::TYPE);
        if (!$visitsQb instanceof QueryBuilder) {
            return 0;
        }

        list($start, $end) = $this->dateHelper->getPeriod(
            $dateRange,
            TrackingVisit::class,
            'firstActionTime'
        );
        $this->applyDateFiltering($visitsQb, 'visit.firstActionTime', $start, $end);

        return (int) $this->aclHelper->apply($visitsQb)->getSingleScalarResult();
    }

    /**
     * @return ChannelRepository
     */
    protected function getChannelRepository()
    {
        return $this->doctrine->getRepository(Channel::class);
    }
}
