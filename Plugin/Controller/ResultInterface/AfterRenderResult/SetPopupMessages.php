<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\Plugin\Controller\ResultInterface\AfterRenderResult;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Translate\Inline\ParserInterface;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Montikids\MessagePopup\Model\Logger\AnyContextLogger;
use Montikids\MessagePopup\Service\Checker\CheckCurrentArea;
use Montikids\MessagePopup\Model\Config;
use Montikids\MessagePopup\Model\Manager\PopupMessage as PopupMessageManager;
use Montikids\MessagePopup\Service\Checker\IsValidRequest;

/**
 * Moves popup messages from session to the cookie
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class SetPopupMessages
{
    /**
     * Cookie name for events
     */
    public const COOKIE_NAME_FRONTEND = 'mk-popup-messages';
    public const COOKIE_NAME_ADMIN = 'mk-popup-messages-admin';

    /**
     * Message field keys
     */
    private const KEY_TYPE = 'type';
    private const KEY_TEXT = 'text';
    private const KEY_TITLE = 'title';

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var PopupMessageManager
     */
    private $messageManager;

    /**
     * @var InterpretationStrategyInterface
     */
    private $interpreter;

    /**
     * @var InlineInterface
     */
    private $inlineTranslate;

    /**
     * @var AnyContextLogger
     */
    private $logger;

    /**
     * @var CheckCurrentArea
     */
    private $checkCurrentArea;

    /**
     * @var IsValidRequest
     */
    private $isValidRequest;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $metadataFactory
     * @param JsonSerializer $serializer
     * @param PopupMessageManager $messageManager
     * @param InterpretationStrategyInterface $interpreter
     * @param InlineInterface $inlineTranslate
     * @param AnyContextLogger $logger
     * @param CheckCurrentArea $checkCurrentArea
     * @param IsValidRequest $isValidRequest
     * @param Config $config
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $metadataFactory,
        JsonSerializer $serializer,
        PopupMessageManager $messageManager,
        InterpretationStrategyInterface $interpreter,
        InlineInterface $inlineTranslate,
        AnyContextLogger $logger,
        CheckCurrentArea $checkCurrentArea,
        Config $config,
        IsValidRequest $isValidRequest
    ) {
        $this->cookieManager = $cookieManager;
        $this->metadataFactory = $metadataFactory;
        $this->serializer = $serializer;
        $this->messageManager = $messageManager;
        $this->interpreter = $interpreter;
        $this->inlineTranslate = $inlineTranslate;
        $this->logger = $logger;
        $this->checkCurrentArea = $checkCurrentArea;
        $this->isValidRequest = $isValidRequest;
        $this->config = $config;
    }

    /**
     * Updates the cookie after each non-AJAX request
     *
     * @param ResultInterface $subject
     * @param ResultInterface $result
     * @return ResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRenderResult(ResultInterface $subject, ResultInterface $result): ResultInterface
    {
        if ((false === $this->checkIsEnabled()) || (false === $this->isValidRequest->check())) {
            return $result;
        }

        if (false === ($subject instanceof Json)) {
            $this->setPopupMessagesCookie();
        }

        return $result;
    }

    /**
     * Sets events to the cookie
     *
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    private function setPopupMessagesCookie(): void
    {
        if (true === $this->config->isEnabledFrontend()) {
            try {
                $frontendMessages = $this->getFrontendMessages();
                $this->setCookieMessages($frontendMessages, static::COOKIE_NAME_FRONTEND);
            } catch (\Throwable $t) {
                $this->logger->logExceptionAsCritical($t, "Can't set the cookie", [static::COOKIE_NAME_FRONTEND]);
            }
        }

        if (true === $this->config->isEnabledAdmin()) {
            try {
                $adminMessages = $this->getAdminMessages();
                $this->setCookieMessages($adminMessages, static::COOKIE_NAME_ADMIN);
            } catch (\Throwable $t) {
                $this->logger->logExceptionAsCritical($t, "Can't set the cookie", [static::COOKIE_NAME_ADMIN]);
            }
        }
    }

    /**
     * @param array $messages
     * @param string $cookieName
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws InputException
     */
    private function setCookieMessages(array $messages, string $cookieName): void
    {
        if (false === empty($messages)) {
            $this->prepareMessages($messages);

            $metadata = $this->metadataFactory->createPublicCookieMetadata();
            $metadata->setDurationOneYear();
            $metadata->setPath('/');
            $metadata->setHttpOnly(false);

            try {
                $serializedMessages = $this->serializer->serialize($messages);
            } catch (\Throwable $t) {
                $serializedMessages = '';
            }

            $this->cookieManager->setPublicCookie($cookieName, $serializedMessages, $metadata);
        }
    }

    /**
     * Returns messages that should be displayed in frontend area
     *
     * @return array
     */
    private function getFrontendMessages(): array
    {
        $cookieMessages = $this->getCookieMessages(static::COOKIE_NAME_FRONTEND);
        $sessionMessages = $this->getSessionMessages(PopupMessageManager::GROUP_FRONTEND);
        $maxPoolSize = $this->config->getPoolSize();

        $result = array_merge($cookieMessages, $sessionMessages);

        if (0 !== $maxPoolSize) {
            $result = array_slice($result, 0, $maxPoolSize);
        }

        return $result;
    }

    /**
     * Returns messages that should be displayed in admin area
     *
     * @return array
     */
    private function getAdminMessages(): array
    {
        $cookieMessages = $this->getCookieMessages(static::COOKIE_NAME_ADMIN);
        $sessionMessages = $this->getSessionMessages(PopupMessageManager::GROUP_ADMIN);
        $maxPoolSize = $this->config->getPoolSize();

        $result = array_merge($cookieMessages, $sessionMessages);

        if (0 !== $maxPoolSize) {
            $result = array_slice($result, 0, $maxPoolSize);
        }

        return $result;
    }

    /**
     * Returns messages already stored in the cookie
     *
     * @param string $cookieName
     * @return array
     */
    private function getCookieMessages(string $cookieName): array
    {
        $result = [];
        $messages = $this->cookieManager->getCookie($cookieName);

        if (false === empty($messages)) {
            try {
                $messages = $this->serializer->unserialize($messages);
            } catch (\Throwable $t) {
                $this->logger->logExceptionAsError($t, "Can't unserialize cookie content", [$cookieName]);
            }

            if (is_array($messages)) {
                $result = $messages;
            }
        }

        return $result;
    }

    /**
     * @param string $group
     * @return array
     */
    private function getSessionMessages(string $group): array
    {
        $result = [];
        $sessionMessages = $this->messageManager->getMessages(true, $group);

        foreach ($sessionMessages->getItems() as $message) {
            $result[] = [
                self::KEY_TYPE => $message->getType(),
                self::KEY_TITLE => ucfirst($message->getType()),
                self::KEY_TEXT => $this->interpreter->interpret($message),
            ];
        }

        return $result;
    }

    /**
     * @param array $messages
     */
    private function prepareMessages(array &$messages): void
    {
        if (true === $this->inlineTranslate->isAllowed()) {
            foreach ($messages as &$message) {
                $message[self::KEY_TEXT] = $this->convertMessageText($message[self::KEY_TEXT]);
            }
        }
    }

    /**
     * Copied from @param string $text
     * @return string
     * @see \Magento\Theme\Controller\Result\MessagePlugin::convertMessageText
     *
     */
    private function convertMessageText(string $text): string
    {
        if (preg_match('#' . ParserInterface::REGEXP_TOKEN . '#', $text, $matches)) {
            $text = $matches[1];
        }

        return $text;
    }

    /**
     * @return bool
     */
    private function checkIsEnabled(): bool
    {
        $result = false;

        if (true === $this->checkCurrentArea->isAdmin()) {
            $result = $this->config->isEnabledAdmin();
        } elseif (true === $this->checkCurrentArea->isFrontend()) {
            $result = $this->config->isEnabledFrontend();
        }

        return $result;
    }
}
