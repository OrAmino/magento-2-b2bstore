<?php

namespace Orienteed\OrderIncidences\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Orienteed\OrderIncidences\Helper\Email;

class EmailSent implements ResolverInterface
{
    private $helperEmail;

    public function __construct(
        Email $helperEmail
    ) {
        $this->helperEmail = $helperEmail;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['input'])) {
            throw new GraphQlInputException(__('Invalid parameter list.'));
        }

        $input = $args['input'];

        if (
            !isset($input['order_number']) || !isset($input['name']) || !isset($input['email']) || !isset($input['phone']) || !isset($input['incidences']) || empty($input['order_number']) || empty($input['name']) || empty($input['email']) || empty($input['phone'])
        ) {
            throw new GraphQlInputException(__('Invalid parameter list.'));
        }

        try {
            $orderNumber = $input['order_number'];
            $name  = $input['name'];
            $email = $input['email'];
            $phone = $input['phone'];
            $incidences = $input['incidences'];
            $this->helperEmail->sendEmail($name, $email, $phone, $incidences, $orderNumber);
            $response = [
                'message' => __("Email send."),
                'status' => true
            ];
        } catch (\Exception $e) {
            $response = [
                'message' => __("Something wrong."),
                'status' => false
            ];
        }

        return $response;
    }
}
