<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * Adds global variables to all Twig templates.
 *
 * Global variables:
 * - app_version: Application version from composer.json
 * - symfony_version: Current Symfony version
 * - php_version: Current PHP version
 */
final readonly class ControllerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private string $projectDir,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        // Only process main requests
        if (!$event->isMainRequest()) {
            return;
        }

        // Add global Twig variables
        $this->twig->addGlobal('app_version', $this->getAppVersion());
        $this->twig->addGlobal('symfony_version', \Symfony\Component\HttpKernel\Kernel::VERSION);
        $this->twig->addGlobal('php_version', PHP_VERSION);
    }

    private function getAppVersion(): string
    {
        $composerFile = $this->projectDir . '/composer.json';

        if (!file_exists($composerFile)) {
            return 'unknown';
        }

        $composerData = json_decode((string) file_get_contents($composerFile), true);

        return $composerData['version'] ?? 'dev';
    }
}
