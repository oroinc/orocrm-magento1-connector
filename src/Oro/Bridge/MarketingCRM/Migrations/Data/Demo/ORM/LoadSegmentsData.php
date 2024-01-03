<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\SegmentBundle\Entity\SegmentType;

/**
 * Creates a segment of contacts.
 */
class LoadSegmentsData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadBusinessUnitData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $segment = new Segment();
        $definition['columns'][] = [
            'name'    => 'primaryEmail',
            'label'   => 'primaryEmail',
            'sorting' => '',
            'func'    => null,
        ];
        $segment->setName('Contact List Segment')
            ->setDefinition(json_encode($definition))
            ->setEntity('Oro\Bundle\ContactBundle\Entity\Contact')
            ->setOrganization($this->getReference('default_organization'))
            ->setOwner($manager->getRepository(BusinessUnit::class)->getFirst())
            ->setType($manager->getRepository(SegmentType::class)->findOneBy(['name' => 'dynamic']));
        $manager->persist($segment);

        $manager->flush();
    }
}
