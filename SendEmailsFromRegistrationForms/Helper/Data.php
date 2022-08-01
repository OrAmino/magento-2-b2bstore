<?php

namespace Orienteed\SendEmailsFromRegistrationForms\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const GENERAL_CONTACT_SENDER_NAME  = "trans_email/ident_general/name";
    const GENERAL_CONTACT_SENDER_EMAIL = "trans_email/ident_general/email";
    const BE_CUSTOMER_EMAIL_TEMPLATE   = 'orienteed_emails/general/becustomer';
    const NON_CUSTOMER_EMAIL_TEMPLATE  = 'orienteed_emails/general/noncustomer';
    const ACTION_REDIRECT_URL_CONFIG   = 'orienteed_emails/general/redirecturl';

    /**
     * @param ScopeConfigInterface $_scopeConfig
     */
    protected $_scopeConfig;

    /**
     * @param StoreManagerInterface $_storeManager
     */
    protected $_storeManager;

    /**
     * @param StateInterface $_stateInterface
     */
    protected $_stateInterface;

    /**
     * @param TransportBuilder $_transportBuilder
     */
    protected $_transportBuilder;

    /**
     * @param LoggerInterface $_loggerInterface
     */
    protected $_loggerInterface;

    public function __construct(
        ScopeConfigInterface $_scopeConfig,
        StoreManagerInterface $storeManager,
        StateInterface $stateInterface,
        TransportBuilder $transportBuilder,
        LoggerInterface $loggerInterface
    ) {
        $this->_scopeConfig      = $_scopeConfig;
        $this->_storeManager     = $storeManager;
        $this->_stateInterface   = $stateInterface;
        $this->_transportBuilder = $transportBuilder;
        $this->_loggerInterface  = $loggerInterface;
    }

    /**
     * Retrieve config value
     *
     * @return string
     */
    public function getConfig($config)
    {
        return $this->_scopeConfig->getValue(
            $config,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function sendEmail($params, $template, $receiver)
    {
        try {
            $this->_stateInterface->suspend();
            $storeScope  = ScopeInterface::SCOPE_STORE;
            $sentToEmail = $this->_scopeConfig->getValue(self::GENERAL_CONTACT_SENDER_EMAIL, $storeScope);
            $sentToName  = $this->_scopeConfig->getValue(self::GENERAL_CONTACT_SENDER_NAME, $storeScope);

            $senderEmail = $sentToEmail;
            $senderName = $sentToName;

            if ($receiver == "user") {
                if (isset($params['email'])) {
                    if ($params['email']) {
                        $senderEmail = $params['email'];
                    }
                }

                if (isset($params['name'])) {
                    if ($params['name']) {
                        $senderName = $params['name'];
                    }
                }
            }

            $sender = [
                'email' => $senderEmail,
                'name'  => $sentToName
            ];

            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions([
                    'area'  => 'frontend',
                    'store' => Store::DEFAULT_STORE_ID,
                ])
                ->setTemplateVars($params)
                ->setFrom($sender)
                ->addTo($senderEmail, $senderName)
                ->getTransport();

            try {
                $transport->sendMessage();
                $this->_stateInterface->resume();

                return true;
            } catch (\Exception $e) {
                $this->_loggerInterface->debug($e->getMessage());
            }

            return false;
        } catch (\Exception $e) {
            $this->_loggerInterface->debug($e->getMessage());
        }
    }

    public function getBaseUrl()
    {
        $this->_storeManager->getStore()->getBaseUrl();
    }
}
