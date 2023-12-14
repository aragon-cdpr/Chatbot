<?php

namespace App\Infrastructure\EventListeners;

use App\Application\Services\WebsocketService;
use App\Infrastructure\Events\WebsocketCloseEvent;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: WebsocketCloseEvent::class, method: 'onWebsocketClose')]
class WebsocketCloseEventListener
{
    private ConsoleLogger $consoleLogger;
    private ConsoleOutput $output;

    public function __construct(
        private WebsocketService $websocketService,
    ) {
        $this->output = new ConsoleOutput(OutputInterface::VERBOSITY_DEBUG);
        $this->consoleLogger = new ConsoleLogger($this->output);
    }

    public function onWebsocketClose(WebsocketCloseEvent $event)
    {
        $this->consoleLogger->log(LogLevel::DEBUG, 'Test Close');
    }
}
