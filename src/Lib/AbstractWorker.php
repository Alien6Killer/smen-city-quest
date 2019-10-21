<?php
declare(strict_types=1);

namespace App\Lib;

use App\Service\StopSignalHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use const DATE_W3C;
use Throwable;

/**
 * @package App\Lib
 */
abstract class AbstractWorker extends Command implements LoggerAwareInterface
{
    /**
     * @var string
     */
    protected $workerNamePrefix = 'worker:';

    /**
     * @var int
     */
    protected $defaultUSleep = 1e6; // 1 second

    /**
     * @var bool
     */
    protected $stop = false;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var RegistryInterface
     */
    private $doctrineRegistry;

    /**
     * @var array|EntityManagerInterface[]
     */
    private $entityManagers = [];

    /**
     * @var CacheInterface
     */
    private $arrayCache;

    /**
     * @var StopSignalHandler
     */
    private $stopSignalHandler;

    /**
     * AbstractWorker constructor.
     */
    public function __construct()
    {
        parent::__construct($this->workerNamePrefix.$this->getWorkerName());
    }

    /**
     *  Configure command
     */
    final protected function configure()
    {
        $this
            ->setDescription($this->getWorkerDescription())
            ->addOption('once', null, InputOption::VALUE_OPTIONAL, 'Run worker once', false);

        $this->configureWorker();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->stopSignalHandler->init();
        $once = $input->getOption('once');
        if (!$once) {
            $output->writeln($this->formatOutputMessage('Start working...'));
        }
        while (!$this->isStopped()) {
            if ($once) {
                $this->stop = true;
            }
            try {
                $this->refreshDatabaseConnection();
                $this->work($input, $output);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage(), ['exception' => $e]);
                $this->stop = true;
            }
            if ($this->isStopped()) {
                break;
            }
            $this->clearArrayCache();
            $this->clearEntityManagers();
            usleep((int)$this->defaultUSleep);
        }
        $output->writeln($this->formatOutputMessage('Finished!'));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    abstract protected function work(InputInterface $input, OutputInterface $output): void;

    /**
     * Return worker name
     * @return string
     */
    abstract protected function getWorkerName(): string;

    /**
     * Return worker description
     * @return string
     */
    abstract protected function getWorkerDescription(): string;

    /**
     * Set additional argument/options to worker
     */
    abstract protected function configureWorker(): void;

    /**
     * @param string $message
     * @return string
     */
    protected function formatOutputMessage(string $message): string
    {
        return sprintf('[%s] %s', date(DATE_W3C), $message);
    }

    /**
     * @return bool
     */
    private function isStopped(): bool
    {
        return $this->stop || $this->stopSignalHandler->hasSignal();
    }

    /**
     * Clear array cache
     */
    private function clearArrayCache(): void
    {
        $this->arrayCache->clear();
    }

    /**
     * Clear entity managers
     */
    private function clearEntityManagers(): void
    {
        foreach ($this->entityManagers as $em) {
            $em->clear();
        }
    }

    /**
     * @return void
     */
    private function refreshDatabaseConnection(): void
    {
        foreach ($this->entityManagers as $em) {
            $conn = $em->getConnection();
            $conn->getConfiguration()->setSQLLogger(null);
            $refresh = true;
            try {
                $refresh = false === $conn->ping();
            } catch (Throwable $e) {
                $this->logger->info($e->getMessage());
            } finally {
                if ($refresh) {
                    $conn->close();
                    $conn->connect();
                }
            }
        }
    }

    /**
     * @required
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @required
     * @param RegistryInterface $doctrineRegistry
     */
    public function setDoctrineRegistry(RegistryInterface $doctrineRegistry): void
    {
        $this->entityManagers = $doctrineRegistry->getManagers();
        $this->doctrineRegistry = $doctrineRegistry;
    }

    /**
     * @param StopSignalHandler $signalHandler
     * @required
     */
    public function setStopSignalHandler(StopSignalHandler $signalHandler)
    {
        $this->stopSignalHandler = $signalHandler;
    }
}
