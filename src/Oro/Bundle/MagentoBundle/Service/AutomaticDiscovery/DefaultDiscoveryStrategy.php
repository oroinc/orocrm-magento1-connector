<?php

namespace Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

class DefaultDiscoveryStrategy extends AbstractDiscoveryStrategy
{
    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $rootAlias, $field, array $configuration, $entity)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $fieldValue = $propertyAccessor->getValue($entity, $field);

        $parameterName = ':' . $field;
        $qbFieldName = QueryBuilderUtil::getField($rootAlias, $field);

        $qb->andWhere($this->getFieldExpr($qb, $qbFieldName, $parameterName, $configuration))
            ->setParameter($parameterName, $fieldValue);
    }
}
