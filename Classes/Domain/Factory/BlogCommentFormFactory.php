<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Domain\Factory;

use Psr\Http\Message\ServerRequestInterface;
use StudioMitte\FriendlyCaptcha\Configuration;
use StudioMitte\FriendlyCaptcha\FieldValidator\FormValidator;
use T3G\AgencyPack\Blog\Domain\Finisher\CommentFormFinisher;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\CMS\Extbase\Validation\Validator\UrlValidator;
use TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;
use TYPO3\CMS\Form\Domain\Factory\AbstractFormFactory;
use TYPO3\CMS\Form\Domain\Finishers\RedirectFinisher;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use Webconsulting\Desiderio\Utility\SiteSettingsBoolean;

final class BlogCommentFormFactory extends AbstractFormFactory
{
    private const FRIENDLY_CAPTCHA_TEST_MODE_SETTING = 'desiderio.forms.friendlyCaptchaTestMode';

    /**
     * @param array<mixed> $configuration
     */
    public function build(array $configuration, ?string $prototypeName = null, ?ServerRequestInterface $request = null): FormDefinition
    {
        unset($configuration);

        $request ??= $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            throw new \RuntimeException('Blog comment form requires a frontend request.', 1717603200);
        }

        $prototypeName = 'standard';
        $formConfigurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $prototypeConfiguration = $formConfigurationService->getPrototypeConfiguration($prototypeName);

        $settings = GeneralUtility::makeInstance(ConfigurationManagerInterface::class)
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'blog');
        if (!is_array($settings)) {
            $settings = [];
        }

        $form = GeneralUtility::makeInstance(FormDefinition::class, 'postcomment', $prototypeConfiguration);
        $form->setRenderingOption('controllerAction', 'form');
        $form->setRenderingOption('submitButtonLabel', LocalizationUtility::translate('form.comment.submit', 'blog'));

        $renderingOptions = $form->getRenderingOptions();
        if (!is_array($renderingOptions)) {
            $renderingOptions = [];
        }
        $partialRootPaths = $renderingOptions['partialRootPaths'] ?? [];
        if (!is_array($partialRootPaths)) {
            $partialRootPaths = [];
        }
        $partialRootPaths[150] = 'EXT:desiderio/Resources/Private/Extensions/Blog/Partials/Form/';
        $form->setRenderingOption('partialRootPaths', $partialRootPaths);

        $page = $form->createPage('commentform');

        /** @var GenericFormElement $nameField */
        $nameField = $page->createElement('name', 'Text');
        $nameField->setLabel((string)LocalizationUtility::translate('form.comment.name', 'blog'));
        $nameField->addValidator(GeneralUtility::makeInstance(NotEmptyValidator::class));

        /** @var GenericFormElement $emailField */
        $emailField = $page->createElement('email', 'Text');
        $emailField->setLabel((string)LocalizationUtility::translate('form.comment.email', 'blog'));
        $emailField->addValidator(GeneralUtility::makeInstance(NotEmptyValidator::class));
        $emailField->addValidator(GeneralUtility::makeInstance(EmailAddressValidator::class));

        if ($this->commentsAllowUrls($settings)) {
            /** @var GenericFormElement $urlField */
            $urlField = $page->createElement('url', 'Text');
            $urlField->setLabel((string)LocalizationUtility::translate('form.comment.url', 'blog'));
            $urlField->addValidator(GeneralUtility::makeInstance(UrlValidator::class));
        }

        /** @var GenericFormElement $commentField */
        $commentField = $page->createElement('comment', 'Textarea');
        $commentField->setLabel((string)LocalizationUtility::translate('form.comment.comment', 'blog'));
        $commentField->addValidator(GeneralUtility::makeInstance(NotEmptyValidator::class));

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 12) {
            $stringLengthValidator = GeneralUtility::makeInstance(StringLengthValidator::class, ['minimum' => 5]);
        } else {
            $stringLengthValidator = GeneralUtility::makeInstance(StringLengthValidator::class);
            $stringLengthValidator->setOptions(['minimum' => 5]);
        }
        $commentField->addValidator($stringLengthValidator);

        if ($this->shouldAddFriendlyCaptcha($request)) {
            /** @var GenericFormElement $captchaField */
            $captchaField = $page->createElement('friendlycaptcha', 'Friendlycaptcha');
            $captchaField->setLabel('');
            $captchaField->addValidator(GeneralUtility::makeInstance(FormValidator::class));
        }

        $explanationText = $page->createElement('explanation', 'StaticText');
        $explanationText->setProperty(
            'text',
            LocalizationUtility::translate('label.required.field', 'blog') . ' '
            . LocalizationUtility::translate('label.required.field.explanation', 'blog')
        );

        $commentFinisher = GeneralUtility::makeInstance(CommentFormFinisher::class);
        $commentFinisher->setFinisherIdentifier(CommentFormFinisher::class);
        $form->addFinisher($commentFinisher);

        $redirectFinisher = GeneralUtility::makeInstance(RedirectFinisher::class);
        $redirectFinisher->setFinisherIdentifier(RedirectFinisher::class);
        $redirectFinisher->setOption(
            'pageUid',
            (string)$request->getAttribute('frontend.page.information')?->getId()
        );
        $form->addFinisher($redirectFinisher);

        $this->triggerFormBuildingFinished($form);

        return $form;
    }

    private function shouldAddFriendlyCaptcha(ServerRequestInterface $request): bool
    {
        if (!ExtensionManagementUtility::isLoaded('friendlycaptcha_official')) {
            return false;
        }

        if ($this->isFriendlyCaptchaTestModeEnabled($request)) {
            return true;
        }

        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return false;
        }

        return GeneralUtility::makeInstance(Configuration::class, $site)->isEnabled();
    }

    private function isFriendlyCaptchaTestModeEnabled(ServerRequestInterface $request): bool
    {
        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return false;
        }

        return SiteSettingsBoolean::isEnabled($site, self::FRIENDLY_CAPTCHA_TEST_MODE_SETTING);
    }

    /**
     * @param array<mixed> $settings
     */
    private function commentsAllowUrls(array $settings): bool
    {
        $comments = $settings['comments'] ?? null;
        if (!is_array($comments)) {
            return false;
        }

        $features = $comments['features'] ?? null;
        if (!is_array($features)) {
            return false;
        }

        return (bool)($features['urls'] ?? false);
    }
}
