<?php
namespace App\Command;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->setName('bot:send-message')

            // the short description shown while running "php bin/console list"
            ->setDescription('Send message in chat')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to send message')
        ;

        $this->addArgument('chat_id', InputArgument::REQUIRED, 'Chat id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Sending message',
            '============',
            '',
        ]);

        $chatId = $input->getArgument('chat_id');
        $this->sendMessage($chatId);
    }

    private function sendMessage(int $chatId)
    {
        try {
            \Longman\TelegramBot\Request::sendChatAction(['chat_id' => 162758256, 'action' => 'typing']);

            $messageResponse = \Longman\TelegramBot\Request::sendMessage([
                'chat_id' => $chatId,
                'text' => 'English lesson is starting in 10 minutes 😱',
            ]);

            $stickerResponse = \Longman\TelegramBot\Request::sendSticker([
                'chat_id' => $chatId,
                'sticker' => 'CAADAgADTgQAAmvEygo8YG7sYoE4WgI',
            ]);
            $this->logger->info($messageResponse->toJson());
            $this->logger->info($stickerResponse->toJson());
        } catch (TelegramException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}