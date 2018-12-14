<?php

namespace Oro\Bundle\MagentoDemoDataBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;
use Oro\Bundle\WorkflowBundle\Model\Filter\WorkflowDefinitionFilters;

/**
 * Loads CRM workflows ACL data
 */
class LoadWorkflowAclData extends AbstractLoadAclData
{
    public function getDependencies()
    {
        return [
            LoadRolesData::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        /* @var $filters WorkflowDefinitionFilters */
        $filters = $this->container->get('oro_workflow.registry.definition_filters');
        $filters->setEnabled(false); // disable filters, because some workflows disabled by `features` by default

        parent::load($manager);

        $filters->setEnabled(true);
    }

    protected function getDataPath()
    {
        return '@OroMagentoDemoDataBundle/Migrations/Data/ORM/CrmRoles/workflows.yml';
    }
}
