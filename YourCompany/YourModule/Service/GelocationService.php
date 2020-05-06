<?php

namespace YourCompany\YourModule\Service;

use Exception;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\HTTP\Client\CurlFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\HTTP\Client\Curl;
use YourCompany\YourModule\Api\GeolocationServiceInterface;

class GeolocationService implements GeolocationServiceInterface
{
    /**
     * @var DirectoryList
     */
    private $dir;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * LocationRepository constructor.
     *
     * @param CurlFactory $curlFactory
     * @param ResourceConnection $resourceConnection
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        DirectoryList $dir,
        LoggerInterface $logger,
        CurlFactory $curlFactory,
        ResourceConnection $resourceConnection
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->curlFactory = $curlFactory;
        $this->logger = $logger;
        $this->dir = $dir;
    }

    public function getCountryCodeByIp(): string
    {
        $ipAddress = $this->getClientIp();
        $this->logger->debug('IP address is: ' . $ipAddress);
        if ($ipAddress !== 'UNKNOWN') {
            return $this->getCountryCodeFromIpStack($ipAddress);
        }
        return '';
    }

    /**
    * @param string $ipAddress
    * @return string
    */
    private function getCountryCodeFromIpStack(string $ipAddress): string
    {
        $requestUrl = 'http://api.ipstack.com/' . $ipAddress . '?access_key=YOUR_ACCESS_KEY_HERE&fields=country_code';

        /** @var Curl $curl */
        $curl = $this->curlFactory->create();
        $curl->setTimeout(5);
        try {
            $curl->get($requestUrl);
            $body = $curl->getBody();
            $response = json_decode($body, true);
            if(isset($response['country_code'])) {
                $this->logger->debug($body);
                return strtoupper($response['country_code']);
            }
        } catch (Exception $ex) {
            return '';
        }
    }

    private function getClientIp()
    {
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}