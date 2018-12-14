<?php

namespace Oro\Bridge\MagentoCalendarCRM\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMagentoCalendarCRMBridgeBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface
{
    /** @var ActivityExtension */
    private $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addCalendarActivityAssociations($schema);
    }

    /**
     * @param Schema $schema
     */
    private function addCalendarActivityAssociations(Schema $schema)
    {
        $associationTables = [
            'orocrm_magento_customer',
            'orocrm_magento_order',
        ];

        foreach ($associationTables as $tableName) {
            $associationTableName = $this->activityExtension->getAssociationTableName(
                'oro_calendar_event',
                $tableName
            );
            if (!$schema->hasTable($associationTableName)) {
                $this->activityExtension->addActivityAssociation($schema, 'oro_calendar_event', $tableName);
            }
        }
    }
}
