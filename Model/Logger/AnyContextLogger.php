<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\Model\Logger;

use Psr\Log\LogLevel;

/**
 * Simplifies and makes more standard logging exceptions/errors/whatever
 *
 * Extent it in case you want to set main context (@see static::CONTEXT)
 * Use it directly if you don't need it (context is empty by default)
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AnyContextLogger
{
    use ContextLoggerTrait;

    /**
     * @var string
     */
    public const CONTEXT = '';

    /**
     * @return string
     */
    protected function getMainContext(): string
    {
        return static::CONTEXT;
    }

    /**
     * @param \Throwable $throwable
     * @param string $message
     * @param array $tags
     * @param bool $extended
     * @param bool $trace
     * @param string $logLevel
     */
    public function logException(
        \Throwable $throwable,
        string $message,
        array $tags = [],
        bool $extended = false,
        bool $trace = false,
        string $logLevel = LogLevel::ERROR
    ): void {
        $this->genericLogException($tags, $throwable, $message, [], $extended, $trace, $logLevel);
    }

    /**
     * @param \Throwable $throwable
     * @param string $message
     * @param array $tags
     */
    public function logExceptionAsEmergency(\Throwable $throwable, string $message, array $tags = []): void
    {
        $this->genericLogExceptionAsEmergency($tags, $throwable, $message);
    }

    /**
     * @param \Throwable $throwable
     * @param string $message
     * @param array $tags
     */
    public function logExceptionAsCritical(\Throwable $throwable, string $message, array $tags = []): void
    {
        $this->genericLogExceptionAsCritical($tags, $throwable, $message);
    }

    /**
     * @param \Throwable $throwable
     * @param string $message
     * @param array $tags
     */
    public function logExceptionAsError(\Throwable $throwable, string $message, array $tags = []): void
    {
        $this->genericLogExceptionAsError($tags, $throwable, $message);
    }

    /**
     * @param \Throwable $throwable
     * @param string $message
     * @param array $tags
     */
    public function logExceptionAsWarning(\Throwable $throwable, string $message, array $tags = []): void
    {
        $this->genericLogExceptionAsWarning($tags, $throwable, $message);
    }

    /**
     * @param \Throwable $throwable
     * @param string $message
     * @param array $tags
     */
    public function logExceptionAsNotice(\Throwable $throwable, string $message, array $tags = []): void
    {
        $this->genericLogExceptionAsNotice($tags, $throwable, $message);
    }

    /**
     * @param string $message
     * @param array $tags
     */
    public function logEmergency(string $message, array $tags = []): void
    {
        $this->genericLogEmergency($tags, $message);
    }

    /**
     * @param string $message
     * @param array $tags
     */
    public function logCritical(string $message, array $tags = []): void
    {
        $this->genericLogCritical($tags, $message);
    }

    /**
     * @param string $message
     * @param array $tags
     */
    public function logError(string $message, array $tags = []): void
    {
        $this->genericLogError($tags, $message);
    }

    /**
     * @param string $message
     * @param array $tags
     */
    public function logWarning(string $message, array $tags = []): void
    {
        $this->genericLogWarning($tags, $message);
    }

    /**
     * @param string $message
     * @param array $tags
     */
    public function logNotice(string $message, array $tags = []): void
    {
        $this->genericLogNotice($tags, $message);
    }

    /**
     * @param string $message
     * @param array $tags
     */
    public function logInfo(string $message, array $tags = []): void
    {
        $this->genericLogInfo($tags, $message);
    }

    /**
     * @param array $tags
     * @param string $level
     * @param array $partsToLog
     * @param array $extraTags
     */
    protected function log(array $tags, string $level, array $partsToLog, array $extraTags = []): void
    {
        $message = implode(' ', $partsToLog);
        $tags = $this->prepareTags($tags, $extraTags);

        $this->logger->log($level, $message, $tags);
    }

    /**
     * @param array $tags
     * @param array $extraTags
     * @return array
     */
    protected function prepareTags(array $tags, array $extraTags): array
    {
        $result = [];
        $mainContext = $this->getMainContext();

        if (!empty($mainContext)) {
            $result[] = $mainContext;
        }

        $result = array_merge($result, $tags);
        $result = array_merge($result, $extraTags);

        return $result;
    }
}
