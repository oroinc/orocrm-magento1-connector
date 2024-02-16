<?php

namespace Oro\Bundle\MagentoBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityNotFoundException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\MagentoBundle\Entity\Manager\OrderApiEntityManager;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderAddress;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * API CRUD controller for OrderAddress entity.
 */
#[NamePrefix(['value' => 'oro_api_'])]
class OrderAddressController extends RestController implements ClassResourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('oro_magento.order_address.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('oro_magento.form.order_address.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_magento.form.handler.order_address.api');
    }

    /**
     * Get all addresses items.
     *
     * @ApiDoc(
     *      description="Get all addresses items",
     *      resource=true
     * )
     * @param int $orderId
     * @return JsonResponse
     */
    #[AclAncestor('oro_magento_order_view')]
    #[Get(requirements: ['orderId' => '\d+'])]
    public function cgetAction(int $orderId)
    {
        $addressItems = $this->getManager()->getAllSerializedItems($orderId);

        return new JsonResponse(
            $addressItems,
            empty($addressItems) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * Add address to the order.
     *
     * @ApiDoc(
     *      description="Add address to the order",
     *      resource=true
     * )
     * @param int $orderId
     * @return JsonResponse
     */
    #[Acl(id: 'oro_magento_order_address_create', type: 'entity', class: 'Oro\Bundle\MagentoBundle\Entity\OrderAddress', permission: 'CREATE')]
    #[Post(requirements: ['orderId' => '\d+'])]
    public function postAction(int $orderId)
    {
        /** @var Order $order */
        $order       = $this->getOrderManager()->find($orderId);
        $isProcessed = false;
        $entity      = new OrderAddress();

        if (!empty($order)) {
            $entity = $this->processForm($entity);

            if ($entity) {
                $view = $this->view($this->createResponseData($entity), Response::HTTP_CREATED);
                $isProcessed = true;
            } else {
                $view = $this->view($this->getForm(), Response::HTTP_BAD_REQUEST);
            }
        } else {
            $view = $this->view($this->getForm(), Response::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_CREATE, ['success' => $isProcessed, 'entity' => $entity]);
    }

    /**
     * Get order address.
     *
     * @param int $addressId
     * @param int $orderId
     *
     * @ApiDoc(
     *      description="Get order address",
     *      resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_magento_order_view')]
    #[Get(requirements: ['orderId' => '\d+', 'addressId' => '\d+'])]
    public function getAction(int $orderId, int $addressId)
    {
        $address = $this->getManager()->serializeElement($orderId, $addressId);

        return new JsonResponse(
            $address,
            empty($address) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * Update order address.
     *
     * @param int $addressId order address item id
     * @param int $orderId   order id
     *
     * @ApiDoc(
     *      description="Update order address",
     *      resource=true
     * )
     * @return Response
     */
    #[Acl(id: 'oro_magento_order_address_update', type: 'entity', class: 'Oro\Bundle\MagentoBundle\Entity\OrderAddress', permission: 'EDIT')]
    #[Put(requirements: ['orderId' => '\d+', 'addressId' => '\d+'])]
    public function putAction(int $orderId, int $addressId)
    {
        /** @var OrderAddress $address */
        $address = $this->getManager()->findOneBy(['owner' => $orderId, 'id' => $addressId]);

        if ($address) {
            if ($this->processForm($address)) {
                $view = $this->view(null, Response::HTTP_NO_CONTENT);
            } else {
                $view = $this->view($this->getForm(), Response::HTTP_BAD_REQUEST);
            }
        } else {
            $view = $this->view(null, Response::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_UPDATE, ['id' => $addressId, 'entity' => $address]);
    }

    /**
     * Delete order address.
     *
     * @param int $addressId order address item id
     * @param int $orderId   order id
     *
     * @ApiDoc(
     *      description="Delete order address",
     *      resource=true
     * )
     * @return Response
     */
    #[Acl(id: 'oro_magento_order_delete', type: 'entity', class: 'Oro\Bundle\MagentoBundle\Entity\OrderAddress', permission: 'DELETE')]
    #[Delete(requirements: ['orderId' => '\d+', 'addressId' => '\d+'])]
    public function deleteAction(int $orderId, int $addressId)
    {
        $isProcessed = false;

        /** @var OrderAddress $address */
        $address = $this->getManager()->findOneBy(['owner' => $orderId, 'id' => $addressId]);

        if (!$address) {
            $view = $this->view(null, Response::HTTP_NOT_FOUND);
        } else {
            try {
                $this->getDeleteHandler()->handleDelete($addressId, $this->getManager());
                $isProcessed = true;
                $view        = $this->view(null, Response::HTTP_NO_CONTENT);
            } catch (EntityNotFoundException $e) {
                $view = $this->view(null, Response::HTTP_NOT_FOUND);
            } catch (AccessDeniedException $e) {
                $view = $this->view(['reason' => $e->getMessage()], Response::HTTP_FORBIDDEN);
            }
        }

        return $this->buildResponse($view, self::ACTION_DELETE, ['id' => $addressId, 'success' => $isProcessed]);
    }

    /**
     * @return OrderApiEntityManager
     */
    protected function getOrderManager()
    {
        return $this->container->get('oro_magento.order.manager.api');
    }
}
