#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * PR Consolidation Analysis Script
 *
 * Analyzes open pull requests to identify duplicates targeting the same issues
 * and provides recommendations for which PRs to merge or close.
 *
 * Usage: php scripts/analyze-pr-consolidation.php
 */

class PRConsolidationAnalyzer
{
    private string $repo;
    private array $prs = [];
    private array $issueToPrMap = [];

    public function __construct(string $repo)
    {
        $this->repo = $repo;
    }

    public function fetchAllPRs(): self
    {
        $page = 1;
        $perPage = 100;

        do {
            $json = shell_exec("gh pr list --state open --limit {$perPage} --json number,title,author,createdAt,headRefName,labels,body --search 'is:pr'");

            if (empty($json)) {
                break;
            }

            $prs = json_decode($json, true);

            if (empty($prs)) {
                break;
            }

            $this->prs = array_merge($this->prs, $prs);
            $page++;

            sleep(1); // Rate limiting
        } while (count($prs) === $perPage);

        return $this;
    }

    public function mapPrsToIssues(): self
    {
        foreach ($this->prs as $pr) {
            $issueNumbers = $this->extractIssueNumbers($pr['title'], $pr['body'] ?? '');

            foreach ($issueNumbers as $issueNumber) {
                if (!isset($this->issueToPrMap[$issueNumber])) {
                    $this->issueToPrMap[$issueNumber] = [];
                }

                $this->issueToPrMap[$issueNumber][] = [
                    'number' => $pr['number'],
                    'title' => $pr['title'],
                    'author' => $pr['author']['login'] ?? 'unknown',
                    'created_at' => $pr['createdAt'],
                    'branch' => $pr['headRefName'],
                    'labels' => array_map(fn($l) => $l['name'], $pr['labels'] ?? []),
                ];
            }
        }

        return $this;
    }

    public function identifyDuplicates(): array
    {
        $duplicates = [];

        foreach ($this->issueToPrMap as $issueNumber => $prs) {
            if (count($prs) > 1) {
                // Sort by creation date (oldest first)
                usort($prs, fn($a, $b) => strtotime($a['created_at']) <=> strtotime($b['created_at']));

                $duplicates[$issueNumber] = [
                    'prs' => $prs,
                    'recommendation' => $this->generateRecommendation($prs),
                    'total' => count($prs),
                ];
            }
        }

        return $duplicates;
    }

    private function extractIssueNumbers(string $title, string $body): array
    {
        $pattern = '/(?:Fixes|Closes|Resolves|Related|Fix|Close|Resolve|issue|#)(\s+#)?(\d+)/i';
        $matches = [];

        // Search in title
        preg_match_all($pattern, $title, $titleMatches);
        if (!empty($titleMatches[2])) {
            $matches = array_merge($matches, $titleMatches[2]);
        }

        // Search in body
        preg_match_all($pattern, $body, $bodyMatches);
        if (!empty($bodyMatches[2])) {
            $matches = array_merge($matches, $bodyMatches[2]);
        }

        // Also look for standalone #123 patterns
        preg_match_all('/#(\d+)/', $title . ' ' . $body, $hashMatches);
        if (!empty($hashMatches[1])) {
            $matches = array_merge($matches, $hashMatches[1]);
        }

        return array_unique(array_map('intval', $matches));
    }

    private function generateRecommendation(array $prs): array
    {
        $recommendation = [
            'action' => 'merge_first',
            'reason' => 'Oldest PR should be merged as canonical implementation',
            'pr_to_merge' => $prs[0]['number'],
            'prs_to_close' => array_slice(array_column($prs, 'number'), 1),
            'close_message_template' => $this->generateCloseMessage($prs[0]['number'], $prs[0]['title']),
        ];

        // Check if any PR has 'ready' or 'review' labels
        foreach ($prs as $pr) {
            if (in_array('ready', $pr['labels'], true) || in_array('approved', $pr['labels'], true)) {
                $recommendation['action'] = 'merge_ready';
                $recommendation['pr_to_merge'] = $pr['number'];
                $recommendation['reason'] = 'PR has ready/approved label, should be prioritized';
                $recommendation['prs_to_close'] = array_filter(array_column($prs, 'number'), fn($n) => $n !== $pr['number']);
                $recommendation['close_message_template'] = $this->generateCloseMessage($pr['number'], $pr['title']);
                break;
            }
        }

        return $recommendation;
    }

    private function generateCloseMessage(int $canonicalPr, string $title): string
    {
        return <<<'EOF'
This PR is being closed as a duplicate of PR #{$canonicalPr} "{$title}"

Multiple PRs were created for the same issue. To maintain focus and reduce reviewer workload, we're consolidating to the canonical implementation.

Please:
1. Review PR #{$canonicalPr} for the merged solution
2. If your PR has improvements not in the canonical one, please comment on PR #{$canonicalPr}
3. Future contributions: Check for existing PRs before creating new ones (see CONTRIBUTING.md)

Thank you for your contribution!
EOF;
    }

    public function generateReport(): string
    {
        $duplicates = $this->identifyDuplicates();

        $report = "# PR Consolidation Analysis Report\n\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $report .= "Total Open PRs: " . count($this->prs) . "\n";
        $report .= "Issues with Duplicate PRs: " . count($duplicates) . "\n";
        $report .= "Total Duplicate PRs: " . array_sum(array_column($duplicates, 'total')) . "\n\n";

        $report .= "---\n\n";
        $report .= "## Summary by Issue\n\n";

        foreach ($duplicates as $issueNumber => $data) {
            $report .= "### Issue #{$issueNumber}\n\n";
            $report .= "**Total PRs**: {$data['total']}\n";
            $report .= "**Recommendation**: {$data['recommendation']['action']}\n";
            $report .= "**Reason**: {$data['recommendation']['reason']}\n";
            $report .= "**Canonical PR**: #{$data['recommendation']['pr_to_merge']}\n\n";

            $report .= "**All PRs for this issue**:\n";
            foreach ($data['prs'] as $pr) {
                $isCanonical = $pr['number'] === $data['recommendation']['pr_to_merge'];
                $indicator = $isCanonical ? '✅ **CANONICAL**' : '❌';
                $labels = !empty($pr['labels']) ? ' [' . implode(', ', $pr['labels']) . ']' : '';

                $report .= "- {$indicator} #{$pr['number']}: {$pr['title']}{$labels}\n";
                $report .= "  - Author: @{$pr['author']}\n";
                $report .= "  - Created: {$pr['created_at']}\n";
                $report .= "  - Branch: {$pr['branch']}\n\n";
            }

            if (!empty($data['recommendation']['prs_to_close'])) {
                $report .= "**PRs to Close**: " . implode(', ', array_map(fn($n) => "#{$n}", $data['recommendation']['prs_to_close'])) . "\n\n";
                $report .= "**Close Message Template**:\n";
                $report .= "```\n";
                $report .= $data['recommendation']['close_message_template'];
                $report .= "\n```\n\n";
            }

            $report .= "---\n\n";
        }

        // Add actionable recommendations section
        $report .= "## Actionable Recommendations\n\n";
        $report .= "### High Priority (Ready to Merge)\n\n";
        foreach ($duplicates as $issueNumber => $data) {
            if ($data['recommendation']['action'] === 'merge_ready') {
                $report .= "- [ ] Merge PR #{$data['recommendation']['pr_to_merge']} (Issue #{$issueNumber})\n";
                $report .= "  - Then close: " . implode(', ', array_map(fn($n) => "#{$n}", $data['recommendation']['prs_to_close'])) . "\n\n";
            }
        }

        $report .= "### Medium Priority (Canonical PR Selected)\n\n";
        foreach ($duplicates as $issueNumber => $data) {
            if ($data['recommendation']['action'] === 'merge_first') {
                $report .= "- [ ] Review PR #{$data['recommendation']['pr_to_merge']} (Issue #{$issueNumber})\n";
                $report .= "  - If approved, merge and close: " . implode(', ', array_map(fn($n) => "#{$n}", $data['recommendation']['prs_to_close'])) . "\n\n";
            }
        }

        return $report;
    }

    public function saveReport(string $filename): void
    {
        file_put_contents($filename, $this->generateReport());
        echo "Report saved to: {$filename}\n";
    }
}

// Execute if run directly
if (php_sapi_name() === 'cli' && realpath($argv[0]) === __FILE__) {
    $repo = getenv('GITHUB_REPOSITORY') ?: 'sulhicmz/malnu-backend';

    echo "Analyzing PRs for: {$repo}\n";

    $analyzer = new PRConsolidationAnalyzer($repo);
    $analyzer
        ->fetchAllPRs()
        ->mapPrsToIssues()
        ->saveReport('docs/PR_CONSOLIDATION_REPORT.md');

    echo "\nAnalysis complete!\n";
}
