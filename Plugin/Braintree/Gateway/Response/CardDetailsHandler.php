<?php

namespace Forter\Braintree\Plugin\Braintree\Gateway\Response;

use Forter\Forter\Model\AbstractApi;
use Forter\Forter\Model\Config;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Response\CardDetailsHandler as OrigCardDetailsHandler;

class CardDetailsHandler
{

    /**
     * A plugin that wraps the 'Magento\Braintree\Gateway\Response\CardDetailsHandler' class.
     * It's purpose is to extract the cc bin from the gateway request and save it on the order object in order to send it to Forter later.
     */

    public function __construct(
        Config $forterConfig,
        SubjectReader $subjectReader,
        AbstractApi $abstractApi
    ) {
        $this->forterConfig = $forterConfig;
        $this->subjectReader = $subjectReader;
        $this->abstractApi = $abstractApi;
    }

    public function beforeHandle(OrigCardDetailsHandler $cardDetailsHandler, array $handlingSubject, array $response)
    {
        try {
            if ($this->forterConfig->isEnabled()) {
                $paymentDO = $this->subjectReader->readPayment($handlingSubject);
                $transaction = $this->subjectReader->readTransaction($response);

                $payment = $paymentDO->getPayment();

                $creditCard = $transaction->creditCard;

                $cc_bin = $creditCard['bin'];
                $name_on_card = $creditCard['cardholderName'];
                $payment->setAdditionalInformation('forter_cc_bin', $cc_bin);
                $payment->setAdditionalInformation('forter_cc_owner', $name_on_card);
            }
        } catch (\Exception $e) {
            $this->abstractApi->reportToForterOnCatch($e);
        }
    }
}
