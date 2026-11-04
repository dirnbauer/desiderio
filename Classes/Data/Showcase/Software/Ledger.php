<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase\Software;

use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseStrategyPages;

/**
 * /software/ledger: Ledger, webconsulting's internal tool for time billing,
 * receipts, bank bookings and the monthly handover to the tax advisor.
 *
 * Plan: docs/plans/2026-11-04-software-ai-features-plan.md in the lab, section
 * 4.3 (page copy), 3.2 (title, nav title, abstract), 1.4 (what the page may
 * claim) and 5.3 (screenshots, synthetic data only). Product-specific blocks
 * (the imports, eight features, the month in five steps, the internal-tool
 * note) go into 'sections'.
 *
 * Every screenshot shows the German interface with the synthetic demo company
 * "Musterfirma" (Build/Data/feature-screenshots.software-ledger.json). The
 * page names no client, no vendor, no volume and no price: Ledger is not for
 * sale, and its figures belong to webconsulting.
 *
 * @phpstan-import-type FeatureDefinition from \Webconsulting\Desiderio\Data\Showcase\ShowcaseFeatureDefinitions
 */
final class Ledger
{
    private const string SHOT = 'Screenshot of Ledger with synthetic demo data.';

    /**
     * @return FeatureDefinition
     */
    public static function definition(): array
    {
        return [
            'slug' => 'ledger',
            'product' => 'Ledger',
            'badge' => 'Ledger',
            'title' => 'Ledger: hours, receipts and bank in one place',
            'navTitle' => 'Ledger',
            'description' => 'Ledger, our internal month-end tool: hours from Redmine, receipts from mail, bank bookings over PSD2, matched and handed to the tax advisor.',
            'abstract' => 'Imports hours from Redmine, receipts from mail and bank bookings over PSD2, matches them and hands the month to the tax advisor.',
            'hero' => [
                // The app's own line on its sign-in page: "Weniger Buchhaltung. Mehr Überblick."
                'header' => 'Less bookkeeping. More overview.',
                'subheadline' => 'Hours from Redmine, receipts from your inbox, bookings from the bank, matched in one place and handed to the tax advisor once a month.',
                'primaryButton' => ['text' => 'Book a call', 'link' => '{{page:desiderio-powermail/callback}}'],
                'secondaryButton' => ['text' => 'See all software', 'link' => '{{page:software}}'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-ledger-overview-bac84103.webp', 'The next twelve months at a glance', 'Ledger overview: income, running costs and net for the next twelve months as ranges, a range chart and the next due invoices.', self::SHOT),
            ],
            'problem' => ['header' => 'Three places, one month-end', 'content' => '<p>Hours sit in Redmine, receipts in mail and Drive, bookings in the bank. Our month-end meant copying between them and chasing receipts.</p>'],
            'tour' => [
                'header' => 'From hours to a closed month',
                'subheadline' => 'Ledger’s interface is in German. Every screen shows synthetic data of a made-up company.',
                'shots' => [
                    [
                        'tab' => 'Time to invoice',
                        'title' => 'One line per ticket',
                        'description' => 'Six imported Redmine entries are selected, 16.5 hours in all. <strong>Rechnung aus Auswahl</strong> turns them into an invoice with one line per ticket.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-ledger-time-invoice-0696abd3.webp', 'One line per ticket', 'Ledger list of open hours from Redmine for one project, six entries selected and the button to invoice them.', self::SHOT),
                    ],
                    [
                        'tab' => 'Invoice',
                        'title' => 'An invoice with a payment code',
                        'description' => 'A sent demo invoice with 20 % VAT, due in 14 days. The QR code lets a banking app fill in the transfer.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-ledger-invoice-pdf-9170dc2b.webp', 'An invoice with a payment code', 'A4 demo invoice of a made-up company with one line, 20 % VAT and a payment QR code, beside an enlarged detail of the totals.', 'Invoice rendered by Ledger with synthetic demo data.'),
                    ],
                    [
                        'tab' => 'Receipt review',
                        'title' => 'Defects explained in plain words',
                        'description' => 'A receipt in review. The warning explains that the seller’s VAT id is missing and why <strong>§ 11 UStG</strong> asks for it above €400.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-ledger-receipt-review-7b7873c1.webp', 'Defects explained in plain words', 'Ledger receipt in review: a yellow warning about a missing VAT id above the form to check the data read from it.', self::SHOT),
                    ],
                    [
                        'tab' => 'Monthly closing',
                        'title' => 'Matched, suggested, missing',
                        'description' => 'Step 3 of the closing: every booking needs a receipt, a proof or a decision. Suggestions wait for <strong>Bestätigen</strong>, and missing receipts are marked red.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-ledger-closing-matching-6cfea2bd.webp', 'Matched, suggested, missing', 'Ledger closing at step 3 of 5: bookings without a receipt in red, two suggested receipts to confirm or reject.', self::SHOT),
                    ],
                    [
                        'tab' => 'Bank payments',
                        'title' => 'Payments booked on their invoices',
                        'description' => 'Two payments name their invoice number and match the open amount, so Ledger booked them. The third names no invoice and waits for a person.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-ledger-bank-5b0c5bd0.webp', 'Payments booked on their invoices', 'Ledger list of incoming payments: one open payment in red without an invoice number, two booked on their invoices in green.', self::SHOT),
                    ],
                    [
                        'tab' => 'Dark mode',
                        'title' => 'Sign-in page in dark mode',
                        'description' => 'The app’s own line, <strong>Weniger Buchhaltung. Mehr Überblick.</strong>, on the sign-in page. Every screen also comes in dark mode.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-ledger-login-dark-dd563912.webp', 'Sign-in page in dark mode', 'Ledger sign-in page in dark mode: the line Weniger Buchhaltung. Mehr Überblick. beside the sign-in form.', self::SHOT),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What Ledger brings together',
                'items' => [
                    ['icon' => 'history', 'title' => 'Imports from Redmine', 'description' => 'Selected hours become an invoice: one line per ticket, hours times rate, with a time statement frozen on sending. Invoiced hours are never overwritten.'],
                    ['icon' => 'database', 'title' => 'Imports from the bank', 'description' => 'Read-only account access over PSD2 through Enable Banking. A payment is booked only when it names exactly one open invoice and the amount matches.'],
                    ['icon' => 'file-text', 'title' => 'Imports from the tax office', 'description' => 'Notices and account statements from the FinanzOnline DataBox, as PDF or XML with a checksum. Due dates follow § 108 BAO, payments get a QR code.'],
                    ['icon' => 'sparkles', 'title' => 'Receipts read by AI, confirmed by you', 'description' => 'AI reads number, date, VAT and gross. Uncertain receipts go to review, duplicates are linked, and VAT ids are checked against VIES.'],
                ],
            ],
            'sections' => [
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'Imports',
                    'header' => 'Where the month’s data comes from',
                    'subheadline' => 'Ledger reads from three places. It never writes back to Redmine or the bank.',
                    'items' => [
                        ['title' => 'Hours from Redmine', 'description' => 'A daily import over the Redmine REST API, and a button for the moment. Hours from projects not linked yet are kept and attached later.'],
                        ['title' => 'Receipts from mail and Drive', 'description' => 'PDFs from a mail label and a Drive folder, uploads, and a bookmarklet for Apple invoices. Mails without a PDF are printed to one.'],
                        ['title' => 'Bookings from the bank', 'description' => 'Account data over PSD2, George exports as CSV or Excel, and the original statement PDFs, accepted only when their printed totals add up.'],
                    ],
                ]),
                // Eight features in four columns, two full rows: the numbered
                // grid has three columns and left two empty slots.
                ShowcaseBlocks::block('desiderio_features', [
                    'header' => 'Eight features we use every month',
                    'subheadline' => 'The German name in each item is the label you see in the app.',
                    'columns' => '4',
                    'variant' => 'icon-top',
                    'feature_items' => [
                        ['icon_name' => 'file-text', 'title' => 'Statement with receipts', 'description' => '<p><strong>Auszug mit Belegen</strong>: the original bank statement with matched bookings highlighted and the receipt named under each one. Missing receipts are marked red.</p>'],
                        ['icon_name' => 'list-checks', 'title' => 'Matching with clear rules', 'description' => '<p><strong>Automatisch abgleichen</strong> matches on hard evidence only. Subscriptions, tips, foreign currency and 2 to 4 receipts in one charge have their own rules.</p>'],
                        ['icon_name' => 'clock', 'title' => 'Invoice from selected hours', 'description' => '<p><strong>Rechnung aus Auswahl</strong> turns ticked hours into an invoice with one line per ticket and a time statement.</p>'],
                        ['icon_name' => 'lock', 'title' => 'Sent documents stay as sent', 'description' => '<p>A database trigger blocks changes to sent invoices and billed hours. <strong>Admin-Korrektur</strong> needs a reason and keeps the old version.</p>'],
                        ['icon_name' => 'history', 'title' => 'Every change on record', 'description' => '<p><strong>Protokoll</strong> logs each changed row with its old and new value, whether the change came from the app, an import script or psql.</p>'],
                        ['icon_name' => 'message-circle', 'title' => 'An assistant that asks first', 'description' => '<p>Ask in plain language and get answers from read-only queries. Proposed changes show before and after, and run only after <strong>Ausführen</strong>.</p>'],
                        ['icon_name' => 'building', 'title' => 'Card bookings from George', 'description' => '<p>Card bookings don’t come over PSD2. The <strong>George-Assistent</strong> collects them in a browser window on the office computer.</p>'],
                        ['icon_name' => 'smartphone', 'title' => 'Pay with a code', 'description' => '<p><strong>Zahlen mit Code</strong>: every invoice carries an EPC QR code. A banking app scans it and fills in amount, IBAN and reference.</p>'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_featuretimeline', [
                    'header' => 'The month in five steps',
                    'subheadline' => 'The monthly closing walks through the same five steps every time.',
                    'items' => [
                        ['step' => '1', 'title' => 'Review the receipts', 'description' => '<p>Check what AI read from each receipt. Defects and duplicates are flagged before anything moves on.</p>'],
                        ['step' => '2', 'title' => 'Load the statements', 'description' => '<p>Bring in the month’s account and card statements. A PDF counts only when its printed totals add up.</p>'],
                        ['step' => '3', 'title' => 'Match the bookings', 'description' => '<p>Every booking gets a receipt, a proof or a decision. Suggestions wait for a person to confirm them.</p>'],
                        ['step' => '4', 'title' => 'Hand over to the tax advisor', 'description' => '<p>Approved receipts, sent invoices and statements are uploaded to the tax advisor’s portal, each in its folder.</p>'],
                        ['step' => '5', 'title' => 'Close the month', 'description' => '<p>The month is closed, and the statement with receipts is frozen as its record.</p>'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_callout', [
                    'header' => 'An internal tool, tuned to Austria',
                    'content' => '<p>Ledger is webconsulting’s internal tool. It follows Austrian rules such as § 11 UStG, § 20 Abs. 6 UStG and § 108 BAO, and it works with one bank and one tax advisor. We show it because the pattern is what we build for clients: imports first, matching second, people deciding.</p>',
                    'variant' => 'note',
                ]),
            ],
            'faq' => [
                'header' => 'Questions about Ledger',
                'subheadline' => 'Banks, taxes, AI and availability.',
                'items' => [
                    ['question' => 'Which bank does it connect to?', 'answer' => 'Erste Bank and George, over PSD2 and read-only. Card bookings come from George exports and the George assistant. Another bank would need its own import.'],
                    ['question' => 'Does it file taxes?', 'answer' => 'No. It retrieves notices and account statements, shows due dates and prepares payment codes. The tax advisor files the returns.'],
                    ['question' => 'Where does the AI run?', 'answer' => 'Receipt reading and the assistant call AI models over their APIs. The assistant runs on Claude, whose commercial terms exclude training on API data. Every proposed change waits for a click.'],
                    ['question' => 'Can I get it?', 'answer' => 'Ledger is not sold as a product. Talk to us if you want the same workflow for your company, built on your rules, your bank and your tax advisor.'],
                ],
            ],
            'install' => null,
            'cta' => [
                'header' => 'Bring this pattern to your month-end',
                'description' => ShowcaseStrategyPages::standardLine('Ledger'),
                'text' => 'Book a call',
                'link' => '{{page:desiderio-powermail/callback}}',
            ],
        ];
    }
}
