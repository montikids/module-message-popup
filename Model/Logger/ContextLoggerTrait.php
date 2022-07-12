<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\Model\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * TODO: Try to move the code into an abstract class when we start supporting PHP 7.4+ only
 * @see https://www.php.net/manual/en/language.oop5.variance.php
 */
trait ContextLoggerTrait
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $message
     * @param array $tags
     * @param string $level
     */
    public function logCustom(string $message, array $tags = [], string $level = LogLevel::ERROR): void
    {
        array_unshift($tags, $this->getMainContext());
        $this->logger->log($level, $message, $tags);
    }

    /**
     * @param string $message
     * @param \Throwable $exception
     * @param array $tags
     * @param bool $extended
     * @param bool $trace
     * @param string $logLevel
     */
    public function logCustomException(
        string $message,
        \Throwable $exception,
        array $tags = [],
        bool $extended = false,
        bool $trace = false,
        string $logLevel = LogLevel::ERROR
    ): void {
        $this->logNonEntityExceptionInfo($exception, $message, $tags, $extended, $trace, $logLevel);
    }

    /**
     * @return string
     */
    abstract protected function getMainContext(): string;

    /**
     * @param mixed $context
     * @param \Throwable $throwable
     * @param string $message
     * @param bool $extended
     * @param bool $trace
     * @param string $logLevel
     */
    protected function genericLogException(
        $context,
        \Throwable $throwable,
        string $message,
        array $extraTags,
        bool $extended = false,
        bool $trace = false,
        string $logLevel = LogLevel::ERROR
    ): void {
        $partsToLog = [];
        $partsToLog[] = "{$this->trim($message)}.";
        $partsToLog[] = "Error: {$this->trim($throwable->getMessage())}.";

        if (true === $extended) {
            $partsToLog[] = "File: {$throwable->getFile()}. Line: {$throwable->getLine()}.";
        }

        if (true === $trace) {
            $partsToLog[] = "Trace: {$throwable->getTraceAsString()}.";
        }

        $this->log($context, $logLevel, $partsToLog, $extraTags);
    }

    /**
     * @param mixed $context
     * @param \Throwable $throwable
     * @param string $message
     * @param array $extraTags
     */
    protected function genericLogExceptionAsEmergency(
        $context,
        \Throwable $throwable,
        string $message,
        array $extraTags = []
    ): void {
        $this->genericLogException($context, $throwable, $message, $extraTags, true, true, LogLevel::EMERGENCY);
    }

    /**
     * @param mixed $context
     * @param \Throwable $throwable
     * @param string $message
     * @param array $extraTags
     */
    protected function genericLogExceptionAsCritical(
        $context,
        \Throwable $throwable,
        string $message,
        array $extraTags = []
    ): void {
        $this->genericLogException($context, $throwable, $message, $extraTags, true, false, LogLevel::CRITICAL);
    }

    /**
     * @param mixed $context
     * @param \Throwable $throwable
     * @param string $message
     * @param array $extraTags
     */
    protected function genericLogExceptionAsError(
        $context,
        \Throwable $throwable,
        string $message,
        array $extraTags = []
    ): void {
        $this->genericLogException($context, $throwable, $message, $extraTags);
    }

    /**
     * @param mixed $context
     * @param \Throwable $throwable
     * @param string $message
     * @param array $extraTags
     */
    protected function genericLogExceptionAsWarning(
        $context,
        \Throwable $throwable,
        string $message,
        array $extraTags = []
    ): void {
        $this->genericLogException($context, $throwable, $message, $extraTags, false, false, LogLevel::WARNING);
    }

    /**
     * @param mixed $context
     * @param \Throwable $throwable
     * @param string $message
     * @param array $extraTags
     */
    protected function genericLogExceptionAsNotice(
        $context,
        \Throwable $throwable,
        string $message,
        array $extraTags = []
    ): void {
        $this->genericLogException($context, $throwable, $message, $extraTags, false, false, LogLevel::NOTICE);
    }

    /**
     * @param mixed $context
     * @param string $message
     * @param array $extraTags
     */
    protected function genericLogEmergency($context, string $message, array $extraTags = []): void
    {
        $partsToLog[] = "{$this->trim($message)}.";
        $this->log($context, LogLevel::EMERGENCY, $partsToLog, $extraTags);
    }

    /**
     * @param mixed $context
     * @param string $message
     * @param array $extraTags
     */
    protected function genericLogCritical($context, string $message, array $extraTags = []): void
    {
        $partsToLog[] = "{$this->trim($message)}.";
        $this->log($context, LogLevel::CRITICAL, $partsToLog, $extraTags);
    }

    /**
     * @param mixed $context
     * @param string $message
     * @param array $extraTags
     */
    protected function genericLogError($context, string $message, array $extraTags = []): void
    {
        $partsToLog[] = "{$this->trim($message)}.";
        $this->log($context, LogLevel::ERROR, $partsToLog, $extraTags);
    }

    /**
     * @param mixed $context
     * @param string $message
     * @param array $extraTags
     */
    protected function genericLogWarning($context, string $message, array $extraTags = []): void
    {
        $partsToLog[] = "{$this->trim($message)}.";
        $this->log($context, LogLevel::WARNING, $partsToLog, $extraTags);
    }

    /**
     * @param mixed $context
     * @param string $message
     * @param array $extraTags
     */
    protected function genericLogNotice($context, string $message, array $extraTags = []): void
    {
        $partsToLog[] = "{$this->trim($message)}.";
        $this->log($context, LogLevel::NOTICE, $partsToLog, $extraTags);
    }

    /**
     * @param mixed $context
     * @param string $message
     * @param array $extraTags
     */
    protected function genericLogInfo($context, string $message, array $extraTags = []): void
    {
        $partsToLog[] = "{$this->trim($message)}.";
        $this->log($context, LogLevel::INFO, $partsToLog, $extraTags);
    }

    /**
     * @param mixed $context
     * @param string $level
     * @param array $partsToLog
     * @param array $extraTags
     */
    protected function log($context, string $level, array $partsToLog, array $extraTags = []): void
    {
        $this->genericLog($context, $level, $partsToLog, $extraTags);
    }

    /**
     * @param \Throwable $throwable
     * @param string $message
     * @param array $tags
     * @param bool $extended
     * @param bool $trace
     * @param string $logLevel
     */
    private function logNonEntityExceptionInfo(
        \Throwable $throwable,
        string $message,
        array $tags = [],
        bool $extended = false,
        bool $trace = false,
        string $logLevel = LogLevel::ERROR
    ): void {
        $partsToLog = [];
        $partsToLog[] = "{$this->trim($message)}.";
        $partsToLog[] = "Error: {$this->trim($throwable->getMessage())}.";

        if (true === $extended) {
            $partsToLog[] = "File: {$throwable->getFile()}. Line: {$throwable->getLine()}.";
        }

        if (true === $trace) {
            $partsToLog[] = "Trace: {$throwable->getTraceAsString()}.";
        }

        $this->genericLog($tags, $logLevel, $partsToLog);
    }

    /**
     * @param mixed $context
     * @param string $level
     * @param array $partsToLog
     * @param array $extraTags
     */
    private function genericLog($context, string $level, array $partsToLog, array $extraTags = []): void
    {
        $message = implode(' ', $partsToLog);

        if (is_scalar($context)) {
            $tags = [$this->getMainContext(), $context];
        } elseif (is_array($context)) {
            $tags = array_merge([$this->getMainContext()], $context);
        } else {
            $tags = [$this->getMainContext()];
        }

        $tags = array_merge($tags, $extraTags);
        $this->logger->log($level, $message, $tags);
    }

    /**
     * @param string $message
     * @return string
     */
    private function trim(string $message, string $extraChars = '.'): string
    {
        $charsToTrim = "{$extraChars}\t\n\r\0\x0B";
        $result = trim($message, $charsToTrim);

        return $result;
    }
}
