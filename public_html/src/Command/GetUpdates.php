<?php
namespace App\Command;

use App\Kernel;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class GetUpdates
 * @package App\Command
 */
class GetUpdates extends Command
{
    /**
     * @var Telegram
     */
    private $telegramService;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Kernel
     */
    private $kernel;

    /** @var array  */
    private $mysqlCredentials;

    /**
     * GetUpdates constructor.
     * @param null $name
     * @param Telegram $telegramService
     * @param LoggerInterface $logger
     * @param KernelInterface $kernel
     */
    public function __construct($name = null, Telegram $telegramService, LoggerInterface $logger, KernelInterface $kernel)
    {
        parent::__construct($name);
        $this->telegramService = $telegramService;
        $this->logger = $logger;
        $this->kernel = $kernel;
        $this->mysqlCredentials = [
            'host'     => getenv('DB_HOST'),
            'user'     => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'database' => getenv('DB_NAME'),
        ];
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('bot:get-updates')

            // the short description shown while running "php bin/console list"
            ->setDescription('Getting updates')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to get updates')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Longman\TelegramBot\Exception\TelegramLogException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Updating',
            '============',
            '',
        ]);

        $this->getUpdates();
        $output->writeln([
            'OK',
        ]);
    }

    /**
     * @throws \Longman\TelegramBot\Exception\TelegramLogException
     */
    private function getUpdates()
    {
        TelegramLog::initialize($this->logger);
        TelegramLog::initDebugLog($this->kernel->getLogDir() . '/bot_updates.log');

        try {
            $this->telegramService->enableMySql($this->mysqlCredentials);

            $response = $this->telegramService->handleGetUpdates();
            $this->logger->info(sprintf('Updated. %s', $response->toJson()));
            $this->logger->info('updated');
        } catch (TelegramException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}