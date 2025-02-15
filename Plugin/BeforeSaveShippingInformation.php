<?php

namespace SendCloud\SendCloud\Plugin;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\PaymentDetails;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\QuoteRepository;
use SendCloud\SendCloud\Helper\Checkout;
use SendCloud\SendCloud\Logger\SendCloudLogger;

/**
 * Class BeforeSaveShippingInformation
 */
class BeforeSaveShippingInformation
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var Checkout
     */
    private $helper;

    /**
     * BeforeSaveShippingInformation constructor.
     * @param RequestInterface $request
     * @param QuoteRepository $quoteRepository
     * @param Checkout $helper
     */
    public function __construct(
        RequestInterface $request,
        QuoteRepository $quoteRepository,
        Checkout $helper
    ) {
        $this->request = $request;
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param PaymentDetails $paymentDetails
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return PaymentDetails
     */
    public function afterSaveAddressInformation(ShippingInformationManagement $subject, PaymentDetails $paymentDetails, $cartId, ShippingInformationInterface $addressInformation)
    {
        $extensionAttributes = $addressInformation->getExtensionAttributes();

        if ($this->helper->checkForScriptUrl() && $extensionAttributes != null && $this->helper->checkIfModuleIsActive()) {
            $spId = $extensionAttributes->getSendcloudServicePointId();
            $spName = $extensionAttributes->getSendcloudServicePointName();
            $spStreet = $extensionAttributes->getSendcloudServicePointStreet();
            $spHouseNumber = $extensionAttributes->getSendcloudServicePointHouseNumber();
            $spZipCode = $extensionAttributes->getSendcloudServicePointZipCode();
            $spCity = $extensionAttributes->getSendcloudServicePointCity();
            $spCountry = $extensionAttributes->getSendcloudServicePointCountry();
            $spPostnumber = $extensionAttributes->getSendcloudServicePointPostnumber();

            $quote = $this->quoteRepository->getActive($cartId);
            $quote->setSendcloudServicePointId($spId);
            $quote->setSendcloudServicePointName($spName);
            $quote->setSendcloudServicePointStreet($spStreet);
            $quote->setSendcloudServicePointHouseNumber($spHouseNumber);
            $quote->setSendcloudServicePointZipCode($spZipCode);
            $quote->setSendcloudServicePointCity($spCity);
            $quote->setSendcloudServicePointCountry($spCountry);
            $quote->setSendcloudServicePointPostnumber($spPostnumber);
            $this->quoteRepository->save($quote);
        }
        return $paymentDetails;
    }
}
