<?php
namespace App\Command;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class SendMessageToChat
 * @package App\Command
 */
class SendMessageToChat extends Command
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
     * @var KernelInterface
     */
    private $kernel;

    /**
     * SendMessageToChat constructor.
     * @param null $name
     * @param Telegram $telegramService
     * @param LoggerInterface $logger
     * @param KernelInterface $kernel
     */
    public function __construct(
        $name = null,
        Telegram $telegramService,
        LoggerInterface $logger,
        KernelInterface $kernel
    ) {
        parent::__construct($name);
        $this->telegramService = $telegramService;
        $this->logger = $logger;
        $this->kernel = $kernel;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('bot:send-message')

            // the short description shown while running "php bin/console list"
            ->setDescription('Send message in chat')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to send message')
        ;

        $this->addArgument('chatId', InputArgument::REQUIRED, 'Chat id');
        $this->addArgument('message', InputArgument::REQUIRED, 'Message');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Sending message',
            '============',
            '',
        ]);

        $chatId = $input->getArgument('chatId');
        $message = $input->getArgument('message');
        $this->sendMessage($chatId, $message);
    }

    private function sendMessage(int $chatId, $message)
    {
        TelegramLog::initialize($this->logger);
        TelegramLog::initDebugLog($this->kernel->getLogDir() . '/bot_send_message.log');
        try {
            \Longman\TelegramBot\Request::sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
            ]);
        } catch (TelegramException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}