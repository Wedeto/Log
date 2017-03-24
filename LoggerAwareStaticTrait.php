<?php

namespace WASP\Log;

/**
 * Static variant of LoggerAwareTrait
 */
trait LoggerAwareStaticTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected static $logger;

    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger = null)
    {
        if ($logger === null)
            $logger = LoggerFactory::getLogger([static::class]);
        self::$logger = $logger;
    }
}
