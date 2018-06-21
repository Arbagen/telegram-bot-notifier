<?php
namespace App\Command\Ostrov;

use App\Kernel;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use PHPHtmlParser\Dom\AbstractNode;
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
        $rowClass = "epts-typeRow";
        $availableClassName = ".epts-td-available";
        $priceClassName = ".epts-price__effective";

        $this->domParser->loadFromUrl($this->uri);
        /** @var AbstractNode[] $rows */
        $rows = $this->domParser->getElementsByClass(sprintf('tr.%s', $rowClass));
        $messages = [];
        foreach ($rows as $row) {
            /** @var Dom\Collection|AbstractNode $availableElement */
            $availableElement = $row->find($availableClassName);
            /** @var Dom\Collection|AbstractNode $priceElement */
            $priceElement = $row->find($priceClassName);
            $ticketCount = $availableElement->count() ? (int) $availableElement->text() : '???';
            $ticketPrice = $priceElement->count() ? html_entity_decode((string) $priceElement->text()) : '???';
            $messages[] = sprintf("%d билетов(a) по цене %s", $ticketCount, $ticketPrice);
        }

        $message = 'На данный момент доступно: ';
        $message .= implode(' и ', $messages);

        try {
            \Longman\TelegramBot\Request::sendMessage([
                'chat_id' => $this->chatId,
                'text' => $message,
            ]);
        } catch (TelegramException $e) {
                $this->logger->error($e->getMessage());
            }
    }
}