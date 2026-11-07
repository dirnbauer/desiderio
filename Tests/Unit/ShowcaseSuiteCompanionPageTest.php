<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseAudiencePages;
use Webconsulting\Desiderio\Data\Showcase\ShowcasePricing;
use Webconsulting\Desiderio\Data\Showcase\ShowcasePricingPages;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseSuiteCompanionPage;

final class ShowcaseSuiteCompanionPageTest extends TestCase
{
    public function testCompanionUsesTheExistingStudioPriceAndScope(): void
    {
        $studio = array_column(ShowcasePricing::plans(), null, 'key')['studio'];
        $companion = ShowcasePricing::suiteCompanion();

        self::assertSame($studio['price'], $companion['price']);
        self::assertSame($studio['features'], $companion['features']);
        self::assertSame(ShowcasePricing::ORDER_LINK, $companion['button']['link']);
        self::assertStringContainsString('plus VAT', $companion['period']);
    }

    public function testOverviewAndDetailDiscloseIndependenceBeforeThePaidOffer(): void
    {
        foreach ([ShowcaseAudiencePages::pages()[0], ShowcaseSuiteCompanionPage::page()] as $page) {
            $notice = $page['content'][1]['fields']['content'];
            self::assertIsString($notice);
            self::assertStringContainsString(ShowcasePricing::INDEPENDENCE_NOTICE, $notice);
            self::assertStringContainsString('purchased separately', $notice);
        }
    }

    public function testEveryCampaignBlockExpiresAtTheEndOfTheViennaYear(): void
    {
        $end = ShowcasePricing::suitePromotionEnd();
        $vienna = new \DateTimeZone('Europe/Vienna');
        self::assertSame('2026-12-31 23:59:59', new \DateTimeImmutable('@' . ($end - 1))->setTimezone($vienna)->format('Y-m-d H:i:s'));
        self::assertSame('2027-01-01 00:00:00', new \DateTimeImmutable('@' . $end)->setTimezone($vienna)->format('Y-m-d H:i:s'));

        $campaigns = 0;
        foreach (ShowcaseAudiencePages::pages() as $page) {
            foreach ($page['content'] as $block) {
                $copy = json_encode($block['fields'], JSON_THROW_ON_ERROR);
                if (!str_contains($copy, '60-minute remote onboarding')) {
                    continue;
                }
                ++$campaigns;
                self::assertSame($end, $block['fields']['endtime'] ?? null, $page['slug']);
                self::assertStringContainsString('Existing annual Studio customers qualify too', $copy);
                self::assertStringContainsString('One session per company', $copy);
                self::assertStringContainsString('31 March 2027', $copy);
            }
        }
        self::assertSame(2, $campaigns);
    }

    public function testCompanionKeepsSoftwareFreeWithoutASuitePurchase(): void
    {
        $copy = json_encode(ShowcaseSuiteCompanionPage::page(), JSON_THROW_ON_ERROR);
        self::assertStringContainsString('Free, with or without the official TYPO3 Suite.', $copy);
        self::assertStringContainsString('No licence key or paid access gate', $copy);
    }

    public function testOrdersLeadToTheRealSellerInsteadOfTheDemoForm(): void
    {
        $order = array_column(ShowcasePricingPages::pages(), null, 'slug')['/pricing/order'];
        self::assertNotContains('desiderio_demorequest', array_column($order['content'], 'ctype'));
        self::assertSame(ShowcasePricing::SELLER_CONTACT_URL, $order['content'][1]['fields']['link']);
    }
}
