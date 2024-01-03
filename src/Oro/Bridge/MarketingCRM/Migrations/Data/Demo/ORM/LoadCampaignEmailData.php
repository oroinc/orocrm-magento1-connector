<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\InternalTransportSettings;
use Oro\Bundle\CampaignBundle\Transport\EmailTransport;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\UserBundle\Entity\User;

class LoadCampaignEmailData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadCampaignData',
            'Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadMarketingListData',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->getClassMetadata('Oro\Bundle\CampaignBundle\Entity\EmailCampaign')->setLifecycleCallbacks([]);

        $marketingList = $manager->getRepository(MarketingList::class)->findOneBy([
            'entity' => 'Oro\Bundle\ContactBundle\Entity\Contact'
        ]);
        $campaigns = $manager->getRepository(Campaign::class)->findAll();
        $campaignsMax = count($campaigns) - 1;

        $defaultUser = $manager->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $emailCampaign     = new EmailCampaign();
        $transportSettings = new InternalTransportSettings();
        $emailCampaign->setTransportSettings($transportSettings)
            ->setOwner($defaultUser)
            ->setOrganization($this->getReference('default_organization'))
            ->setMarketingList($marketingList)
            ->setName('Special Sale')
            ->setSent(1)
            ->setCampaign($campaigns[mt_rand(0, $campaignsMax)])
            ->setTransport(EmailTransport::NAME)
            ->setSchedule('manual')
            ->setSenderEmail('magento.shop@magento-oro.com')
            ->setSenderName('Magento Shop')
            ->setCreatedAt(date_create('-' . (mt_rand(3600, 32535)) . 'seconds', new \DateTimeZone('UTC')));
        $emailCampaign->setUpdatedAt($emailCampaign->getCreatedAt());
        $emailCampaign->setSentAt($emailCampaign->getUpdatedAt());
        $this->addReference('marketing_activity_campaign', $emailCampaign);

        $manager->persist($emailCampaign);
        $manager->flush();
    }
}
