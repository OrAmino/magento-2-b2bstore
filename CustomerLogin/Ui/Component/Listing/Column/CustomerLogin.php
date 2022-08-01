<?php

namespace Orienteed\CustomerLogin\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Orienteed\CustomerLogin\Helper\Data;

class CustomerLogin extends Column
{
    const URL_STORE_FRONT_PATH = 'orienteedcustomerlogin/login/login';

    protected $urlBuilder;
    protected $_helper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->_helper = $helper;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($this->_helper->isEnabled() && isset($item['entity_id'])) {
                    $item[$this->getData('name')] = [
                        'storeFrontUrl' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_STORE_FRONT_PATH,
                                [
                                    'id' => $item['entity_id'],
                                ]
                            ),
                            'target' => '_blank',
                            'label' => __('Login as PWA Customer'),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }

    public function prepare()
    {
        if (!$this->_helper->isEnabled()) {
            $this->_data['config']['componentDisabled'] = true;
        }
        parent::prepare();
    }
}
