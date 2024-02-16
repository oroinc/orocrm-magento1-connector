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
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\CartItem;
use Oro\Bundle\MagentoBundle\Entity\Manager\CartApiEntityManager;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * API CRUD controller for CartItem entity.
 */
#[NamePrefix(['value' => 'oro_api_'])]
class CartItemController extends RestController implements ClassResourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('oro_magento.cart_item.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('oro_magento.form.cart_item.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_magento.form.handler.cart_item');
    }

    /**
     * Add item to the cart.
     *
     * @ApiDoc(
     *      description="Add item to the cart",
     *      resource=true
     * )
     * @param int $cartId
     * @return JsonResponse
     */
    #[Acl(id: 'oro_magento_cart_item_create', type: 'entity', class: 'Oro\Bundle\MagentoBundle\Entity\CartItem', permission: 'CREATE')]
    #[Post(requirements: ['cartId' => '\d+'])]
    public function postAction(int $cartId)
    {
        /** @var Cart $cart */
        $cart        = $this->getCartManager()->find($cartId);
        $isProcessed = false;
        $entity      = new CartItem();

        if (!empty($cart)) {
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
     * Get cart item.
     *
     * @param int $cartId
     * @param int $itemId
     *
     * @ApiDoc(
     *      description="Get cart item",
     *      resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_magento_cart_view')]
    #[Get(requirements: ['cartId' => '\d+', 'itemId' => '\d+'])]
    public function getAction($cartId, $itemId)
    {
        $cartItem = $this->getManager()->getSpecificSerializedItem($cartId, $itemId);

        return new JsonResponse(
            $cartItem,
            empty($cartItem) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * Get all cart items.
     *
     * @ApiDoc(
     *      description="Get all cart items",
     *      resource=true
     * )
     *
     * @param int $cartId
     * @return JsonResponse
     */
    #[AclAncestor('oro_magento_cart_view')]
    #[Get(requirements: ['cartId' => '\d+'])]
    public function cgetAction(int $cartId)
    {
        $cartItems = $this->getManager()->getAllSerializedItems($cartId);

        return new JsonResponse(
            $cartItems,
            empty($cartItems) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * Update cart item.
     *
     * @param int $itemId cart item id
     * @param int $cartId cart id
     *
     * @ApiDoc(
     *      description="Update cart item",
     *      resource=true
     * )
     * @return Response
     */
    #[Acl(id: 'oro_magento_cart_item_update', type: 'entity', class: 'Oro\Bundle\MagentoBundle\Entity\CartItem', permission: 'EDIT')]
    #[Put(requirements: ['cartId' => '\d+', 'itemId' => '\d+'])]
    public function putAction(int $cartId, int $itemId)
    {
        $cartItem = $this->getManager()->findOneBy(['cart' => $cartId, 'id' => $itemId]);

        if ($cartItem) {
            if ($this->processForm($cartItem)) {
                $view = $this->view(null, Response::HTTP_NO_CONTENT);
            } else {
                $view = $this->view($this->getForm(), Response::HTTP_BAD_REQUEST);
            }
        } else {
            $view = $this->view(null, Response::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_UPDATE, ['id' => $itemId, 'entity' => $cartItem]);
    }

    /**
     * Delete cart item.
     *
     * @param int $itemId item id
     * @param int $cartId cart id
     *
     * @ApiDoc(
     *      description="Delete cart item",
     *      resource=true
     * )
     * @return Response
     */
    #[Acl(id: 'oro_magento_cart_item_delete', type: 'entity', class: 'Oro\Bundle\MagentoBundle\Entity\CartItem', permission: 'DELETE')]
    #[Delete(requirements: ['cartId' => '\d+', 'itemId' => '\d+'])]
    public function deleteAction(int $cartId, int $itemId)
    {
        $isProcessed = false;

        $cartItem = $this->getManager()->findOneBy(['cart' => $cartId, 'id' => $itemId]);

        if (!$cartItem) {
            $view = $this->view(null, Response::HTTP_NOT_FOUND);
        } else {
            try {
                $this->getDeleteHandler()->handleDelete($itemId, $this->getManager());

                $isProcessed = true;
                $view        = $this->view(null, Response::HTTP_NO_CONTENT);
            } catch (EntityNotFoundException $e) {
                $view = $this->view(null, Response::HTTP_NOT_FOUND);
            } catch (AccessDeniedException $e) {
                $view = $this->view(['reason' => $e->getMessage()], Response::HTTP_FORBIDDEN);
            }
        }

        return $this->buildResponse($view, self::ACTION_DELETE, ['id' => $itemId, 'success' => $isProcessed]);
    }

    /**
     * @return CartApiEntityManager
     */
    protected function getCartManager()
    {
        return $this->container->get('oro_magento.cart.manager.api');
    }
}
