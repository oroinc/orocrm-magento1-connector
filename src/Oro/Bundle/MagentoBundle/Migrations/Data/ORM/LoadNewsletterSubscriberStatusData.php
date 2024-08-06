<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class LoadNewsletterSubscriberStatusData extends AbstractEnumFixture
{
    #[\Override]
    protected function getData(): array
    {
        return [
            NewsletterSubscriber::STATUS_SUBSCRIBED => 'Subscribed',
            NewsletterSubscriber::STATUS_UNSUBSCRIBED => 'Unsubscribed',
            NewsletterSubscriber::STATUS_UNCONFIRMED => 'Unconfirmed',
            NewsletterSubscriber::STATUS_NOT_ACTIVE => 'Not active'
        ];
    }

    #[\Override]
    protected function getEnumCode(): string
    {
        return 'mage_subscr_status';
    }
}
