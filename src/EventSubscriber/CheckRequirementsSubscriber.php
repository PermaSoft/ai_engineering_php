<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Checks application requirements before processing requests.
 *
 * Verifies:
 * - Minimum PHP version
 * - Minimum Symfony version
 * - Required PHP extensions
 *
 * Throws HTTP 500 error if requirements are not met.
 */
final readonly class CheckRequirementsSubscriber implements EventSubscriberInterface
{
    private const REQUIRED_PHP_VERSION = '8.2.0';
    private const REQUIRED_SYMFONY_VERSION = '7.2.0';
    private const REQUIRED_EXTENSIONS = ['pdo', 'pdo_sqlite', 'intl', 'mbstring'];

    public function __construct(
        private string $environment,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // High priority to run early
            KernelEvents::REQUEST => ['onKernelRequest', 512],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Only check on main request, not sub-requests
        if (!$event->isMainRequest()) {
            return;
        }

        // Skip checks in production and test (already verified during deployment/setup)
        if ($this->environment === 'prod' || $this->environment === 'test') {
            return;
        }

        $this->checkPhpVersion();
        $this->checkSymfonyVersion();
        $this->checkExtensions();
    }

    private function checkPhpVersion(): void
    {
        if (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION, '<')) {
            throw new HttpException(
                500,
                sprintf(
                    'This application requires PHP %s or higher. You are running PHP %s.',
                    self::REQUIRED_PHP_VERSION,
                    PHP_VERSION
                )
            );
        }
    }

    private function checkSymfonyVersion(): void
    {
        $currentVersion = \Symfony\Component\HttpKernel\Kernel::VERSION;

        if (version_compare($currentVersion, self::REQUIRED_SYMFONY_VERSION, '<')) {
            throw new HttpException(
                500,
                sprintf(
                    'This application requires Symfony %s or higher. You are running Symfony %s.',
                    self::REQUIRED_SYMFONY_VERSION,
                    $currentVersion
                )
            );
        }
    }

    private function checkExtensions(): void
    {
        $missingExtensions = [];

        foreach (self::REQUIRED_EXTENSIONS as $extension) {
            if (!extension_loaded($extension)) {
                $missingExtensions[] = $extension;
            }
        }

        if (!empty($missingExtensions)) {
            throw new HttpException(
                500,
                sprintf(
                    'The following PHP extensions are required but not loaded: %s',
                    implode(', ', $missingExtensions)
                )
            );
        }
    }
}
