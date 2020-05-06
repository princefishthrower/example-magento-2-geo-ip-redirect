<?php

namespace YourCompany\YourModule\Plugin\Session;

use Magento\Framework\Session\SessionManager;
use Magento\Framework\Session\StorageInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher\ManageStoreCookie;
use Psr\Log\LoggerInterface;
use YourCompany\YourModule\Api\GeolocationServiceInterface;

class SessionManagerPlugin
{
    /**
     * @var GeolocationServiceInterface
     */
    private $geolocationService;
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
    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        ManageStoreCookie $manageStoreCookie,
        UrlInterface $url,
        StoreManagerInterface $storeManager,
        GeolocationServiceInterface $geolocationService,
        StorageInterface $storage,
        LoggerInterface $logger
    )
    {
        $this->geolocationService = $geolocationService;
        $this->storage = $storage;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->manageStoreCookie = $manageStoreCookie;
    }

    /**
     * After plugin for start() function of SessionManager
     * @param SessionManager $subject
     * @param SessionManager $result
     * @return SessionManager
     */
    public function afterStart(SessionManager $subject, SessionManager $result)
    {
        // get stored code from session storage
        $storedStoreCode = $this->storage->getData('store_code');

        // if set, simply continue plugin
        if (isset($storedStoreCode)) {
            return $result;
        }

        // otherwise, get the store code from the geo ip api and map the country
        $storeCode = $this->mapCountryCodeToStoreCode();
        $this->storage->setData('store_code', $storeCode);
        return $result;
    }

    /**
     * Maps a country code (found by geolocation IP) to a corresponding store code
     * @return string
     */
    private function mapCountryCodeToStoreCode(): string {
        $countryCode = $this->geolocationService->getCountryCodeByIp();

        // TODO: as an alternative to this switch statement, there could be mapper class of country code:store code
        // add countries and store codes as needed
        switch($countryCode) {
            case 'US':
                return 'us';
            case 'DE':
                return 'de';
            // ... more cases here ...
            // default, as stated in blog post, should be our US shop
            default:
                return 'us';
        }
    }
}