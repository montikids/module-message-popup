<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\Service\Checker;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;

/**
 * Helps you identify current area
 */
class CheckCurrentArea
{
    /**
     * @var State
     */
    private $state;

    /**
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        $result = (Area::AREA_ADMINHTML === $this->getAreaCode());

        return $result;
    }

    /**
     * @return bool
     */
    public function isFrontend(): bool
    {
        $result = (Area::AREA_FRONTEND === $this->getAreaCode());

        return $result;
    }

    /**
     * @return bool
     */
    public function isCrontab(): bool
    {
        $result = (Area::AREA_CRONTAB === $this->getAreaCode());

        return $result;
    }

    /**
     * @return bool
     */
    public function isApi(): bool
    {
        $areaCode = $this->getAreaCode();
        $result = $this->isRest();
        $result = $result || (Area::AREA_WEBAPI_SOAP === $areaCode);
        $result = $result || (Area::AREA_GRAPHQL === $areaCode);

        return $result;
    }

    /**
     * @return bool
     */
    public function isRest(): bool
    {
        $areaCode = $this->getAreaCode();
        $result = (Area::AREA_WEBAPI_REST === $areaCode);

        return $result;
    }

    /**
     * @return bool
     */
    public function isSet(): bool
    {
        $result = ('' !== $this->getAreaCode());

        return $result;
    }

    /**
     * @return string
     */
    public function getAreaCode(): string
    {
        try {
            /** @var string|null $areaCode */
            $areaCode = $this->state->getAreaCode();
            $result = $areaCode ?? '';
        } catch (\Throwable $t) {
            $result = '';
        }

        return $result;
    }
}
