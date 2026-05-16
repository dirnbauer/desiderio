<?php

declare(strict_types=1);

/**
 * Convert TYPO3 Content Block XLIFF 1.2 label files to XLIFF 2.0.
 *
 * Iterates over ContentBlocks/ContentElements/<slug>/language/*.labels.xlf,
 * detects XLIFF 1.2 files (trans-unit / source-language / target-language),
 * and rewrites them in TYPO3 v14 XLIFF 2.0 with srcLang/trgLang attributes
 * and <unit><segment state="final"><source/><target/></segment></unit>
 * sequences. Idempotent: already-2.0 files are left alone.
 *
 * Usage:
 *   php Build/Scripts/convert-xliff-1-2-to-2-0.php [path-to-extension-root]
 */

$root = rtrim($argv[1] ?? dirname(__DIR__, 2), '/');
$pattern = $root . '/ContentBlocks/ContentElements/*/language/*.labels.xlf';
$files = glob($pattern) ?: [];

$converted = 0;
$skipped = 0;

foreach ($files as $file) {
    $contents = (string) file_get_contents($file);
    if (str_contains($contents, 'urn:oasis:names:tc:xliff:document:2.0')) {
        $skipped++;
        continue;
    }
    if (!str_contains($contents, 'urn:oasis:names:tc:xliff:document:1.2')) {
        $skipped++;
        continue;
    }

    $previousValue = libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $document->preserveWhiteSpace = false;
    if (!$document->loadXML($contents, LIBXML_PARSEHUGE)) {
        libxml_clear_errors();
        libxml_use_internal_errors($previousValue);
        fwrite(STDERR, "Failed to parse {$file}\n");
        continue;
    }
    libxml_clear_errors();
    libxml_use_internal_errors($previousValue);

    $fileNode = $document->getElementsByTagName('file')->item(0);
    if (!$fileNode instanceof DOMElement) {
        fwrite(STDERR, "Missing <file> node in {$file}\n");
        continue;
    }

    $sourceLang = $fileNode->getAttribute('source-language') ?: 'en';
    $targetLang = $fileNode->getAttribute('target-language');
    $productName = $fileNode->getAttribute('product-name')
        ?: basename(dirname(dirname($file)));

    $units = [];
    foreach ($fileNode->getElementsByTagName('trans-unit') as $unit) {
        if (!$unit instanceof DOMElement) {
            continue;
        }
        $id = $unit->getAttribute('id');
        $sourceNode = $unit->getElementsByTagName('source')->item(0);
        $targetNode = $unit->getElementsByTagName('target')->item(0);
        $units[] = [
            'id' => $id,
            'source' => $sourceNode instanceof DOMElement ? $sourceNode->textContent : '',
            'target' => $targetNode instanceof DOMElement ? $targetNode->textContent : '',
        ];
    }

    $isSourceFile = $targetLang === '' || $targetLang === $sourceLang;
    $xliffAttributes = sprintf(
        ' version="2.0" xmlns="urn:oasis:names:tc:xliff:document:2.0" srcLang="%s"%s',
        htmlspecialchars($sourceLang, ENT_QUOTES | ENT_XML1),
        $isSourceFile ? '' : sprintf(' trgLang="%s"', htmlspecialchars($targetLang, ENT_QUOTES | ENT_XML1))
    );

    $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
    $output .= "<xliff{$xliffAttributes}>\n";
    $output .= sprintf("  <file id=\"%s\">\n", htmlspecialchars($productName, ENT_QUOTES | ENT_XML1));

    foreach ($units as $unit) {
        $output .= sprintf("    <unit id=\"%s\">\n", htmlspecialchars($unit['id'], ENT_QUOTES | ENT_XML1));
        if ($isSourceFile) {
            $output .= "      <segment>\n";
            $output .= sprintf("        <source>%s</source>\n", htmlspecialchars($unit['source'], ENT_XML1 | ENT_COMPAT, 'UTF-8'));
            $output .= "      </segment>\n";
        } else {
            $output .= "      <segment state=\"final\">\n";
            $output .= sprintf("        <source>%s</source>\n", htmlspecialchars($unit['source'], ENT_XML1 | ENT_COMPAT, 'UTF-8'));
            $output .= sprintf("        <target>%s</target>\n", htmlspecialchars($unit['target'], ENT_XML1 | ENT_COMPAT, 'UTF-8'));
            $output .= "      </segment>\n";
        }
        $output .= "    </unit>\n";
    }
    $output .= "  </file>\n";
    $output .= "</xliff>\n";

    file_put_contents($file, $output);
    $converted++;
}

printf("Converted: %d\nSkipped (already 2.0 or unknown format): %d\n", $converted, $skipped);
