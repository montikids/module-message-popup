<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Montikids\MessagePopup\Model\Config;

/**
 * Provides access to config values
 */
class ConfigProvider implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function isEnabledFrontend(): bool
    {
        $result = $this->config->isEnabledFrontend();

        return $result;
    }

    /**
     * @return bool
     */
    public function isEnabledAdmin(): bool
    {
        $result = $this->config->isEnabledAdmin();

        return $result;
    }
}
