<?php

namespace Orienteed\SendEmailsFromRegistrationForms\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Orienteed\SendEmailsFromRegistrationForms\Helper\Data;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Stdlib\DateTime;

class BeCustomerSendMail implements ResolverInterface
{
    /**
     * @var Data
     */
    private $_helper;

    /**
     * @var CustomerFactory
     */
    private $_customerFactory;

    /**
     * __construct
     *
     * @param Data $helper
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Data $helper,
        CustomerFactory $customerFactory,
        \Magento\Framework\Math\Random $random,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_helper          = $helper;
        $this->_customerFactory = $customerFactory;
        $this->random = $random;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->customerRegistry = $customerRegistry;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['email']) || '' == trim($args['email'])) {
            throw new GraphQlInputException(__('Specify the "email" value.'));
        }

        if (!isset($args['nif']) || '' == trim($args['nif'])) {
            throw new GraphQlInputException(__('Specify the "nif" value.'));
        }

        if (!isset($args['no_of_client']) || '' == trim($args['no_of_client'])) {
            throw new GraphQlInputException(__('Specify the "no_of_client" value.'));
        }

        $email      = $args['email'];
        $nif        = $args['nif'];
        $noOfClient = $args['no_of_client'];

        $post  = [
            'email'        => $email,
            'nif'          => $nif,
            'no_of_client' => $noOfClient,
            'rp_token'     => '',
            'name'         => '',
            'redirect_url' => $this->_helper->getBaseUrl()
        ];

        $customer = $this->getFilteredCustomersCollection($email, $noOfClient, $nif);

        if ($customer) {
            if ($customer->getRpToken()) {
                $post['rp_token'] = $customer->getRpToken();
            }
            if ($customer->getFirstname()) {
                $post['name'] = $customer->getFirstname();
            }
        }

        if ($post['rp_token'] != '') {
            if ($redirectUrl = $this->getRedirectUrl($post['rp_token'])) {
                $post['redirect_url'] = $redirectUrl;
            }

            $beCustomertemplate = $this->_helper->getConfig(Data::BE_CUSTOMER_EMAIL_TEMPLATE);
            if ($this->_helper->sendEmail($post, $beCustomertemplate, 'user')) {
                $result = ['error' => false, 'message' => __('Email sent successfully.')];
            } else {
                $result = ['error' => true, 'message' => __('Something went wrong while sending email. Please try again after some time.')];
            }
        } else {
            $result = ['error' => true, 'message' => __('Customer not found.')];
        }

        return $result;
    }

    public function getFilteredCustomersCollection($customerEmail, $clientNumber, $vatId)
    {
        $customerCollection = $this->_customerFactory->create()->getCollection();
        $customerCollection->addAttributeToSelect("*");
        $customerCollection->addAttributeToFilter("email", array("eq" => $customerEmail));
        $customerCollection->addAttributeToFilter("client_number", array("eq" => $clientNumber));
        $customerCollection->addAttributeToFilter("taxvat", array("eq" => $vatId));

        if (count($customerCollection)) {
            $customer = $customerCollection->getFirstItem();
            $customerRep = $this->customerRepository->get($customer->getEmail());

            // Generate RP Token for customer and set
            $newPasswordToken = $this->random->getUniqueHash();
            $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
            $customerSecure->setRpToken($newPasswordToken);
            $limitTokenDate = $this->dateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT);
            $customerSecure->setRpTokenCreatedAt($limitTokenDate);
            $this->customerRepository->save($customerRep);

            $customer->setRpToken($newPasswordToken);

            return $customer;
        }

        return;
    }

    public function getRedirectUrl($rpToken)
    {
        $redirectUrl = "";
        if ($redirectUrlConfig = $this->_helper->getConfig(Data::ACTION_REDIRECT_URL_CONFIG)) {
            $redirectUrl = $redirectUrlConfig . "customer/account/createPassword/?token=" . $rpToken;
        }

        return $redirectUrl;
    }
}
