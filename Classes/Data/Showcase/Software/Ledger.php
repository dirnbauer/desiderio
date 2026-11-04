<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase\Software;

use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;

/**
 * /software/ledger: Ledger, webconsulting's internal tool for time billing,
 * receipts, bank bookings and the monthly handover to the tax advisor.
 *
 * Plan: docs/plans/2026-11-04-software-ai-features-plan.md in the lab, section
 * 4.3 (page copy), 3.2 (title, nav title, abstract), 1.4 (what the page may
 * claim) and 5.3 (screenshots, synthetic data only). Product-specific blocks
 * (features, the month in five steps, the internal-tool note) go into
 * 'sections'.
 *
 * @phpstan-import-type FeatureDefinition from \Webconsulting\Desiderio\Data\Showcase\ShowcaseFeatureDefinitions
 */
final class Ledger
{
    /**
     * @return FeatureDefinition
     */
    public static function definition(): array
    {
        return [
            'slug' => 'ledger',
            'product' => 'Ledger',
            'badge' => 'Ledger',
            'title' => 'Ledger: time, receipts and bank in one month-end',
            'navTitle' => 'Ledger',
            'description' => 'Ledger imports hours from Redmine, receipts from mail and bank bookings over PSD2, matches them and hands the month to the tax advisor.',
            'abstract' => 'Imports hours from Redmine, receipts from mail and bank bookings over PSD2, matches them and hands the month to the tax advisor.',
            'hero' => [
                'header' => 'Less bookkeeping. More overview.',
                'subheadline' => 'Hours from Redmine, receipts from your inbox, bookings from the bank, matched in one place and handed to the tax advisor once a month.',
                'primaryButton' => ['text' => 'Book a demo', 'link' => '{{page:desiderio-powermail/callback}}'],
                'secondaryButton' => ['text' => 'See all software', 'link' => '{{page:software}}'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-ledger-overview-bac84103.webp', 'The month at a glance', 'Ledger overview with open invoices, receipts to review and a twelve-month outlook, filled with synthetic demo data.', 'Screenshot of Ledger with synthetic demo data.'),
            ],
            'problem' => ['header' => 'Three places, one month-end', 'content' => '<p>Time sits in Redmine, receipts in mail and Drive, bookings in the bank. The month-end is copying between them, and the tax advisor waits for the last receipt.</p>'],
            'tour' => [
                'header' => 'From hours to a closed month',
                'subheadline' => 'Screens from Ledger with synthetic demo data.',
                'shots' => [
                    [
                        'tab' => 'Time to invoice',
                        'title' => 'One line per ticket',
                        'description' => 'Select imported hours and create the invoice: one line per ticket, hours times rate, with a frozen time statement.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-ledger-time-invoice-0696abd3.webp', 'One line per ticket', 'Ledger time view with imported Redmine hours selected for an invoice, filled with synthetic demo data.', 'Screenshot of Ledger with synthetic demo data.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What Ledger brings together',
                'items' => [
                    ['icon' => 'history', 'title' => 'Imports from Redmine', 'description' => 'Hours, tickets, activity and the billable flag, read-only. Invoiced entries are never overwritten.'],
                    ['icon' => 'database', 'title' => 'Imports from the bank', 'description' => 'PSD2 account access through Enable Banking, read-only. A payment is booked automatically only when exactly one open invoice matches.'],
                    ['icon' => 'file-text', 'title' => 'Imports from the tax office', 'description' => 'Notices and account statements from the FinanzOnline DataBox, checksummed, with due dates and payment QR codes.'],
                    ['icon' => 'check-circle', 'title' => 'Receipts read by AI, confirmed by you', 'description' => 'Number, date, VAT and gross are read for you. Uncertain receipts go to review, duplicates are linked and VAT ids are checked.'],
                ],
            ],
            'sections' => [],
            'faq' => [
                'header' => 'Questions about Ledger',
                'subheadline' => 'Banks, taxes, AI and availability.',
                'items' => [
                    ['question' => 'Which bank does it connect to?', 'answer' => 'Erste Bank and George over PSD2 today. Other banks can be added through their exports.'],
                    ['question' => 'Does it file taxes?', 'answer' => 'No. It retrieves notices and statements and prepares payments. The tax advisor files.'],
                    ['question' => 'Where does the AI run?', 'answer' => 'Receipt reading and the assistant use API models whose commercial terms exclude training on the data. Every proposed change waits for a click.'],
                    ['question' => 'Can I get it?', 'answer' => 'Ledger is not sold as a product. Talk to us if you want the same workflow for your company.'],
                ],
            ],
            'install' => null,
            'cta' => [
                'header' => 'Want this for your month-end?',
                'description' => 'We show you Ledger with demo data. Like everything we build, it follows the five points of our strategy.',
                'text' => 'Book a call',
                'link' => '{{page:desiderio-powermail/callback}}',
            ],
        ];
    }
}
