<?php
/**
 * PR Consolidation Analyzer
 *
 * Analyzes all open pull requests to identify:
 * - Duplicate PRs for the same issue
 * - Ready-to-merge PRs
 * - Stale PRs (no activity for 14+ days)
 * - PRs needing review
 *
 * Requires: GitHub CLI (gh) installed and authenticated
 */

declare(strict_types=1);

class PRAnalyzer
{
    private string $repo = 'sulhicmz/malnu-backend';
    private array $prs = [];
    private array $prsByIssue = [];
    private array $report = [];

    public function __construct()
    {
        $this->fetchPullRequests();
        $this->groupPRsByIssue();
        $this->analyzeIssues();
        $this->generateReport();
    }

    /**
     * Fetch all open pull requests using GitHub CLI
     */
    private function fetchPullRequests(): void
    {
        echo "Fetching open pull requests...\n";

        $json = shell_exec('gh pr list --state open --json number,title,body,headRefName,headRepository,author,createdAt,updatedAt,additions,deletions,reviewDecision,state,mergeable,url,reviews --limit 100');

        if ($json === null) {
            echo "Error: Failed to fetch PRs. Ensure GitHub CLI is installed and authenticated.\n";
            exit(1);
        }

        $this->prs = json_decode($json, true);

        echo "Found " . count($this->prs) . " open pull requests\n\n";
    }

    /**
     * Group PRs by the issue they reference
     */
    private function groupPRsByIssue(): void
    {
        echo "Grouping PRs by issue...\n";

        foreach ($this->prs as $pr) {
            $issueNumber = $this->extractIssueNumber($pr);

            if ($issueNumber !== null) {
                if (!isset($this->prsByIssue[$issueNumber])) {
                    $this->prsByIssue[$issueNumber] = [];
                }
                $this->prsByIssue[$issueNumber][] = $pr;
            }
        }

        $groupedCount = 0;
        $singlePRs = 0;
        foreach ($this->prsByIssue as $issue => $prs) {
            if (count($prs) > 1) {
                $groupedCount++;
            } else {
                $singlePRs++;
            }
        }

        echo "Found $groupedCount issues with multiple PRs, $singlePRs issues with single PR\n\n";
    }

    /**
     * Extract issue number from PR title or body
     */
    private function extractIssueNumber(array $pr): ?int
    {
        $text = $pr['title'] . ' ' . $pr['body'];

        if (preg_match_all('/#(\d+)/', $text, $matches)) {
            return (int) $matches[1][0];
        }

        return null;
    }

    /**
     * Analyze each issue group
     */
    private function analyzeIssues(): void
    {
        echo "Analyzing PRs by issue...\n";

        $today = new DateTime();
        $staleThresholdDays = 14;
        $staleThreshold = $today->sub(new DateInterval("P{$staleThresholdDays}D"));

        foreach ($this->prsByIssue as $issueNumber => $prs) {
            $analysis = $this->analyzeIssuePRs($issueNumber, $prs, $staleThreshold);
            $this->report[] = $analysis;
        }

        $readyCount = 0;
        $needsReviewCount = 0;
        $staleCount = 0;

        foreach ($this->report as $r) {
            switch ($r['recommendation']) {
                case 'READY_TO_MERGE':
                    $readyCount++;
                    break;
                case 'NEEDS_REVIEW':
                    $needsReviewCount++;
                    break;
                case 'STALE':
                    $staleCount++;
                    break;
            }
        }

        echo "Analysis complete: $readyCount ready to merge, $needsReviewCount need review, $staleCount stale\n\n";
    }

    /**
     * Analyze PRs for a specific issue
     */
    private function analyzeIssuePRs(int $issueNumber, array $prs, DateTime $staleThreshold): array
    {
        if (count($prs) === 1) {
            $pr = $prs[0];
            $status = $this->getSinglePRStatus($pr, $staleThreshold);
            return [
                'issue' => $issueNumber,
                'prs' => [$pr],
                'recommendation' => $status,
                'recommended_pr' => $pr['number'],
                'reason' => $this->getSinglePRReason($pr, $status, $staleThreshold),
            ];
        }

        $sortedPRs = $this->sortPRs($prs);
        $recommended = $sortedPRs[0];
        $status = 'READY_TO_MERGE';
        $reason = $this->getRecommendationReason($sortedPRs);

        return [
            'issue' => $issueNumber,
            'prs' => $sortedPRs,
            'recommendation' => $status,
            'recommended_pr' => $recommended['number'],
            'reason' => $reason,
        ];
    }

    /**
     * Get status for single PR
     */
    private function getSinglePRStatus(array $pr, DateTime $staleThreshold): string
    {
        $updatedAt = new DateTime($pr['updatedAt']);

        if ($updatedAt < $staleThreshold) {
            return 'STALE';
        }

        if ($pr['reviewDecision'] !== null && $pr['reviewDecision'] !== 'APPROVED') {
            return 'NEEDS_REVIEW';
        }

        if ($pr['reviewCount'] > 0) {
            return 'NEEDS_REVIEW';
        }

        return 'READY_TO_MERGE';
    }

    /**
     * Get reason for single PR status
     */
    private function getSinglePRReason(array $pr, string $status, DateTime $staleThreshold): string
    {
        switch ($status) {
            case 'STALE':
                $updatedAt = new DateTime($pr['updatedAt']);
                $daysSinceUpdate = $updatedAt->diff(new DateTime())->days;
                return "Last updated {$daysSinceUpdate} days ago (stale threshold: 14 days)";
            case 'NEEDS_REVIEW':
                return "Has review comments or not approved";
            default:
                return "Ready for merge";
        }
    }

    /**
     * Sort PRs by quality criteria
     */
    private function sortPRs(array $prs): array
    {
        usort($prs, function ($a, $b) {
            $scoreA = $this->calculatePRScore($a);
            $scoreB = $this->calculatePRScore($b);

            return $scoreB <=> $scoreA;
        });

        return $prs;
    }

    /**
     * Calculate PR score for sorting
     */
    private function calculatePRScore(array $pr): int
    {
        $score = 0;

        // Prefer more recent PRs (updated)
        $updatedAt = new DateTime($pr['updatedAt']);
        $daysSinceUpdate = (int)$updatedAt->diff(new DateTime())->days;
        $score -= $daysSinceUpdate;

        // Prefer PRs with more changes (more complete)
        $score += (int)(($pr['additions'] + $pr['deletions']) / 100);

        // Prefer PRs with approvals
        if ($pr['reviewDecision'] === 'APPROVED') {
            $score += 100;
        }

        // Prefer PRs from main repository (not forks)
        if (isset($pr['headRepository']) && $pr['headRepository']['name'] === $this->repo) {
            $score += 50;
        }

        return $score;
    }

    /**
     * Get recommendation reason for multiple PRs
     */
    private function getRecommendationReason(array $prs): string
    {
        $count = count($prs);
        $recommended = $prs[0];

        $parts = [];
        $parts[] = "PR #{$recommended['number']} selected as most complete and recent";

        $others = array_slice($prs, 1);
        foreach ($others as $pr) {
            $reason = $this->getWhyNotSelected($recommended, $pr);
            $parts[] = "PR #{$pr['number']} ({$reason})";
        }

        return implode('. ', $parts);
    }

    /**
     * Get reason why PR wasn't selected
     */
    private function getWhyNotSelected(array $selected, array $other): string
    {
        $selectedDate = new DateTime($selected['updatedAt']);
        $otherDate = new DateTime($other['updatedAt']);
        $daysDiff = $selectedDate->diff($otherDate)->days;

        if ($daysDiff > 0) {
            return "older by {$daysDiff} days";
        }

        $selectedChanges = $selected['additions'] + $selected['deletions'];
        $otherChanges = $other['additions'] + $other['deletions'];

        if ($selectedChanges > $otherChanges) {
            return "less changes";
        }

        return "less recent";
    }

    /**
     * Generate markdown report
     */
    private function generateReport(): void
    {
        echo "Generating consolidation report...\n\n";

        $output = "# PR Consolidation Report\n\n";
        $output .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "Total Issues Analyzed: " . count($this->report) . "\n\n";

        $readyToMerge = array_filter($this->report, fn($r) => $r['recommendation'] === 'READY_TO_MERGE');
        $needsReview = array_filter($this->report, fn($r) => $r['recommendation'] === 'NEEDS_REVIEW');
        $stale = array_filter($this->report, fn($r) => $r['recommendation'] === 'STALE');

        $output .= "## Summary\n\n";
        $output .= "- **Ready to Merge**: " . count($readyToMerge) . " issues\n";
        $output .= "- **Needs Review**: " . count($needsReview) . " issues\n";
        $output .= "- **Stale**: " . count($stale) . " issues\n\n";

        $output .= "## Ready to Merge\n\n";
        foreach ($readyToMerge as $r) {
            $output .= $this->formatIssueSection($r);
        }

        if (count($needsReview) > 0) {
            $output .= "\n## Needs Review\n\n";
            foreach ($needsReview as $r) {
                $output .= $this->formatIssueSection($r);
            }
        }

        if (count($stale) > 0) {
            $output .= "\n## Stale PRs (14+ days no activity)\n\n";
            foreach ($stale as $r) {
                $output .= $this->formatIssueSection($r);
            }
        }

        file_put_contents(__DIR__ . '/PR_CONSOLIDATION_REPORT.md', $output);

        echo "Report generated: PR_CONSOLIDATION_REPORT.md\n";
    }

    /**
     * Format a single issue section in the report
     */
    private function formatIssueSection(array $r): string
    {
        $pr = $r['prs'][0];
        $output = "### Issue #{$r['issue']}\n\n";
        $output .= "**Recommendation**: {$r['recommendation']}\n\n";
        $output .= "**Reason**: {$r['reason']}\n\n";

        if (count($r['prs']) === 1) {
            $output .= "**PR**: [#{$pr['number']}]({$pr['url']})\n";
            $output .= "- **Title**: {$pr['title']}\n";
            $output .= "- **Author**: {$pr['author']['login']}\n";
            $output .= "- **Updated**: {$pr['updatedAt']}\n";
            $output .= "- **Changes**: +{$pr['additions']}, -{$pr['deletions']}\n\n";
        } else {
            $output .= "**Recommended PR**: [#{$r['recommended_pr']}]({$pr['url']})\n\n";
            $output .= "**All PRs for this issue**:\n\n";
            foreach ($r['prs'] as $p) {
                $marker = $p['number'] === $r['recommended_pr'] ? '✅' : '❌';
                $output .= "- {$marker} **PR #{$p['number']}**: {$p['title']} ({$p['url']})\n";
                $output .= "  - Author: {$p['author']['login']}\n";
                $output .= "  - Updated: {$p['updatedAt']}\n";
                $output .= "  - Changes: +{$p['additions']}, -{$p['deletions']}\n\n";
            }
        }

        $output .= "---\n\n";

        return $output;
    }
}

$analyzer = new PRAnalyzer();
