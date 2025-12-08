<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Redirects users to their preferred locale when visiting the homepage.
 *
 * When accessing the root URL (/), redirects to /{_locale}/
 * based on the Accept-Language header.
 */
final readonly class RedirectToPreferredLocaleSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_LOCALES = [
        'en', 'ar', 'bg', 'bs', 'ca', 'cs', 'de', 'es', 'eu',
        'fr', 'hr', 'id', 'it', 'ja', 'lt', 'ne', 'nl', 'pl', 'pt_BR',
        'ro', 'ru', 'sk', 'sl', 'sq', 'sr_Cyrl', 'sr_Latn', 'tr', 'uk', 'vi',
        'zh_CN',
    ];

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private string $defaultLocale,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Only handle main requests to the homepage
        if (!$event->isMainRequest() || $request->getPathInfo() !== '/') {
            return;
        }

        // Get preferred locale from Accept-Language header
        $preferredLocale = $request->getPreferredLanguage(self::SUPPORTED_LOCALES);

        // Fallback to default locale if not supported
        $locale = $preferredLocale ?? $this->defaultLocale;

        // Redirect to localized homepage
        $response = new RedirectResponse(
            $this->urlGenerator->generate('homepage', ['_locale' => $locale])
        );

        $event->setResponse($response);
    }
}
