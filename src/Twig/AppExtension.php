<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\Intl\Locales;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('locales', $this->getLocales(...)),
        ];
    }

    /**
     * Returns an array with locale codes as keys and locale names as values.
     *
     * @return array<string, string>
     */
    public function getLocales(): array
    {
        $localeCodes = ['en', 'fr', 'de', 'es', 'cs', 'nl', 'ru', 'uk', 'ro', 'pt_BR', 'pl', 'it', 'ja', 'id', 'ca', 'sl', 'hr', 'zh_CN', 'bg', 'tr', 'lt'];

        $locales = [];
        foreach ($localeCodes as $localeCode) {
            $locales[$localeCode] = Locales::getName($localeCode, $localeCode);
        }

        return $locales;
    }
}
