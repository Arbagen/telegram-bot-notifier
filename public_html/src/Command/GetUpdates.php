<?php
namespace App\Command;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function __construct($name = null, Telegram $telegramService, LoggerInterface $logger)
    {
        parent::__construct($name);
        $this->telegramService = $telegramService;
        $this->logger = $logger;
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Updating',
            '============',
            '',
        ]);

        $this->getUpdates();
    }

    private function getUpdates()
    {
        $mysql_credentials = [
            'host'     => '172.21.0.2',
            'user'     => 'root',
            'password' => 'pwd',
            'database' => 'telegram-notifier',
        ];

        try {
            $this->telegramService->enableMySql($mysql_credentials);

            $this->telegramService->handleGetUpdates();
            $this->logger->info('updated');
        } catch (TelegramException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}