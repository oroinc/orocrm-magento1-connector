<?php

namespace Oro\Bundle\MagentoDemoDataBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData as LoadUserRolesData;

/**
 * Sets permissions defined in "@OroMagentoDemoDataBundle/Migrations/Data/ORM/CrmRoles/roles.yml" file.
 */
class LoadRolesData extends AbstractLoadAclData
{
    public function getDependencies()
    {
        return [
            LoadOrganizationAndBusinessUnitData::class,
            LoadUserRolesData::class
        ];
    }

    public function getDataPath()
    {
        return '@OroMagentoDemoDataBundle/Migrations/Data/ORM/CrmRoles/roles.yml';
    }

    protected function getRole(ObjectManager $objectManager, $roleName, $roleConfigData)
    {
        $role = parent::getRole($objectManager, $roleName, $roleConfigData);
        if (!$role) {
            $role = new Role($roleName);
        }

        return $role;
    }
}
