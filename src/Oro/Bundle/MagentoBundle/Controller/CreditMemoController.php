<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bridge\Twig\Attribute\Template;
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
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/CreditMemo/index.html.twig')]
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
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/CreditMemo/customerCreditMemos.html.twig')]
    public function customerCreditMemosAction(#[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'customerId')]
    Customer $customer, #[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'channelId')]
    Channel $channel)
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
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/CreditMemo/customerCreditMemosWidget.html.twig')]
    public function customerCreditMemosWidgetAction(#[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'customerId')]
    Customer $customer, #[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'channelId')]
    Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @param Order $order
     * @return array
     */
    #[Route(path: '/widget/order_credit_memo/{orderId}', name: 'oro_magento_order_credit_memo_widget', requirements: ['orderId' => '\d+'])]
    #[AclAncestor('oro_magento_credit_memo_view')]
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/CreditMemo/orderCreditMemosWidget.html.twig')]
    public function orderCreditMemosWidgetAction(#[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'orderId')]
    $order)
    {
        return ['order' => $order];
    }
}
