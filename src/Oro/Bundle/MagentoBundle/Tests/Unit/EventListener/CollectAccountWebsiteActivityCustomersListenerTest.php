<?php
declare(strict_types=1);

namespace Oro\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\AccountBundle\Event\CollectAccountWebsiteActivityCustomersEvent;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\MagentoBundle\EventListener\CollectAccountWebsiteActivityCustomersListener;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class CollectAccountWebsiteActivityCustomersListenerTest extends TestCase
{
    public function testOnAccountView()
    {
        $customers = [new Customer()];

        $repository = $this->createMock(CustomerRepository::class);
        $repository->expects(static::once())
            ->method('findBy')
            ->with(['account' => 12345])
            ->willReturn($customers);
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(static::once())
            ->method('getRepository')
            ->with(Customer::class)
            ->willReturn($repository);

        $event = $this->createMock(CollectAccountWebsiteActivityCustomersEvent::class);

        $event->expects(static::once())->method('setCustomers')->with($customers);

        $listener = new CollectAccountWebsiteActivityCustomersListener($doctrine);
        $listener->onAccountView($event);
    }
}
