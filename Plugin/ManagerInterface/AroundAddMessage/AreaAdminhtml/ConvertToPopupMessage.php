<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\Plugin\ManagerInterface\AroundAddMessage\AreaAdminhtml;

use Laminas\Http\Request;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Montikids\MessagePopup\Model\Config;
use Montikids\MessagePopup\Model\Manager\PopupMessage;
use Montikids\MessagePopup\Model\Manager\PopupMessageProxy;

/**
 * Intercepts adding new messages and then transforms them into popup messages if the corresponding settings are enabled
 */
class ConvertToPopupMessage
{
    /**
     * @var PopupMessage
     */
    private $popupMessageManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param PopupMessage $popupMessageManager
     * @param RequestInterface $request
     * @param Config $config
     */
    public function __construct(
        PopupMessage $popupMessageManager,
        RequestInterface $request,
        Config $config
    ) {
        $this->popupMessageManager = $popupMessageManager;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @param ManagerInterface $subject
     * @param \Closure $proceed
     * @param MessageInterface $message
     * @param null $group
     * @return ManagerInterface
     */
    public function aroundAddMessage(
        ManagerInterface $subject,
        \Closure $proceed,
        MessageInterface $message,
        $group = null
    ) {
        if (true === $this->validateSkipPlugin($subject)) {
            return $proceed($message, $group);
        }

        try {
            /** @var Request $request */
            $request = $this->request;
            $isAjax = $request->isXmlHttpRequest();
            $isReplacementEnabled = $this->isReplacementEnabled($message);

            if ((false === $isAjax) && (true === $isReplacementEnabled)) {
                $group = PopupMessage::GROUP_ADMIN;
                $result = $this->popupMessageManager->addMessage($message, $group);
            } else {
                $result = $proceed($message, $group);
            }
        } catch (\Throwable $t) {
            $result = $proceed($message, $group);
        }

        return $result;
    }

    /**
     * @param MessageInterface $message
     * @return bool
     */
    private function isReplacementEnabled(MessageInterface $message): bool
    {
        $result = false;

        switch ($message->getType()) {
            case MessageInterface::TYPE_SUCCESS:
                $result = $this->config->isReplaceAdminSuccessMessagesEnabled();
                break;

            case MessageInterface::TYPE_ERROR:
                $result = $this->config->isReplaceAdminErrorsEnabled();
                break;

            case MessageInterface::TYPE_WARNING:
                $result = $this->config->isReplaceAdminWarningsEnabled();
                break;

            case MessageInterface::TYPE_NOTICE:
                $result = $this->config->isReplaceAdminNoticesEnabled();
                break;
        }

        return $result;
    }

    /**
     * @param ManagerInterface $subject
     * @return bool
     */
    private function validateSkipPlugin(ManagerInterface $subject): bool
    {
        $result = (true === ($subject instanceof PopupMessage));
        $result = $result || (true === ($subject instanceof PopupMessageProxy));

        return $result;
    }
}
