<?php

namespace App\Services;

use App\Models\Part;
use App\Models\Variant;
use Illuminate\Validation\ValidationException;

class TradeInQuoteService
{
    private const QUESTION_OPTIONS = [
        'power' => ['yes' => 'ok', 'no' => 'floor'],
        'repaired' => ['never' => 'ok', 'authorised' => 'repaired', 'thirdparty' => 'repaired'],
        'battery' => ['90' => 'ok', '85' => 'tier1', '80' => 'tier2', 'below80' => 'broken'],
        'cracks' => ['intact' => 'ok', 'cracked' => 'broken'],
        'scratches' => ['mint' => 'ok', 'light' => 'tier1', 'deep' => 'tier2'],
        'hardware' => ['all_ok' => 'ok', 'issue' => 'broken'],
        'cameras' => ['ok' => 'ok', 'bad' => 'broken'],
        'connectivity' => ['ok' => 'ok', 'bad' => 'broken'],
        'biometrics' => ['ok' => 'ok', 'bad' => 'broken'],
    ];

    private const QUESTION_TYPES = [
        'power' => [0],
        'repaired' => [1],
        'battery' => [2],
        'cracks' => [3, 4],
        'scratches' => [3, 4],
        'hardware' => [5, 6, 7, 8, 9, 10, 11, 14, 15, 16, 17, 18, 19],
        'cameras' => [12, 13],
        'connectivity' => [22, 23, 24, 25],
        'biometrics' => [20, 21],
    ];

    private const FOLLOW_UPS = [
        'repaired_parts' => [
            'screen' => [3],
            'battery' => [2],
            'back' => [4],
            'charging' => [5],
            'camera' => [12, 13],
            'other' => [],
        ],
        'cracked_parts' => ['front' => [3], 'back' => [4]],
        'hardware_parts' => [
            'charging' => [5],
            'buttons' => [6, 14, 15],
            'audio' => [7, 8, 9, 10, 11],
            'sensors' => [16, 17, 18, 19],
        ],
        'camera_parts' => ['front' => [12], 'back' => [13]],
        'connectivity_parts' => [
            'wifi' => [22],
            'bluetooth' => [23],
            'gps' => [24],
            'nfc' => [25],
        ],
    ];

    private const SEVERITY_RANK = [
        'ok' => 0,
        'tier1' => 1,
        'tier2' => 2,
        'tier3' => 3,
        'broken' => 4,
    ];

    public function __construct(private readonly PriceCalculationService $calculator)
    {
    }

    /**
     * Calculate the customer-facing quote from the answers and current catalog data.
     *
     * @param array<string, mixed> $answers
     */
    public function calculate(int $variantId, array $answers): float
    {
        $variant = Variant::with('product')->findOrFail($variantId);
        $parts = Part::where('product_id', $variant->product_id)->get();
        $partsByType = $parts->groupBy(fn (Part $part) => (int) $part->type);
        $worstByPart = [];
        $repairedByPart = [];
        $floorsToWaste = false;

        foreach (self::QUESTION_OPTIONS as $question => $options) {
            $answer = $answers[$question] ?? null;
            if (!is_string($answer) || !isset($options[$answer])) {
                throw ValidationException::withMessages([
                    'conditionAnswers.' . $question => 'Select a valid answer for this condition question.',
                ]);
            }

            $severity = $options[$answer];
            if ($severity === 'floor') {
                $floorsToWaste = true;
                continue;
            }
            if ($severity === 'ok') {
                continue;
            }

            $types = self::QUESTION_TYPES[$question];
            $followUpId = $this->followUpId($question, $answer);
            if ($followUpId !== null) {
                $selections = $answers[$followUpId] ?? null;
                $choices = self::FOLLOW_UPS[$followUpId];
                if (!is_array($selections) || count($selections) === 0) {
                    throw ValidationException::withMessages([
                        'conditionAnswers.' . $followUpId => 'Select at least one affected component.',
                    ]);
                }

                $types = [];
                foreach ($selections as $selection) {
                    if (!is_string($selection) || !array_key_exists($selection, $choices)) {
                        throw ValidationException::withMessages([
                            'conditionAnswers.' . $followUpId => 'Select valid affected components.',
                        ]);
                    }
                    $types = array_merge($types, $choices[$selection]);
                }

                if ($types === []) {
                    $types = self::QUESTION_TYPES[$question];
                }
            }

            foreach ($types as $type) {
                foreach ($partsByType->get($type, collect()) as $part) {
                    $partId = (string) $part->id;
                    if ($severity === 'repaired') {
                        $repairedByPart[$partId] = (int) $part->id;
                        continue;
                    }
                    $current = $worstByPart[$partId] ?? 'ok';
                    if (self::SEVERITY_RANK[$severity] > self::SEVERITY_RANK[$current]) {
                        $worstByPart[$partId] = $severity;
                    }
                }
            }
        }

        if ($floorsToWaste) {
            return (float) ($variant->product?->waste_price ?? 0);
        }

        $repairedPartsBefore = [];
        $deduction = 0.0;
        $partsWithValuesMap = $parts->mapWithKeys(
            fn (Part $part) => [
                (string) $part->id => (int) $part->type === 2 ? '100' : '1',
            ]
        )->all();

        foreach ($parts as $part) {
            $partId = (string) $part->id;
            $severity = $worstByPart[$partId] ?? 'ok';
            if ($severity === 'broken') {
                $deduction += (float) $part->price;
                continue;
            }
            if (in_array($severity, ['tier1', 'tier2', 'tier3'], true)) {
                $info = is_array($part->info) ? $part->info : [];
                $deduction += (float) ($info['price' . substr($severity, -1)] ?? 0);
                continue;
            }
            if (isset($repairedByPart[$partId])) {
                $repairedPartsBefore[] = $repairedByPart[$partId];
            }
        }

        $baseline = $this->calculator->calculate(
            $variantId,
            $partsWithValuesMap,
            $repairedPartsBefore
        )['finalPrice'];
        $wastePrice = (float) ($variant->product?->waste_price ?? 0);

        return max($wastePrice, round((float) $baseline - $deduction));
    }

    private function followUpId(string $question, string $answer): ?string
    {
        return match ([$question, $answer]) {
            ['repaired', 'authorised'], ['repaired', 'thirdparty'] => 'repaired_parts',
            ['cracks', 'cracked'] => 'cracked_parts',
            ['hardware', 'issue'] => 'hardware_parts',
            ['cameras', 'bad'] => 'camera_parts',
            ['connectivity', 'bad'] => 'connectivity_parts',
            default => null,
        };
    }
}
