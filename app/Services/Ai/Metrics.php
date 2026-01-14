<?php

namespace App\Services\Ai;

class Metrics
{
    /**
     * @param array<int|string> $trueLabels
     * @param array<int|string|null> $predLabels
     * @return array{
     *   labels: array<int, int|string>,
     *   confusion: array<int|string, array<int|string, int>>,
     *   accuracy: float,
     *   perLabel: array<int|string, array{precision: float|null, recall: float|null, f1: float|null}>,
     *   macroF1: float|null
     * }
     */
    public static function compute(array $trueLabels, array $predLabels): array
    {
        $labels = array_values(array_unique(array_merge(
            array_map('strval', $trueLabels),
            array_map(fn($p) => $p === null ? 'null' : strval($p), $predLabels)
        )));

        // Initialize confusion matrix
        $confusion = [];
        foreach ($labels as $l1) {
            $confusion[$l1] = [];
            foreach ($labels as $l2) {
                $confusion[$l1][$l2] = 0;
            }
        }

        $total = count($trueLabels);
        $correct = 0;
        for ($i = 0; $i < $total; $i++) {
            $t = strval($trueLabels[$i]);
            $p = $predLabels[$i];
            $p = $p === null ? 'null' : strval($p);
            if (!isset($confusion[$t])) {
                $confusion[$t] = array_fill_keys($labels, 0);
            }
            if (!isset($confusion[$t][$p])) {
                $confusion[$t][$p] = 0;
            }
            $confusion[$t][$p]++;
            if ($t === $p) {
                $correct++;
            }
        }

        $accuracy = $total > 0 ? $correct / $total : 0.0;

        // Precision/Recall/F1 per label
        $perLabel = [];
        $f1s = [];
        foreach ($labels as $label) {
            $tp = $confusion[$label][$label] ?? 0;
            $fp = 0;
            $fn = 0;
            foreach ($labels as $other) {
                if ($other !== $label) {
                    $fp += $confusion[$other][$label] ?? 0; // predicted as label but true is other
                    $fn += $confusion[$label][$other] ?? 0; // true label but predicted other
                }
            }
            $precision = ($tp + $fp) > 0 ? $tp / ($tp + $fp) : null;
            $recall = ($tp + $fn) > 0 ? $tp / ($tp + $fn) : null;
            $f1 = ($precision !== null && $recall !== null && ($precision + $recall) > 0)
                ? (2 * $precision * $recall) / ($precision + $recall)
                : null;
            $perLabel[$label] = [
                'precision' => $precision,
                'recall' => $recall,
                'f1' => $f1,
            ];
            if ($f1 !== null) {
                $f1s[] = $f1;
            }
        }
        $macroF1 = count($f1s) > 0 ? array_sum($f1s) / count($f1s) : null;

        return [
            'labels' => $labels,
            'confusion' => $confusion,
            'accuracy' => $accuracy,
            'perLabel' => $perLabel,
            'macroF1' => $macroF1,
        ];
    }
}
