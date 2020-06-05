<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerAgreement\Model\Indexer\Action;


use Divante\VsbridgeIndexerAgreement\Model\ResourceModel\Agreement as AgreementResource;

class Agreement
{
    /**
     * @var AgreementResource
     */
    private $resourceModel;

    /**
     * Agreement constructor.
     *
     * @param AgreementResource $agreementResource
     */
    public function __construct(AgreementResource $agreementResource)
    {
        $this->resourceModel = $agreementResource;
    }

    /**
     * @param int $storeId
     * @param array $agreementIds
     *
     * @return \Traversable
     */
    public function rebuild($storeId = 1, array $agreementIds = [])
    {
        $lastAgreementId = 0;

        do {
            $agreements = $this->resourceModel->loadAgreements($storeId, $agreementIds, $lastAgreementId);

            foreach ($agreements as $agreement) {
                $lastAgreementId = (int) $agreement['agreement_id'];
                $agreement['id'] = $lastAgreementId;
                $agreement['title'] = $agreement['name'];
                $agreement['content'] = (string) $agreement['content'];
                $agreement['active'] = (bool) $agreement['is_active'];
                $agreement['is_html'] = (bool) $agreement['is_html'];
                $agreement['mode'] = (int) $agreement['mode'];

                unset($agreement['name'], $agreement['is_active'], $agreement['agreement_id']);

                yield $lastAgreementId => $agreement;
            }
        } while (!empty($agreements));
    }
}
