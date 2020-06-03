<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerAgreement\Plugin\Indexer\Agreement\Save;

use Divante\VsbridgeIndexerAgreement\Model\Indexer\AgreementProcessor;
use Magento\CheckoutAgreements\Model\Agreement;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement as ResourceModelAgreement;

class UpdateAgreement
{

    /**
     * @var AgreementProcessor
     */
    private $agreementProcessor;

    /**
     * @var ResourceModelAgreement
     */
    private $resourceModelAgreement;

    /**
     * Save constructor.
     *
     * @param AgreementProcessor $agreementProcessor
     * @param ResourceModelAgreement $resourceModelAgreement
     */
    public function __construct(AgreementProcessor $agreementProcessor, ResourceModelAgreement $resourceModelAgreement)
    {
        $this->agreementProcessor = $agreementProcessor;
        $this->resourceModelAgreement = $resourceModelAgreement;
    }

    /**
     * @param Agreement $agreement
     * @param Agreement $result
     *
     * @return Agreement
     */
    public function afterAfterSave(Agreement $agreement, Agreement $result): Agreement
    {
        $this->resourceModelAgreement->addCommitCallback(function () use ($agreement) {
            $this->agreementProcessor->reindexRow($agreement->getId());
        });

        return $result;
    }

    /**
     * @param Agreement $agreement
     * @param Agreement $result
     *
     * @return Agreement
     */
    public function afterAfterDeleteCommit(Agreement $agreement, Agreement $result): Agreement
    {
        $this->agreementProcessor->reindexRow($agreement->getId());

        return $result;
    }
}
