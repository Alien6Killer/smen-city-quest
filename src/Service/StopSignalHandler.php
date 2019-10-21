<?php
declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * @package App\Service
 */
class StopSignalHandler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int|null
     */
    private $signal;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Initialize
     * @return void
     */
    public function init(): void
    {
        $this->initialized = true;
        $this->registerAsync();
        $this->registerSigHandlers();
    }

    /**
     * Enable asynchronous signal handling
     * No need to call pcntl_signal_dispatch()
     */
    private function registerAsync()
    {
        pcntl_async_signals(true);
    }

    /**
     * @param int $signo
     */
    private function handlePcntlSignal(int $signo)
    {
        $this->signal = $signo;
        $this->logger->info('Caught signal '.$signo);
    }

    /**
     * Register stop signal handlers
     */
    private function registerSigHandlers()
    {
        $privateHandlerProxy = function (int $signo) {
            $this->handlePcntlSignal($signo);
        };
        pcntl_signal(SIGTERM, $privateHandlerProxy);
        pcntl_signal(SIGINT, $privateHandlerProxy);
        pcntl_signal(SIGQUIT, $privateHandlerProxy);
    }

    /**
     * @return bool True if stop signal is handled
     */
    public function hasSignal()
    {
        $this->assertInitialized();

        return !is_null($this->signal);
    }

    /**
     * @throws \LogicException
     */
    private function assertInitialized()
    {
        if (!$this->initialized) {
            throw new \LogicException('Handler must be initialized');
        }
    }
}
