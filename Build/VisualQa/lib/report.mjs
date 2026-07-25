/**
 * Diff-first report.
 *
 * Nobody approves 244 elements by opening 244 tabs, so the report is ordered by
 * what a human has to decide, not by what the run happened to produce:
 * failures grouped BY CHECK first (one fixed CSS rule usually clears a dozen
 * elements at once), then per-element detail, then the elements that produced
 * nothing at all — which is the boring majority and belongs last.
 */

import { writeFileSync } from 'node:fs';
import { join } from 'node:path';

const escape = (value) => String(value)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

export function writeReport(outDir, results, meta) {
    const byCheck = {};
    const byElement = new Map();

    for (const result of results) {
        if (!byElement.has(result.cType)) {
            byElement.set(result.cType, { cType: result.cType, group: result.group, url: result.url, findings: [] });
        }
        for (const finding of result.findings) {
            byCheck[finding.check] = (byCheck[finding.check] ?? 0) + 1;
            byElement.get(result.cType).findings.push({
                ...finding,
                scope: `${result.preset}/${result.mode}/${result.width}`,
            });
        }
    }

    const elements = [...byElement.values()];
    const withFindings = elements.filter((element) => element.findings.length > 0)
        .sort((a, b) => b.findings.length - a.findings.length);

    const summary = {
        totals: {
            elements: meta.elements,
            renders: results.length,
            findings: Object.values(byCheck).reduce((sum, count) => sum + count, 0),
            elementsWithFindings: withFindings.length,
        },
        byCheck,
        elements: withFindings.map((element) => ({
            cType: element.cType,
            group: element.group,
            url: element.url,
            findings: element.findings,
        })),
    };

    // Findings collapsed per (check, selector): the same rule failing at three
    // widths is one thing to fix, not three.
    const grouped = {};
    for (const element of withFindings) {
        for (const finding of element.findings) {
            const key = `${finding.check}||${finding.selector}||${element.cType}`;
            grouped[key] ??= { ...finding, cType: element.cType, group: element.group, url: element.url, scopes: [] };
            grouped[key].scopes.push(finding.scope);
        }
    }
    const rows = Object.values(grouped).sort((a, b) => a.check.localeCompare(b.check) || a.cType.localeCompare(b.cType));

    const checkRows = Object.entries(byCheck).sort((a, b) => b[1] - a[1])
        .map(([check, count]) => `<tr><td><code>${escape(check)}</code></td><td class="n">${count}</td></tr>`)
        .join('');

    const findingRows = rows.map((row) => `
        <tr>
          <td><code>${escape(row.check)}</code></td>
          <td>${escape(row.cType)}<div class="muted">${escape(row.group)}</div></td>
          <td><code>${escape(row.selector)}</code></td>
          <td>${escape(row.detail ?? '')}</td>
          <td class="muted">${escape([...new Set(row.scopes)].slice(0, 6).join(', '))}${row.scopes.length > 6 ? ` +${row.scopes.length - 6}` : ''}</td>
          <td><a href="${escape(row.url)}" target="_blank" rel="noreferrer">open</a></td>
        </tr>`).join('');

    const clean = elements.filter((element) => element.findings.length === 0)
        .map((element) => `<li><a href="${escape(element.url)}" target="_blank" rel="noreferrer">${escape(element.cType)}</a></li>`)
        .join('');

    const verdict = summary.totals.findings === 0 ? 'pass' : 'fail';

    writeFileSync(join(outDir, 'index.html'), `<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Desiderio visual QA</title>
<style>
  :root { color-scheme: light dark; --bg: #fff; --fg: #18181b; --muted: #71717a; --line: #e4e4e7; --bad: #b91c1c; --good: #15803d; }
  @media (prefers-color-scheme: dark) { :root { --bg: #09090b; --fg: #fafafa; --muted: #a1a1aa; --line: #27272a; --bad: #f87171; --good: #4ade80; } }
  body { margin: 0; padding: 2rem clamp(1rem, 4vw, 3rem); background: var(--bg); color: var(--fg);
         font: 14px/1.55 ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; }
  h1 { font-size: 1.5rem; margin: 0 0 .25rem; }
  h2 { font-size: 1.05rem; margin: 2.5rem 0 .75rem; }
  .verdict { display: inline-block; padding: .35rem .7rem; border-radius: .4rem; font-weight: 600; }
  .verdict.pass { background: color-mix(in oklab, var(--good) 18%, transparent); color: var(--good); }
  .verdict.fail { background: color-mix(in oklab, var(--bad) 18%, transparent); color: var(--bad); }
  .muted { color: var(--muted); font-size: .85em; }
  table { border-collapse: collapse; width: 100%; margin-top: .5rem; }
  th, td { text-align: left; padding: .45rem .6rem; border-bottom: 1px solid var(--line); vertical-align: top; }
  th { font-weight: 600; font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); }
  td.n { text-align: right; font-variant-numeric: tabular-nums; }
  code { font: 12px/1.4 ui-monospace, SFMono-Regular, Menlo, monospace; word-break: break-all; }
  .scroll { overflow-x: auto; }
  ul.clean { columns: 4 12rem; list-style: none; padding: 0; font-size: .85rem; }
  a { color: inherit; }
</style></head><body>
<h1>Desiderio visual QA</h1>
<p class="muted">${meta.elements} elements &middot; ${results.length} renders &middot;
  <span class="verdict ${verdict}">${summary.totals.findings} findings across ${withFindings.length} elements</span></p>

<h2>By check &mdash; fix these first</h2>
<p class="muted">One rule usually explains many elements. Start at the top.</p>
<div class="scroll"><table><thead><tr><th>Check</th><th class="n">Count</th></tr></thead><tbody>${checkRows || '<tr><td colspan="2">None</td></tr>'}</tbody></table></div>

<h2>Findings</h2>
<div class="scroll"><table><thead><tr><th>Check</th><th>Element</th><th>Selector</th><th>Detail</th><th>Scopes</th><th></th></tr></thead>
<tbody>${findingRows || '<tr><td colspan="6">None</td></tr>'}</tbody></table></div>

<h2>Clean (${elements.length - withFindings.length})</h2>
<ul class="clean">${clean}</ul>
</body></html>
`);

    return summary;
}
