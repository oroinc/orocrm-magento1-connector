<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Guzzle\Http\Exception\CurlException;
use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use Oro\Bundle\MagentoBundle\Exception\RuntimeException;
use Oro\Bundle\MagentoBundle\Utils\ValidationUtils;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Magento Integration Config Controller
 */
class IntegrationConfigController extends AbstractController
{
    /**
     * @return JsonResponse
     */
    #[Route(path: '/check', name: 'oro_magento_integration_check', methods: ['POST'])]
    #[AclAncestor('oro_integration_update')]
    #[CsrfProtection]
    public function checkAction()
    {
        $handler = $this->container->get('oro_magento.handler.transport');

        try {
            $response = $handler->getCheckResponse();
        } catch (\Exception $e) {
            $response = $this->logErrorAndGetResponse($e);
        }

        return new JsonResponse($response);
    }

    /**
     * @param \Exception $e
     * @return array
     */
    protected function logErrorAndGetResponse(\Exception $e)
    {
        if ($e instanceof TransportException
            || $e instanceof CurlException
        ) {
            $this->logDebugException($e);

            return $this->createFailResponse(
                $this->container->get('translator')->trans('oro.magento.controller.not_valid_parameters')
            );
        }

        if ($e instanceof ExtensionRequiredException) {
            $this->logDebugException($e);

            return $this->createFailResponse(
                $this->container->get('translator')->trans('oro.magento.controller.extension_required')
            );
        }

        if ($e instanceof RuntimeException) {
            $this->logCriticalException($e);

            return $this->createFailResponse(
                $this->container->get('translator')->trans('oro.magento.controller.transport_error')
            );
        }

        $this->logCriticalException($e);

        return $this->createFailResponse(
            $this->container->get('translator')->trans('oro.magento.controller.not_valid_parameters')
        );
    }

    /**
     * @param \Exception $exception
     */
    protected function logDebugException(\Exception $exception)
    {
        $message = ValidationUtils::sanitizeSecureInfo($exception->getMessage());
        $this->container->get('logger')->debug(sprintf('MageCheck error: %s: %s', $exception->getCode(), $message));
    }

    /**
     * @param \Exception $exception
     */
    protected function logCriticalException(\Exception $exception)
    {
        $message = ValidationUtils::sanitizeSecureInfo($exception->getMessage());
        $this->container->get('logger')->critical(sprintf('MageCheck error: %s: %s', $exception->getCode(), $message));
    }

    /**
     * @param string    $message
     *
     * @return array
     */
    protected function createFailResponse($message)
    {
        $response = [
            'success'      => false,
            'errorMessage' => $message
        ];

        return $response;
    }
}
