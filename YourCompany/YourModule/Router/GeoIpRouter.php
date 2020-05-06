<?php

namespace YourCompany\YourModule\Router;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Session\StorageInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher\ManageStoreCookie;
use Psr\Log\LoggerInterface;

class GeoIpRouter implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ManageStoreCookie
     */
    private $manageStoreCookie;
    /**
     * @var StorageInterface
     */
    private $storage;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ManageStoreCookie $manageStoreCookie,
        StoreManagerInterface $storeManager,
        StorageInterface $storage,
        ActionFactory $actionFactory,
        LoggerInterface $logger
    )
    {
        $this->actionFactory = $actionFactory;
        $this->storage = $storage;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->manageStoreCookie = $manageStoreCookie;
    }

    /**
     * For any page, ensure current store is set properly
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        // get store code from store_code storage value
        $storeCode = $this->storage->getData('store_code');

        // Loop at all stores until (or if!) we find a matching store code
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            if ($store->getCode() === $storeCode) {
                $this->storeManager->setCurrentStore($store);
                break;
            }
        }

        return null;
    }
}