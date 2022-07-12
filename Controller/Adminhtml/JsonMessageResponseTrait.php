<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\Controller\Adminhtml;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Webapi\Exception as HttpException;

/**
 * Contains some methods that help to prepare AJAX JSON responses that contain messages should be shown in popups
 *
 * @property ResultFactory $resultFactory
 */
trait JsonMessageResponseTrait
{
    /**
     * @param string $message
     * @param string|null $label
     * @return Json
     */
    protected function prepareSuccessResponse(string $message, ?string $label = null): Json
    {
        $body = [
            'success' => true,
            'mk_message' => [
                'type' => 'success',
                'text' => $message,
                'label' => $label,
            ],
        ];

        $result = $this->prepareResponseFromArray($body);

        return $result;
    }

    /**
     * @param string $message
     * @param string|null $label
     * @return Json
     */
    protected function prepareWarningResponse(string $message, ?string $label = null): Json
    {
        $body = [
            'success' => true,
            'mk_message' => [
                'type' => 'warning',
                'text' => $message,
                'label' => $label,
            ],
        ];

        $result = $this->prepareResponseFromArray($body);

        return $result;
    }

    /**
     * @param string $message
     * @param \Throwable|null $exception
     * @param string|null $label
     * @return Json
     */
    protected function prepareErrorResponse(string $message, ?\Throwable $exception, ?string $label = null): Json
    {
        if (null !== $exception) {
            $message = "$message. Error: {$exception->getMessage()}";
        }

        $body = [
            'success' => false,
            'mk_message' => [
                'type' => 'error',
                'text' => $message,
                'label' => $label,
            ],
        ];

        $result = $this->prepareResponseFromArray($body);

        return $result;
    }

    /**
     * @param string $message
     * @param string|null $label
     * @return Json
     */
    protected function prepareBadRequestResponse(string $message, ?string $label = null): Json
    {
        $body = [
            'success' => false,
            'mk_message' => [
                'type' => 'error',
                'text' => $message,
                'label' => $label,
            ],
        ];

        $result = $this->prepareResponseFromArray($body, HttpException::HTTP_BAD_REQUEST);

        return $result;
    }

    /**
     * @param array $body
     * @param int $status
     * @return Json
     */
    private function prepareResponseFromArray(array $body, int $status = 200): Json
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($body);
        $resultJson->setHttpResponseCode($status);

        return $resultJson;
    }
}
