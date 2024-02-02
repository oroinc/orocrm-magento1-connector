<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller for Magento CreditMemo entity.
 */
#[Route(path: '/credit-memo')]
class CreditMemoController extends AbstractController
{
    #[Route(path: '/', name: 'oro_magento_credit_memo_index')]
    #[AclAncestor('oro_magento_credit_memo_view')]
    #[Template]
    public function indexAction()
    {
        return [
            'entity_class' => CreditMemo::class
        ];
    }

    /**
     * @param CreditMemo $entity
     *
     * @return array
     */
    #[Route(path: '/view/{id}', name: 'oro_magento_credit_memo_view', requirements: ['id' => '\d+'])]
    public function viewAction(CreditMemo $entity)
    {
        return ['entity' => $entity];
    }

    /**
     * @param CreditMemo $entity
     *
     * @return array
     */
    #[Route(path: '/info/{id}', name: 'oro_magento_credit_memo_widget_info', requirements: ['id' => '\d+'])]
    public function infoAction(CreditMemo $entity)
    {
        return ['entity' => $entity];
    }

    /**
     * @param CreditMemo $entity
     * @return array
     */
    #[Route(path: '/widget/grid/{id}', name: 'oro_magento_credit_memo_widget_items', requirements: ['id' => '\d+'])]
    public function itemsAction(CreditMemo $entity)
    {
        return ['entity' => $entity];
    }

    /**
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    #[Route(path: '/account-widget/customer_credit_memo/{customerId}/{channelId}', name: 'oro_magento_widget_customer_credit_memo', requirements: ['customerId' => '\d+', 'channelId' => '\d+'])]
    #[AclAncestor('oro_magento_credit_memo_view')]
    #[ParamConverter('customer', class: 'Oro\Bundle\MagentoBundle\Entity\Customer', options: ['id' => 'customerId'])]
    #[ParamConverter('channel', class: 'Oro\Bundle\IntegrationBundle\Entity\Channel', options: ['id' => 'channelId'])]
    #[Template]
    public function customerCreditMemosAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    #[Route(path: '/widget/customer_credit_memo/{customerId}/{channelId}', name: 'oro_magento_customer_credit_memo_widget', requirements: ['customerId' => '\d+', 'channelId' => '\d+'])]
    #[AclAncestor('oro_magento_credit_memo_view')]
    #[ParamConverter('customer', class: 'Oro\Bundle\MagentoBundle\Entity\Customer', options: ['id' => 'customerId'])]
    #[ParamConverter('channel', class: 'Oro\Bundle\IntegrationBundle\Entity\Channel', options: ['id' => 'channelId'])]
    #[Template]
    public function customerCreditMemosWidgetAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @param Order $order
     * @return array
     */
    #[Route(path: '/widget/order_credit_memo/{orderId}', name: 'oro_magento_order_credit_memo_widget', requirements: ['orderId' => '\d+'])]
    #[AclAncestor('oro_magento_credit_memo_view')]
    #[ParamConverter('order', class: 'Oro\Bundle\MagentoBundle\Entity\Order', options: ['id' => 'orderId'])]
    #[Template]
    public function orderCreditMemosWidgetAction($order)
    {
        return ['order' => $order];
    }
}
