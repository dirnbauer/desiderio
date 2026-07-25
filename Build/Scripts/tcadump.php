<?php
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
$al = dirname(__DIR__, 5) . '/vendor/autoload.php';
require $al;
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init(require $al);
foreach (['desiderio_testimonialgrid_testimonials', 'desiderio_faq_items'] as $col) {
    $cfg = $GLOBALS['TCA']['tt_content']['columns'][$col]['config'] ?? null;
    echo "$col:\n";
    if ($cfg === null) { echo "  NO TCA\n"; continue; }
    foreach (['type','foreign_table','foreign_field','foreign_table_field','foreign_match_fields'] as $k) {
        echo "  $k = ", is_array($cfg[$k] ?? null) ? json_encode($cfg[$k]) : var_export($cfg[$k] ?? null, true), "\n";
    }
}
