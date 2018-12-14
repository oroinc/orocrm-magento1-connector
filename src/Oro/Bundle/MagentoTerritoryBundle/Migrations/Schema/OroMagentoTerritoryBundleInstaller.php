<?php

namespace Oro\Bundle\MagentoTerritoryBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\TerritoryBundle\Migration\TerritoryExtensionAwareInterface;
use Oro\Bundle\TerritoryBundle\Migration\TerritoryExtensionAwareTrait;

class OroMagentoTerritoryBundleInstaller implements Installation, TerritoryExtensionAwareInterface
{
    use TerritoryExtensionAwareTrait;

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
        $this->territoryExtension->addTerritoryAssociation($schema, 'orocrm_magento_customer');
    }
}
