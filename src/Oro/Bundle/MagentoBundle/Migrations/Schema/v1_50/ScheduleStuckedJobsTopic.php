<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_50;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to schedule stucked jobs
 */
class ScheduleStuckedJobsTopic extends AbstractTopic
{
    public const NAME = 'oro_magento.schedule_stucked_jobs';

    public static function getName(): string
    {
        return self::NAME;
    }

    public static function getDescription(): string
    {
        return 'Schedule stucked jobs. Should be used only during the schema upgrade process.';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
    }
}
