<?php
namespace App\Command;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class SendStickerToChat extends Command
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
     * SendStickerToChat constructor.
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
            ->setName('bot:send-sticker')

            // the short description shown while running "php bin/console list"
            ->setDescription('Send sticker in chat')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to send sticker')
        ;

        $this->addArgument('chatId', InputArgument::REQUIRED, 'Chat id');
        $this->addArgument('sticker', InputArgument::REQUIRED, 'Sticker key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Sending sticker',
            '============',
            '',
        ]);

        $chatId = $input->getArgument('chatId');
        $sticker = $input->getArgument('sticker');
        $this->sendSticker($chatId, $sticker);
    }

    /**
     * @param int $chatId
     * @param string $sticker
     * @throws \Longman\TelegramBot\Exception\TelegramLogException
     */
    private function sendSticker(int $chatId, string $sticker)
    {
        TelegramLog::initialize($this->logger);
        TelegramLog::initDebugLog($this->kernel->getLogDir() . '/bot_send_sticker.log');

        try {
            \Longman\TelegramBot\Request::sendSticker([
                'chat_id' => $chatId,
                'sticker' => $sticker,
            ]);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}