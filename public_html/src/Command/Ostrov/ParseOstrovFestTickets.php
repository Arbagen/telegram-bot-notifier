<?php
namespace App\Command\Ostrov;

use App\Kernel;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\CssSelector\Exception\ParseException;
use Symfony\Component\HttpKernel\KernelInterface;
use PHPHtmlParser\Dom;

/**
 * Class GetUpdates
 * @package App\Command
 */
class ParseOstrovFestTickets extends Command
{
    /** @var Telegram */
    private $telegramService;

    /** @var LoggerInterface */
    private $logger;

    /** @var Kernel */
    private $kernel;

    /** @var string  */
    private $uri = "https://ostrovfestival2018.ticketforevent.com/";

    /** @var Dom */
    private $domParser;

    /** @var int  */
    private $chatId;

    /**
     * GetUpdates constructor.
     * @param null $name
     * @param Telegram $telegramService
     * @param LoggerInterface $logger
     * @param KernelInterface $kernel
     * @param Dom $domParser
     */
    public function __construct(
        $name = null,
        Telegram $telegramService,
        LoggerInterface $logger,
        KernelInterface $kernel,
        Dom $domParser
    ) {
        parent::__construct($name);
        $this->telegramService = $telegramService;
        $this->logger = $logger;
        $this->kernel = $kernel;
        $this->domParser = $domParser;
        $this->chatId = -1001280053194;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('bot:ostrov-tickets')

            // the short description shown while running "php bin/console list"
            ->setDescription('Getting ticket info')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to get updates about ostrov fest tickets')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Updating',
            '============',
            '',
        ]);
        try {
            $this->parseTicketsForEvent();
        } catch (TelegramException $e) {
            $this->logger->error($e->getMessage());
        }
        $output->writeln(['OK']);
    }

    /**
     * @throws TelegramException
     */
    private function parseTicketsForEvent()
    {
        $availableClassName="epts-td-available";
        $priceClassName="epts-sum";

        $this->domParser->loadFromUrl($this->uri);
        $availableCollection = $this->domParser->getElementsByClass(sprintf('td.%s', $availableClassName));
        $sumCollection = $this->domParser->getElementsByClass(sprintf('td.%s', $priceClassName));

        try {
            if (empty($availableCollection)) {
                throw new ParseException(sprintf("Can't find tag by class '%s'", $availableClassName));
            }
            if (empty($sumCollection[0])) {
                throw new ParseException(sprintf("Can't find tag by class '%s'", $priceClassName));
            }

            $ticketCount = (int) $availableCollection[0]->text;
            $ticketPrice = (int) $sumCollection[0]->text;

            $message = sprintf("На данный момент доступно билетов: %d шт. Цена %s грн", $ticketCount, $ticketPrice);

            try {
                \Longman\TelegramBot\Request::sendMessage([
                    'chat_id' => $this->chatId,
                    'text' => $message,
                ]);
            } catch (TelegramException $e) {
                $this->logger->error($e->getMessage());
            }
        } catch (ParseException $exception) {
            \Longman\TelegramBot\Request::sendMessage([
                'chat_id' => $this->chatId,
                'text' => sprintf('Не удалось обновить инфу. %s', $exception->getMessage()),
            ]);
        }
    }
}