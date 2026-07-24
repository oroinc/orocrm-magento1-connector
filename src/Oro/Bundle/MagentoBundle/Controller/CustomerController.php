<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Form\Handler\CustomerHandler;
use Oro\Bundle\MagentoBundle\Form\Type\CustomerType;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Magento Customer Controller
 */
#[Route(path: '/customer')]
class CustomerController extends AbstractController
{
    #[Route(path: '/', name: 'oro_magento_customer_index')]
    #[AclAncestor('oro_magento_customer_view')]
    #[Template(template: '@OroMagento/Customer/index.html.twig')]
    public function indexAction()
    {
        return [
            'entity_class' => Customer::class
        ];
    }

    /**
     * @param Customer $customer
     * @return array
     */
    #[Route(path: '/view/{id}', name: 'oro_magento_customer_view', requirements: ['id' => '\d+'])]
    public function viewAction(Customer $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @param Customer $customer
     * @return array
     */
    #[Route(path: '/update/{id}', name: 'oro_magento_customer_update', requirements: ['id' => '\d+'])]
    public function updateAction(Customer $customer)
    {
        return $this->update($customer);
    }

    #[Route(path: '/create', name: 'oro_magento_customer_create')]
    public function createAction()
    {
        if (!$this->isGranted('oro_integration_assign')) {
            throw new AccessDeniedException();
        }

        return $this->update(new Customer());
    }

    /**
     * @param Customer $customer
     * @return JsonResponse
     */
    #[Route(path: '/register/{id}', name: 'oro_magento_customer_register', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[AclAncestor('oro_magento_customer_update')]
    #[CsrfProtection]
    public function registerAction(Customer $customer)
    {
        return new JsonResponse([
            'successful' => $this->container->get(CustomerHandler::class)->handleRegister($customer),
        ]);
    }

    /**
     * @param Customer $customer
     * @return array
     */
    protected function update(Customer $customer)
    {
        return $this->container->get(CustomerHandler::class)->handleUpdate(
            $customer,
            $this->createForm(CustomerType::class, $customer),
            function (Customer $customer) {
                return [
                    'route' => 'oro_magento_customer_update',
                    'parameters' => ['id' => $customer->getId()]
                ];
            },
            function (Customer $customer) {
                return [
                    'route' => 'oro_magento_customer_view',
                    'parameters' => ['id' => $customer->getId()]
                ];
            },
            $this->container->get(TranslatorInterface::class)->trans('oro.magento.customer.saved.message')
        );
    }

    /**
     * @param Customer $customer
     * @return array
     */
    #[Route(path: '/info/{id}', name: 'oro_magento_customer_info', requirements: ['id' => '\d+'])]
    public function infoAction(Customer $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @param Account $account
     * @param Channel $channel
     * @return array
     */
    #[Route(path: '/widget/customers-info/{accountId}/{channelId}', name: 'oro_magento_widget_account_customers_info', requirements: ['accountId' => '\d+', 'channelId' => '\d+'])]
    #[AclAncestor('oro_magento_customer_view')]
    #[Template(template: '@OroMagento/Customer/accountCustomersInfo.html.twig')]
    public function accountCustomersInfoAction(
        #[MapEntity(id: 'accountId')]
        Account $account,
        #[MapEntity(id: 'channelId')]
        Channel $channel
    ) {
        $customers = $this->getDoctrine()
            ->getRepository('Oro\\Bundle\\MagentoBundle\\Entity\\Customer')
            ->findBy(['account' => $account, 'dataChannel' => $channel]);
        $customers = array_filter(
            $customers,
            function ($item) {
                return $this->isGranted('VIEW', $item);
            }
        );

        return ['customers' => $customers, 'channel' => $channel, 'account' => $account];
    }

    /**
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    #[Route(path: '/widget/customer-info/{id}/{channelId}', name: 'oro_magento_widget_customer_info', requirements: ['id' => '\d+', 'channelId' => '\d+'])]
    #[AclAncestor('oro_magento_customer_view')]
    #[Template(template: '@OroMagento/Customer/customerInfo.html.twig')]
    public function customerInfoAction(
        Customer $customer,
        #[MapEntity(id: 'channelId')]
        Channel $channel
    ) {
        return [
            'customer'            => $customer,
            'channel'             => $channel,
            'orderClassName'      => Order::class,
            'cartClassName'       => Cart::class,
            'creditMemoClassName' => CreditMemo::class
        ];
    }

    /**
     * @param Customer $customer
     * @return array
     */
    #[Route(path: '/order/{id}', name: 'oro_magento_customer_orderplace', requirements: ['id' => '\d+'])]
    public function placeOrderAction(Customer $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @param Customer $customer
     * @return array
     */
    #[Route(path: '/addressBook/{id}', name: 'oro_magento_customer_address_book', requirements: ['id' => '\d+'])]
    public function addressBookAction(Customer $customer)
    {
        return ['entity' => $customer];
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                CustomerHandler::class,
            ]
        );
    }
}
