<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\MigrationBundle\Fixture\RenamedFixtureInterface;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ReportBundle\Entity\Report;
use Oro\Bundle\ReportBundle\Entity\ReportType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates the "Campaign Performance" report.
 */
class LoadCampaignPerformanceReport extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface,
    RenamedFixtureInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\ReportBundle\Migrations\Data\ORM\LoadReportTypes',
            'Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getPreviousClassNames(): array
    {
        return [
            'Oro\\Bridge\\MarketingCRM\\Migrations\\Migrations\\Data\\ORM\\LoadCampaignPerformanceReport',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load "Campaign Performance" report definition
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $report = new Report();
        $report->setName('Campaign Performance');
        $report->setEntity('Oro\Bundle\CampaignBundle\Entity\Campaign');
        $type = $em->getReference(ReportType::class, ReportType::TYPE_TABLE);
        $report->setType($type);
        // @codingStandardsIgnoreStart
        $definition = [
            'filters'          => [],
            'columns'          => [
                ['name' => 'name', 'label' => 'Name', 'func' => '', 'sorting' => ''],
                ['name' => 'code', 'label' => 'Code', 'func' => '', 'sorting' => ''],
                [
                    'name'    => 'Oro\\Bundle\\SalesBundle\\Entity\\Lead::campaign+Oro\\Bundle\\SalesBundle\\Entity\\Lead::id',
                    'label'   => 'Leads',
                    'func'    => [
                        'name'       => 'Count',
                        'group_type' => 'aggregates',
                        'group_name' => 'number'
                    ],
                    'sorting' => ''
                ],
                [
                    'name'    => 'Oro\\Bundle\\SalesBundle\\Entity\\Lead::campaign+Oro\\Bundle\\SalesBundle\\Entity\\Lead::opportunities+Oro\\Bundle\\SalesBundle\\Entity\\Opportunity::id',
                    'label'   => 'Opportunities',
                    'func'    => [
                        'name'       => 'Count',
                        'group_type' => 'aggregates',
                        'group_name' => 'number'
                    ],
                    'sorting' => ''
                ],
                [
                    'name'    => 'Oro\\Bundle\\SalesBundle\\Entity\\Lead::campaign+Oro\\Bundle\\SalesBundle\\Entity\\Lead::opportunities+Oro\\Bundle\\SalesBundle\\Entity\\Opportunity::status',
                    'label'   => 'Number Won',
                    'func'    => [
                        'name'       => 'WonCount',
                        'group_type' => 'aggregates',
                        'group_name' => 'opportunity_status'
                    ],
                    'sorting' => ''
                ],
                [
                    'name'    => 'Oro\\Bundle\\SalesBundle\\Entity\\Lead::campaign+Oro\\Bundle\\SalesBundle\\Entity\\Lead::opportunities+Oro\\Bundle\\SalesBundle\\Entity\\Opportunity::status',
                    'label'   => 'Number Lost',
                    'func'    => [
                        'name'       => 'LostCount',
                        'group_type' => 'aggregates',
                        'group_name' => 'opportunity_status'
                    ],
                    'sorting' => ''
                ],
                [
                    'name'    => 'Oro\\Bundle\\SalesBundle\\Entity\\Lead::campaign+Oro\\Bundle\\SalesBundle\\Entity\\Lead::opportunities+Oro\\Bundle\\SalesBundle\\Entity\\Opportunity::closeRevenueBaseCurrency',
                    'label'   => 'Close revenue',
                    'func'    => [
                        'name'       => 'WonRevenueSumFunction',
                        'group_type' => 'aggregates',
                        'group_name' => 'opportunity'
                    ],
                    'sorting' => 'DESC'
                ]
            ],
            'grouping_columns' => [
                [
                    'name' => 'code'
                ],
                [
                    'name' => 'name'
                ],
            ]
        ];
        // @codingStandardsIgnoreEnd
        $report->setDefinition(json_encode($definition));
        $report->setOrganization($manager->getRepository(Organization::class)->getFirst());
        $report->setOwner($manager->getRepository(BusinessUnit::class)->getFirst());
        $em->persist($report);
        $em->flush($report);
    }
}
