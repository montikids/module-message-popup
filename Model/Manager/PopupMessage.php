<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\Model\Manager;

use Magento\Framework\Debug;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\CollectionFactory;
use Magento\Framework\Message\ExceptionMessageLookupFactory;
use Magento\Framework\Message\Factory;
use Magento\Framework\Message\Manager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Session;
use Magento\Framework\Phrase;
use Montikids\MessagePopup\Service\Checker\CheckCurrentArea;
use Psr\Log\LoggerInterface;

/**
 * WARNING! Using this class directly not recommended. Please, use @see PopupMessageProxy
 *
 * Manager for popup messages
 * Based on @see Manager
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class PopupMessage implements ManagerInterface
{
    /**
     * Message groups
     */
    public const GROUP_FRONTEND = 'mk_popup_messages_frontend';
    public const GROUP_ADMIN = 'mk_popup_messages_admin';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Factory
     */
    private $messageFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ExceptionMessageLookupFactory
     */
    private $exceptionFactory;

    /**
     * @var CheckCurrentArea
     */
    private $currentArea;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Session $session
     * @param Factory $messageFactory
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     * @param ExceptionMessageLookupFactory $exceptionFactory
     */
    public function __construct(
        Session $session,
        Factory $messageFactory,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger,
        ExceptionMessageLookupFactory $exceptionFactory,
        CheckCurrentArea $currentArea
    ) {
        $this->session = $session;
        $this->messageFactory = $messageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->currentArea = $currentArea;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getMessages($clear = false, $group = null)
    {
        $group = $this->resolveGroup($group);

        /** @var Collection|null $messages */
        $messages = $this->session->getData($group);

        if (null === $messages) {
            $messages = $this->collectionFactory->create();
            $this->session->setData($group, $messages);
        }

        if (true === $clear) {
            $result = clone $messages;
            $messages->clear();
        } else {
            $result = $messages;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultGroup(): string
    {
        if (true === $this->currentArea->isAdmin()) {
            $result = static::GROUP_ADMIN;
        } else {
            $result = static::GROUP_FRONTEND;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function addMessage(MessageInterface $message, $group = null): ManagerInterface
    {
        $group = $group ?? $this->getDefaultGroup();

        $messages = $this->getMessages(false, $group);
        $messages->addMessage($message);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMessages(array $messages, $group = null): ManagerInterface
    {
        foreach ($messages as $message) {
            if ($message instanceof MessageInterface) {
                $this->addMessage($message, $group);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @deprecated Deprecated in the interface
     */
    public function addError($message, $group = null): ManagerInterface
    {
        return $this->addErrorMessage($message, $group);
    }

    /**
     * @inheritDoc
     * @deprecated Deprecated in the interface
     */
    public function addWarning($message, $group = null): ManagerInterface
    {
        return $this->addWarningMessage($message, $group);
    }

    /**
     * @inheritDoc
     * @deprecated Deprecated in the interface
     */
    public function addNotice($message, $group = null): ManagerInterface
    {
        return $this->addNoticeMessage($message, $group);
    }

    /**
     * @inheritDoc
     * @deprecated Deprecated in the interface
     */
    public function addSuccess($message, $group = null): ManagerInterface
    {
        return $this->addSimpleMessage($message, MessageInterface::TYPE_SUCCESS, $group);
    }

    /**
     * @inheritDoc
     */
    public function addErrorMessage($message, $group = null): ManagerInterface
    {
        return $this->addSimpleMessage($message, MessageInterface::TYPE_ERROR, $group);
    }

    /**
     * @inheritDoc
     */
    public function addWarningMessage($message, $group = null): ManagerInterface
    {
        return $this->addSimpleMessage($message, MessageInterface::TYPE_WARNING, $group);
    }

    /**
     * @inheritDoc
     */
    public function addNoticeMessage($message, $group = null): ManagerInterface
    {
        return $this->addSimpleMessage($message, MessageInterface::TYPE_NOTICE, $group);
    }

    /**
     * @inheritDoc
     */
    public function addSuccessMessage($message, $group = null): ManagerInterface
    {
        return $this->addSimpleMessage($message, MessageInterface::TYPE_SUCCESS, $group);
    }

    /**
     * @inheritDoc
     */
    public function addComplexErrorMessage(
        $identifier,
        array $data = [],
        $group = null
    ): ManagerInterface {
        return $this->addComplexMessage($identifier, MessageInterface::TYPE_ERROR, $group, $data);
    }

    /**
     * @inheritDoc
     */
    public function addComplexWarningMessage(
        $identifier,
        array $data = [],
        $group = null
    ): ManagerInterface {
        return $this->addComplexMessage($identifier, MessageInterface::TYPE_WARNING, $group, $data);
    }

    /**
     * @inheritDoc
     */
    public function addComplexNoticeMessage(
        $identifier,
        array $data = [],
        $group = null
    ): ManagerInterface {
        return $this->addComplexMessage($identifier, MessageInterface::TYPE_NOTICE, $group, $data);
    }

    /**
     * @inheritDoc
     */
    public function addComplexSuccessMessage(
        $identifier,
        array $data = [],
        $group = null
    ): ManagerInterface {
        return $this->addComplexMessage($identifier, MessageInterface::TYPE_SUCCESS, $group, $data);
    }

    /**
     * @inheritDoc
     */
    public function addUniqueMessages(array $messages, $group = null): ManagerInterface
    {
        $items = $this->getMessages(false, $group)->getItems();

        foreach ($messages as $message) {
            if ($message instanceof MessageInterface && !in_array($message, $items, false)) {
                $this->addMessage($message, $group);
            }
        }

        return $this;
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
        return $this->addExceptionMessage($exception, $alternativeText, $group);
    }

    /**
     * @inheritDoc
     */
    public function addExceptionMessage(
        \Exception $exception,
        $alternativeText = null,
        $group = null
    ): ManagerInterface {
        $this->logException($exception);

        if (null !== $alternativeText) {
            $this->addErrorMessage($alternativeText, $group);
        } else {
            $message = $this->exceptionFactory->createMessage($exception);
            $this->addMessage($message, $group);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function createMessage($type, $identifier = null): MessageInterface
    {
        $identifier = $this->resolveIdentifier($identifier);

        $result = $this->messageFactory->create($type);
        $result->setIdentifier($identifier);

        return $result;
    }

    /**
     * @param string|Phrase $text
     * @param string $type
     * @param string|null $group
     * @return ManagerInterface
     */
    private function addSimpleMessage($text, string $type, ?string $group): ManagerInterface
    {
        if ($text instanceof Phrase) {
            $text = $text->render();
        }

        $message = $this->messageFactory->create($type, $text);
        $this->addMessage($message, $group);

        return $this;
    }

    /**
     * @param string $identifier
     * @param string $type
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     */
    private function addComplexMessage(
        string $identifier,
        string $type,
        ?string $group = null,
        array $data = []
    ): ManagerInterface {
        $message = $this->createMessage($type, $identifier);
        $message->setData($data);
        $this->addMessage($message, $group);

        return $this;
    }

    /**
     * @param string|null $customGroup
     * @return string
     */
    private function resolveGroup(?string $customGroup): string
    {
        $result = $customGroup ?? static::GROUP_FRONTEND;

        return $result;
    }

    /**
     * @param string|null $identifier
     * @return string
     */
    private function resolveIdentifier(?string $identifier): string
    {
        if (empty($identifier)) {
            $result = MessageInterface::DEFAULT_IDENTIFIER;
        } else {
            $result = $identifier;
        }

        return $result;
    }

    /**
     * @param \Throwable $exception
     */
    private function logException(\Throwable $exception): void
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $trace = Debug::trace($exception->getTrace(), true, true, (bool)getenv('MAGE_DEBUG_SHOW_ARGS'));
        $message = sprintf('Exception message: %s%sTrace: %s', $exception->getMessage(), "\n", $trace);

        $this->logger->critical($message);
    }
}
