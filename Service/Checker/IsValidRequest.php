<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\Service\Checker;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;

/**
 * Checks whether the request is valid to receive the messages cookie
 *
 * Invalid request examples:
 * - browser plugin requested robots.txt
 * - bad media url site.com/page/{{media_url}}/img.jpg
 */
class IsValidRequest
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var bool|null
     */
    private $isValid;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        if (null === $this->isValid) {
            /** @var Http $request */
            $request = $this->request;

            $path = trim($request->getPathInfo(), '/');
            $isRobotTxtRequest = ($path === 'robots.txt');
            $isBadMediaRequest = (strpos($path, 'jpg') > 0) || (strpos($path, 'png') > 0);
            $isInvalid = ((true === $isRobotTxtRequest) || (true === $isBadMediaRequest));

            $this->isValid = (false === $isInvalid);
        }

        return $this->isValid;
    }
}
