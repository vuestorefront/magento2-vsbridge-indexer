<?php

namespace Divante\VsbridgeIndexerAgreement\Model\Indexer\Action;

use Divante\VsbridgeIndexerAgreement\Model\ResourceModel\Agreement as AgreementResource;
use Divante\VsbridgeIndexerCore\Indexer\RebuildActionInterface;

/**
 * Class Agreement
 */
class Agreement implements RebuildActionInterface
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
    public function rebuild(int $storeId, array $agreementIds): \Traversable
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
