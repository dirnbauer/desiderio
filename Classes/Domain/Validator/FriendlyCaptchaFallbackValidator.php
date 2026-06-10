<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Domain\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Stands in for StudioMitte\FriendlyCaptcha\FieldValidator\FormValidator when
 * studiomitte/friendlycaptcha is not installed. The bundled Desiderio forms
 * declare a "Friendlycaptcha" validator; without the real extension the field
 * is a decorative placeholder, so every submission passes.
 */
final class FriendlyCaptchaFallbackValidator extends AbstractValidator
{
    protected function isValid(mixed $value): void
    {
        // Intentionally empty: no captcha service is available to verify against.
    }
}
