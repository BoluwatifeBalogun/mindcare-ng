<?php
/* Risk engine — direct port of the validated JS pipeline (Figure 3.3).
   Runs SERVER-SIDE so the student's browser cannot tamper with escalation. */

const TIER3 = ['/suicid/i', '/kill (myself|me)/i', '/end (my|it all|my life)/i',
    "/don'?t want to (live|be alive|exist)/i", '/take my (own )?life/i',
    '/hurt(ing)? myself/i', '/self[- ]?harm/i', '/no reason to live/i',
    '/better off (dead|without me)/i'];
const TIER2 = ['/hopeless/i', '/worthless/i', "/can'?t (go on|cope|take (it|this) anymore)/i",
    '/give up/i', '/hate myself/i', '/nobody cares/i', '/no way out/i',
    '/empty inside/i', '/panic attack/i'];

function pre_scan(string $t): string {
    foreach (TIER3 as $r) if (preg_match($r, $t)) return 'high';
    foreach (TIER2 as $r) if (preg_match($r, $t)) return 'moderate';
    return 'low';
}
function max_risk(string $a, string $b): string {
    $o = ['low' => 0, 'moderate' => 1, 'high' => 2];
    return $o[$a] >= $o[$b] ? $a : $b;
}
/* Item-8 resolver: keyword scanner is high-recall/low-precision; the model reads
   context. Agreement on high -> crisis. Disagreement -> supportive banner + human
   review, never silence. $model === null means the API was unreachable (fail-safe). */
function resolve_risk(string $pre, ?string $model): array {
    if ($model === null) {
        return ['risk' => $pre, 'action' => $pre === 'high' ? 'crisis' : ($pre === 'moderate' ? 'soft' : 'none')];
    }
    if ($pre === 'high' && $model !== 'high') return ['risk' => 'moderate', 'action' => 'review'];
    $risk = max_risk($pre, $model);
    return ['risk' => $risk, 'action' => $risk === 'high' ? 'crisis' : ($risk === 'moderate' ? 'soft' : 'none')];
}

/* Assessment scoring — published cutoffs (Kroenke et al.; Spitzer et al.) */
function phq_band(int $s): string {
    return $s <= 4 ? 'Minimal' : ($s <= 9 ? 'Mild' : ($s <= 14 ? 'Moderate' : ($s <= 19 ? 'Moderately severe' : 'Severe')));
}
function gad_band(int $s): string {
    return $s <= 4 ? 'Minimal' : ($s <= 9 ? 'Mild' : ($s <= 14 ? 'Moderate' : 'Severe'));
}
