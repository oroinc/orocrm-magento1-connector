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
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Manager\CustomerApiEntityManager;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * API CRUD controller for customer Address entity.
 */
#[NamePrefix(['value' => 'oro_api_'])]
class CustomerAddressController extends RestController implements ClassResourceInterface
{
    /**
     * Get all addresses items.
     *
     * @ApiDoc(
     *      description="Get all addresses items",
     *      resource=true
     * )
     * @param int $customerId
     * @return JsonResponse
     */
    #[AclAncestor('oro_magento_customer_view')]
    #[Get(requirements: ['customerId' => '\d+'])]
    public function cgetAction(int $customerId)
    {
        /** @var Customer $customer */
        $customer = $this->getCustomerManager()->find($customerId);
        $result   = [];

        if ($customer instanceof Customer) {
            $items = $customer->getAddresses();

            foreach ($items as $item) {
                $result[] = $this->getPreparedItem($item);
            }
        }

        return new JsonResponse(
            $result,
            empty($customer) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * Add address to the customer.
     *
     * @ApiDoc(
     *      description="Add address to the customer",
     *      resource=true
     * )
     * @param int $customerId
     * @return JsonResponse
     */
    #[AclAncestor('oro_magento_customer_create')]
    #[Post(requirements: ['customerId' => '\d+'])]
    public function postAction(int $customerId)
    {
        /** @var Customer $customer */
        $customer    = $this->getCustomerManager()->find($customerId);
        $isProcessed = false;
        $entity      = new Address();

        if ($customer instanceof Customer) {
            $entity = $this->processForm($entity, $customer);
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
     * Get customer address.
     *
     * @param int $addressId
     * @param int $customerId
     *
     * @ApiDoc(
     *      description="Get customer address",
     *      resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_magento_customer_view')]
    #[Get(requirements: ['customerId' => '\d+', 'addressId' => '\d+'])]
    public function getAction(int $customerId, int $addressId)
    {
        $address = $this->getManager()->serializeElement($customerId, $addressId);

        return new JsonResponse(
            $address,
            empty($address) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * Update customer address.
     *
     * @param int $addressId  address item id
     * @param int $customerId customer item id
     *
     * @ApiDoc(
     *      description="Update customer address",
     *      resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_magento_customer_update')]
    #[Put(requirements: ['customerId' => '\d+', 'addressId' => '\d+'])]
    public function putAction(int $customerId, int $addressId)
    {
        $address = $this->getManager()->findOneBy(['owner' => $customerId, 'id' => $addressId]);

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
     * Delete customer address.
     *
     * @param int $addressId  address item id
     * @param int $customerId customer item id
     *
     * @ApiDoc(
     *      description="Delete customer address",
     *      resource=true
     * )
     * @return Response
     */
    #[Acl(id: 'oro_magento_customer_delete', type: 'entity', class: 'Oro\Bundle\MagentoBundle\Entity\Address', permission: 'DELETE')]
    #[Delete(requirements: ['customerId' => '\d+', 'addressId' => '\d+'])]
    public function deleteAction(int $customerId, int $addressId)
    {
        $isProcessed = false;
        $address     = $this->getManager()->findOneBy(['owner' => $customerId, 'id' => $addressId]);

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
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('oro_magento.customer_address.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('oro_magento.form.customer_address.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_magento.form.api.handler.customer_address');
    }

    /**
     * @return CustomerApiEntityManager
     */
    protected function getCustomerManager()
    {
        return $this->container->get('oro_magento.customer.manager.api');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity, $resultFields = [])
    {
        // convert addresses to plain array
        $addressTypesData = [];

        /** @var $entity AbstractTypedAddress */
        foreach ($entity->getTypes() as $addressType) {
            $addressTypesData[] = parent::getPreparedItem($addressType);
        }

        $result                = parent::getPreparedItem($entity);
        $result['types']       = $addressTypesData;
        $result['countryIso2'] = $entity->getCountryIso2();
        $result['countryIso3'] = $entity->getCountryIso3();
        $result['regionCode']  = $entity->getRegionCode();
        $result['country'] = $entity->getCountryName();

        unset($result['owner']);

        return $result;
    }
}
