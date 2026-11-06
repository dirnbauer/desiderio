<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/** Language metadata for subtitle files named film.en.vtt, film.de.vtt, etc. */
final class CaptionTrackViewHelper extends AbstractRequestAwareViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('filename', 'string', 'WebVTT filename with a language suffix.', true);
    }

    /** @return array{language: string, label: string, selected: bool} */
    public function render(): array
    {
        $siteLanguage = $this->request()?->getAttribute('language');
        $current = $siteLanguage instanceof SiteLanguage ? $siteLanguage->getLocale()->getLanguageCode() : 'en';

        return self::metadata($this->stringArgument('filename'), $current);
    }

    /** @return array{language: string, label: string, selected: bool} */
    public static function metadata(string $filename, string $currentLanguage = 'en'): array
    {
        $language = preg_match('/\.([a-z]{2})(?:-[a-z]{2})?\.vtt$/i', $filename, $match) === 1
            ? strtolower($match[1]) : 'en';
        $labels = ['en' => 'English', 'de' => 'Deutsch', 'zh' => '中文', 'hu' => 'Magyar'];
        $label = $labels[$language] ?? \Locale::getDisplayLanguage($language, $language);

        return [
            'language' => $language,
            'label' => $label === false || $label === '' ? strtoupper($language) : $label,
            'selected' => $language === $currentLanguage,
        ];
    }
}
