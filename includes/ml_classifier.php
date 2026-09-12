<?php
/* Trained risk classifier - native PHP scorer.
   Model: TF-IDF (1-2 grams) + multinomial logistic regression, trained on the
   CounselChat corpus (Bertagnolli, 2020) by ml/train_classifier.py and exported
   to includes/risk_model.json. This file replicates scikit-learn's transform
   exactly (same token pattern, smooth idf, l2 norm), so predictions match the
   Python model - verified by ml/parity_check.py.

   ml_scan($text)  -> 'low' | 'moderate' | 'high', or null if the model file
                      is absent (the pipeline then runs exactly as before). */

function ml_model(): ?array {
    static $m = false;
    if ($m === false) {
        $path = __DIR__ . '/risk_model.json';
        $m = is_readable($path) ? json_decode(file_get_contents($path), true) : null;
        if ($m !== null && (!isset($m['vocabulary'], $m['idf'], $m['coef'], $m['intercept'], $m['classes']))) $m = null;
    }
    return $m;
}

function ml_scan(string $text): ?string {
    $p = ml_probs($text);
    if ($p === null) return null;
    return array_keys($p, max($p))[0];
}

function ml_probs(string $text): ?array {
    $m = ml_model();
    if ($m === null) return null;

    /* tokenise like sklearn: lowercase, (?u)\b\w\w+\b, then add bigrams */
    $lower = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    preg_match_all('/(*UCP)\b\w\w+\b/u', $lower, $mt);
    $tokens = $mt[0];
    $counts = [];
    foreach ($tokens as $t) $counts[$t] = ($counts[$t] ?? 0) + 1;
    $n = count($tokens);
    for ($i = 0; $i < $n - 1; $i++) {
        $bg = $tokens[$i] . ' ' . $tokens[$i + 1];
        $counts[$bg] = ($counts[$bg] ?? 0) + 1;
    }

    /* tf-idf on known vocabulary, then l2 normalise */
    $x = []; $norm = 0.0;
    foreach ($counts as $term => $c) {
        if (!isset($m['vocabulary'][$term])) continue;
        $idx = $m['vocabulary'][$term];
        $v = $c * $m['idf'][$idx];
        $x[$idx] = $v;
        $norm += $v * $v;
    }
    if (!$x) return ['low' => 1.0, 'moderate' => 0.0, 'high' => 0.0]; // no known words
    $norm = sqrt($norm);

    /* logits -> softmax */
    $logits = [];
    foreach ($m['classes'] as $ci => $cls) {
        $z = $m['intercept'][$ci];
        foreach ($x as $idx => $v) $z += $m['coef'][$ci][$idx] * ($v / $norm);
        $logits[$cls] = $z;
    }
    $mx = max($logits); $sum = 0.0; $probs = [];
    foreach ($logits as $cls => $z) { $probs[$cls] = exp($z - $mx); $sum += $probs[$cls]; }
    foreach ($probs as $cls => $v) $probs[$cls] = $v / $sum;
    return $probs;
}

/* Combined local screening: keyword rules + trained classifier, worst wins.
   This is the "pre" input to resolve_risk(); the LLM's contextual read still
   arbitrates disagreements exactly as before (item-8 resolver). */
function local_scan(string $text): array {
    $kw = pre_scan($text);
    $ml = ml_scan($text);
    $risk = $ml === null ? $kw : max_risk($kw, $ml);
    return ['risk' => $risk, 'kw' => $kw, 'ml' => $ml];
}
