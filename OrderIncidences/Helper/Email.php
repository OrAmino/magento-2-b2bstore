<?php

namespace Orienteed\OrderIncidences\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Area;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ORIENTEED_EMAILS_GENERAL_TEMPLATE         = 'orienteed_emails/general/template';
    const TRANS_EMAIL_IDENT_SUPPORT_EMAIL           = "trans_email/ident_support/email";
    const TRANS_EMAIL_IDENT_SUPPORT_NAME            = "trans_email/ident_support/name";
    const ORIENTEED_EMAILS_GENERAL_ORDERIND_SUBJECT = "orienteed_emails/general/orderind_subject";
    const MEDIA_ORDER_INCIDENCES_PATH               = "order/incidences/";

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    protected $logger;

    /**
     * @var File
     */
    protected $_reader;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * __construct
     *
     * @param Context $context
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $fileSystem
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        File $reader,
        Filesystem $fileSystem
    ) {
        parent::__construct($context);
        $this->scopeConfig       = $scopeConfig;
        $this->storeManager      = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper           = $escaper;
        $this->transportBuilder  = $transportBuilder;
        $this->logger            = $context->getLogger();
        $this->_reader           = $reader;
        $this->_fileSystem       = $fileSystem;
    }

    public function sendEmail($name, $email, $phone, $incidences, $orderNumber)
    {
        $emailTemplate = $this->getConfigValue(self::ORIENTEED_EMAILS_GENERAL_TEMPLATE);
        $senderEmail   = $this->getConfigValue(self::TRANS_EMAIL_IDENT_SUPPORT_EMAIL);
        $senderName    = $this->getConfigValue(self::TRANS_EMAIL_IDENT_SUPPORT_NAME);
        $emailSubject  = $this->getConfigValue(self::ORIENTEED_EMAILS_GENERAL_ORDERIND_SUBJECT) ? $this->getConfigValue(self::ORIENTEED_EMAILS_GENERAL_ORDERIND_SUBJECT) : "";

        $mediaPath       = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $attachmentPath  = $mediaPath . self::MEDIA_ORDER_INCIDENCES_PATH;
        $attachmentFiles = [];

        if (count($incidences)) {
            if (!file_exists($attachmentPath)) {
                mkdir($attachmentPath, 0777, true);
            }

            foreach ($incidences as $_incidences) {
                if (isset($_incidences['images'])) {
                    foreach ($_incidences['images'] as $images) {
                        $data = explode(',', $images['base64']);
                        $output_file = $attachmentPath . $images['name'];
                        $ifp = fopen($output_file, 'wb');
                        fwrite($ifp, base64_decode($data[1]));
                        fclose($ifp);
                        $attachmentFiles[$images['name']] = ['filename' => $images['name'], 'filepath' => $attachmentPath . $images['name']];
                    }
                }
            }
        }

        try {
            $this->inlineTranslation->suspend();
            $sender = [
                'name'  => $this->escaper->escapeHtml($senderName),
                'email' => $this->escaper->escapeHtml($senderEmail),
            ];
            $transport = $this->transportBuilder;
            $transport = $this->transportBuilder->setTemplateIdentifier($emailTemplate);
            $transport = $this->transportBuilder->setTemplateOptions(
                [
                    'area'  => Area::AREA_FRONTEND,
                    'store' => Store::DEFAULT_STORE_ID
                ]
            );
            $transport = $this->transportBuilder->setTemplateVars([
                'emailSubject' => $emailSubject,
                'name'         => $name,
                'email'        => $email,
                'phone'        => $phone,
                'incidences'   => $incidences,
                'orderNumber'  => $orderNumber,
            ]);
            $transport = $this->transportBuilder->setFrom($sender);
            $transport = $this->transportBuilder->addTo($senderEmail);
            if (count($attachmentFiles)) {
                foreach ($attachmentFiles as $attachfile) {
                    if (isset($attachfile['filename']) && isset($attachfile['filepath'])) {
                        $transport = $this->transportBuilder->addAttachment($this->_reader->fileGetContents($attachfile['filepath']), $attachfile['filename']);
                    }
                }
            }
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    public function getStoreUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
}
