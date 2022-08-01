<?php

namespace Orienteed\SendEmailsFromRegistrationForms\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Orienteed\SendEmailsFromRegistrationForms\Helper\Data;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class NonCustomerSendMail implements ResolverInterface
{

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->_helper = $helper;
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

        $nonCustomertemplate = $this->_helper->getConfig(Data::NON_CUSTOMER_EMAIL_TEMPLATE);
        if ($this->_helper->sendEmail($args, $nonCustomertemplate, 'admin')) {
            $result = ['error' => false, 'message' => __('Email sent successfully.')];
        } else {
            $result = ['error' => true, 'message' => __('Something went wrong while sending email. Please try again after some time.')];
        }

        return $result;
    }
}
