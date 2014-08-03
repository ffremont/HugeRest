<?php

namespace Huge\Rest\Utils4Test;

class Log4phpLogger implements \Psr\Log\LoggerInterface {

    private $logger;

    public function __construct($logger = 'main') {
        $this->logger = \Logger::getLogger($logger);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array()) {
        $this->logger->fatal($this->interpolate($message, $context));
    }

    /**
     * Action must be taken immediately.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array()) {
        $this->logger->fatal($this->interpolate($message, $context));
    }

    /**
     * Critical conditions.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array()) {
        $this->logger->fatal($this->interpolate($message, $context));
    }

    /**
     * Runtime errors that do not require immediate action but should
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array()) {
        $this->logger->error($this->interpolate($message, $context));
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array()) {
        $this->logger->warn($this->interpolate($message, $context));
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array()) {
        $this->logger->info($this->interpolate($message, $context));
    }

    /**
     * Interesting events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array()) {
        $this->logger->info($this->interpolate($message, $context));
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array()) {
        $this->logger->debug($this->interpolate($message, $context));
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array()) {
        throw new Exception('Please call specific logging message');
    }

    /**
     * Interpolates context values into the message placeholders.
     * Taken from PSR-3's example implementation.
     */
    protected function interpolate($message, array $context = array()) {
        // build a replacement array with braces around the context
        // keys
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

}

