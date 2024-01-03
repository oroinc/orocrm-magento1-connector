<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadOwnerUser extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');

        $role  = $manager
            ->getRepository(Role::class)
            ->findOneByRole('ROLE_ADMINISTRATOR');
        $group = $manager
            ->getRepository(Group::class)
            ->findOneByName('Administrators');

        $unit = $manager
            ->getRepository(BusinessUnit::class)
            ->findOneByName('Main');

        $organization = $manager->getRepository(Organization::class)->getFirst();

        $user = new User();
        $user->setUsername('owner_User');
        $user->addGroup($group);
        $user->addRole($role);
        $user->addBusinessUnit($unit);
        $user->setFirstname('Test Owner  FirstName');
        $user->setLastname('Test Owner LastName');
        $user->setEmail('owner@example.com');
        $user->setOwner($unit);
        $user->addGroup($group);
        $user->setPlainPassword('test password');
        $user->setSalt(md5(mt_rand(1, 222)));
        $user->setOrganization($organization);

        $userManager->updateUser($user);
        $this->setReference('owner_user', $user);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
