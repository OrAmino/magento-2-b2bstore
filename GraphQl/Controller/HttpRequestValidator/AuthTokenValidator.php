<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Controller\HttpRequestValidator;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Controller\HttpRequestValidatorInterface;

/**
 * Processes the "Auth Token" header entry
 */
class AuthTokenValidator implements HttpRequestValidatorInterface
{
    const UNAUTHORIZE_OPERATIONS = [
        'getLocale',
        'isRequiredLogin',
        'getStoreConfigData',
        'getAvailableStoresData',
        'getCurrencyData',
        'getConfigurableThumbnailSource',
        'getRootCategoryId',
        'createCart',
        'GetNavigationMenu',
        'getStoreName',
        'storeConfigData',
        'getItemCount',
        'MiniCartQuery',
        'checkUserIsAuthed',
        'SignIn',
        'getUrlResolverData',
        'CreateAccount',
        'requestPasswordResetEmail',
        'ResolveURL',
        'GetCmsPage',
        'SignInAfterCreate',
        'beCustomerSendMail',
        'nonCustomerSendMail',
        'resetPassword'
    ];

    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $jsonSerializer,
        \Orienteed\RequiredLogin\Helper\Data $helper
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->helper = $helper;
    }

    /**
     * Handle the mandatory application/json header
     *
     * @param HttpRequestInterface $request
     * @return void
     * @throws GraphQlInputException
     */
    public function validate(HttpRequestInterface $request): void
    {
        $headerName = 'authorization';
        $requiredHeaderValue = 'Bearer';

        $storeHeaderName = 'store';
        $headerStoreValue = (string)$request->getHeader($storeHeaderName);

        $data = [];
        if ($request->isPost()) {
            $data = $this->jsonSerializer->unserialize($request->getContent());
        } else if ($request->isGet()) {
            $data = $request->getParams();
        } else {
            return;
        }

        $headerValue = (string)$request->getHeader($headerName);

        if (
            $this->helper->isLoginRequired($headerStoreValue)
            && strpos($headerValue, $requiredHeaderValue) === false
            && !in_array($data['operationName'], self::UNAUTHORIZE_OPERATIONS)
        ) {
            throw new GraphQlInputException(
                new \Magento\Framework\Phrase('Request authorization must be Bearer.')
            );
        }
    }
}
