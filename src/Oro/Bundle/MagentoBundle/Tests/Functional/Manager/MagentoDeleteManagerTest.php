<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Manager;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class MagentoDeleteManagerTest extends WebTestCase
{
    /** @var int */
    protected static $channelId;

    /**
     * @var EntityManager
     */
    protected $em;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->em = $this->client->getKernel()->getContainer()->get('doctrine.orm.entity_manager');

        $fixtures = ['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel'];
        $this->loadFixtures($fixtures);
    }

    protected function postFixtureLoad()
    {
        $channel = $this->getReference('default_channel');
        if (!$channel) {
            $this->markTestIncomplete('Invalid fixtures, unable to perform test case');
        }

        self::$channelId = $channel->getId();
    }

    public function testDeleteChannel()
    {
        $channel   = $this->em->find(Channel::class, self::$channelId);

        $integration = $channel->getDataSource();

        $integrationId = $integration->getId();

        $this->assertGreaterThan(0, $this->getRecordsCount(Cart::class, $integration));
        $this->assertGreaterThan(0, $this->getRecordsCount(Order::class, $integration));
        $this->assertGreaterThan(0, $this->getRecordsCount(Website::class, $integration));
        $this->client->getKernel()->getContainer()->get('oro_integration.delete_manager')->delete(
            $integration
        );
        $this->assertEquals(0, $this->getRecordsCount(Cart::class, $integrationId));
        $this->assertEquals(0, $this->getRecordsCount(Order::class, $integrationId));
        $this->assertEquals(0, $this->getRecordsCount(Website::class, $integrationId));
    }

    /**
     * @param $repository
     * @param $channel
     *
     * @return integer
     */
    protected function getRecordsCount($repository, $channel)
    {
        $result = $this->em->createQueryBuilder()
            ->select('COUNT(e)')
            ->from($repository, 'e')
            ->where('e.channel = :channel')
            ->setParameter('channel', $channel)
            ->getQuery()
            ->getOneOrNullResult();

        return array_shift($result);
    }
}
