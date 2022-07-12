<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\Model\Manager;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\ObjectManager\NoninterceptableInterface;
use Montikids\MessagePopup\Service\Checker\CheckCurrentArea;
use Montikids\MessagePopup\Model\Config;

/**
 * Proxies @see PopupMessage in order to call whether the popup manager methods or the default one
 * It allows to re-route messages if you decide to turn off the module
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class PopupMessageProxy implements ManagerInterface, NoninterceptableInterface
{
    /**
     * @var PopupMessage
     */
    private $popupManager;

    /**
     * @var ManagerInterface
     */
    private $defaultManager;

    /**
     * @var CheckCurrentArea
     */
    private $currentArea;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param PopupMessage $popupManager
     * @param ManagerInterface $defaultManager
     * @param CheckCurrentArea $currentArea
     * @param Config $config
     */
    public function __construct(
        PopupMessage $popupManager,
        ManagerInterface $defaultManager,
        CheckCurrentArea $currentArea,
        Config $config
    ) {
        $this->popupManager = $popupManager;
        $this->defaultManager = $defaultManager;
        $this->currentArea = $currentArea;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getMessages($clear = false, $group = null)
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function getDefaultGroup(): string
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addMessage(MessageInterface $message, $group = null): ManagerInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addMessages(array $messages, $group = null): ManagerInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     * @deprecated Deprecated in the interface
     */
    public function addError($message, $group = null): ManagerInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     * @deprecated Deprecated in the interface
     */
    public function addWarning($message, $group = null): ManagerInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     * @deprecated Deprecated in the interface
     */
    public function addNotice($message, $group = null): ManagerInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     * @deprecated Deprecated in the interface
     */
    public function addSuccess($message, $group = null): ManagerInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addErrorMessage($message, $group = null): ManagerInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addWarningMessage($message, $group = null): ManagerInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addNoticeMessage($message, $group = null): ManagerInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addSuccessMessage($message, $group = null): ManagerInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addComplexErrorMessage(
        $identifier,
        array $data = [],
        $group = null
    ): ManagerInterface {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addComplexWarningMessage(
        $identifier,
        array $data = [],
        $group = null
    ): ManagerInterface {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addComplexNoticeMessage(
        $identifier,
        array $data = [],
        $group = null
    ): ManagerInterface {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addComplexSuccessMessage(
        $identifier,
        array $data = [],
        $group = null
    ): ManagerInterface {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addUniqueMessages(array $messages, $group = null): ManagerInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     * @deprecated Deprecated in the interface
     */
    public function addException(
        \Exception $exception,
        $alternativeText = null,
        $group = null
    ): ManagerInterface {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function addExceptionMessage(
        \Exception $exception,
        $alternativeText = null,
        $group = null
    ): ManagerInterface {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function createMessage($type, $identifier = null): MessageInterface
    {
        return $this->process(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    private function process(string $method, array $arguments)
    {
        $manager = $this->getSubject();
        $result = $manager->$method(...$arguments);

        return $result;
    }

    /**
     * @return ManagerInterface
     */
    private function getSubject(): ManagerInterface
    {
        if (true === $this->checkEnabledForCurrentArea()) {
            $result = $this->popupManager;
        } else {
            $result = $this->defaultManager;
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function checkEnabledForCurrentArea(): bool
    {
        $result = false;

        if (true === $this->currentArea->isFrontend()) {
            $result = $this->config->isEnabledFrontend();
        } elseif (true === $this->currentArea->isAdmin()) {
            $result = $this->config->isEnabledAdmin();
        }

        return $result;
    }
}
