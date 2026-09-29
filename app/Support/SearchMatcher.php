<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Flipkart/Amazon/Meesho-style product search.
 *
 * - Tokenizes the query, drops filler words ("color", "for", "the"…)
 * - Synonym normalisation: "kurthi"/"kurtis"/"kurta" → kurti family,
 *   plurals handled, so "kurthi" finds every kurti.
 * - AND semantics across tokens: "red color kurti" = every token must match
 *   at least one field (name / category / subcategory / colors / material /
 *   fit / sku / description).
 * - Relevance scoring: name > colour > category > material > description,
 *   with rating / best-seller / discount boosts as tiebreakers.
 */
class SearchMatcher
{
    /**
     * Query-token alias groups. Any token in a group matches any other
     * member of that group on either side (query or field).
     */
    public const ALIAS_GROUPS = [
        ['kurti', 'kurtis', 'kurthi', 'kurthis', 'kurtie', 'kurties', 'kurta', 'kurtas'],
        ['top', 'tops'],
        ['legging', 'leggings'],
        ['maxi', 'maxis', 'dress', 'dresses'],
        ['nightwear', 'nighty', 'nighties', 'sleepwear', 'pyjama', 'pyjamas', 'pajama', 'pajamas'],
        ['coord', 'coords', 'co-ord', 'co-ords'],
        ['dupatta', 'duppata', 'duppatta', 'dupatta'],
        ['tshirt', 'tshirt', 'tee', 'tees'],
        ['palazzo', 'palazzos'],
        ['saree', 'sarees', 'sari', 'saris'],
    ];

    /** Words that carry no search meaning on their own. */
    public const STOPWORDS = [
        'a', 'an', 'the', 'of', 'for', 'with', 'and', 'or', 'in', 'on', 'at', 'to',
        'is', 'it', 'by', 'from', 'that', 'this', 'these', 'those', 'my', 'me',
        'your', 'you', 'we', 'us', 'i', 'am', 'are', 'be', 'as', 'so', 'if',
        'then', 'than', 'too', 'very', 'can', 'will', 'just', 'do', 'does', 'did',
        'not', 'no', 'yes', 'buy', 'shop', 'shopping', 'online', 'please', 'show',
        'find', 'get', 'give', 'looking', 'look', 'want', 'need', 'all', 'any',
        'some', 'more', 'most', 'best',
        'color', 'colour', 'colors', 'colours', 'colored', 'coloured',
    ];

    /** Known shades — matched against the product colour swatches too. */
    public const COLORS = [
        'red', 'blue', 'green', 'black', 'white', 'pink', 'yellow', 'orange',
        'purple', 'maroon', 'navy', 'beige', 'grey', 'gray', 'brown', 'gold',
        'golden', 'silver', 'teal', 'lavender', 'coral', 'olive', 'cream',
        'turquoise', 'magenta', 'wine', 'rust', 'peach', 'mint', 'mustard',
        'indigo', 'charcoal', 'ivory', 'khaki', 'fuchsia', 'aqua', 'burgundy',
        'multicolor', 'multi-color', 'offwhite', 'off-white', 'pastel', 'neon',
        'tan', 'cyan', 'bronze', 'copper',
    ];

    /** Normalised, stopword-free, length-filtered query tokens. */
    public static function tokenize(string $query): array
    {
        $q = mb_strtolower(trim($query));
        $q = preg_replace('/[^\p{L}\p{N}\s-]+/u', ' ', (string) $q);
        $q = trim((string) preg_replace('/\s+/', ' ', (string) $q));
        if ($q === '') {
            return [];
        }

        $raw = explode(' ', $q);
        $tokens = array_values(array_filter($raw, fn ($t) => !in_array($t, self::STOPWORDS, true)));
        if ($tokens === []) {
            $tokens = $raw; // query was only filler words — match literally
        }

        // Multi-word queries: drop 1–2 char tokens ("co ord set" → "ord set").
        if (count($tokens) > 1) {
            $long = array_values(array_filter($tokens, fn ($t) => mb_strlen($t) >= 3));
            if ($long !== []) {
                $tokens = $long;
            }
        }

        return array_values(array_unique($tokens));
    }

    /** Every spelling a token may legitimately match. */
    public static function variants(string $token): array
    {
        $set = [$token];

        foreach (self::ALIAS_GROUPS as $group) {
            if (in_array($token, $group, true)) {
                foreach ($group as $member) {
                    $set[] = $member;
                }
                break;
            }
        }

        // Naive plural/singular tolerance for words outside the alias groups.
        if (mb_substr($token, -1) === 's') {
            $set[] = mb_substr($token, 0, -1);
        } else {
            $set[] = $token.'s';
        }

        return array_values(array_unique(array_filter($set)));
    }

    public static function isColor(string $token): bool
    {
        return in_array(mb_strtolower($token), self::COLORS, true);
    }

    /**
     * AND (default) or OR token groups applied to a product query.
     * Each token group ORs across every searchable field.
     *
     * @param  Builder  $query  Eloquent builder for Product (or any builder with the same relations).
     * @param  string  $mode  'and' = every token must match, 'or' = any token may match.
     */
    public static function apply(Builder $query, string $search, string $mode = 'and'): Builder
    {
        $tokens = self::tokenize($search);
        if ($tokens === []) {
            return $query;
        }

        $query->where(function ($outer) use ($tokens, $mode) {
            foreach ($tokens as $token) {
                $variants = self::variants($token);
                $group = function ($q) use ($variants) {
                    foreach ($variants as $variant) {
                        if ($variant === '') {
                            continue;
                        }
                        $like = '%'.$variant.'%';
                        $q->orWhere('name', 'like', $like)
                            ->orWhere('description', 'like', $like)
                            ->orWhere('sku', 'like', $like)
                            ->orWhere('material', 'like', $like)
                            ->orWhere('fit', 'like', $like)
                            ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like))
                            ->orWhereHas('subcategory', fn ($c) => $c->where('name', 'like', $like))
                            ->orWhereHas('colors', fn ($c) => $c->where('name', 'like', $like));
                    }
                };

                if ($mode === 'or') {
                    $outer->orWhere($group);
                } else {
                    $outer->where($group);
                }
            }
        });

        return $query;
    }

    /**
     * Relevance score for a catalog array (see Product::toCatalogArray()).
     * Returns 0 when a token matches nothing and $strict is true.
     *
     * @param  array{ name?: string, colors?: array, category?: string, subcategory?: string, material?: string, fit?: string, sku?: string, description?: string, rating?: float, discount?: float, best_seller?: bool, trending?: bool, featured?: bool }  $product
     */
    public static function score(array $product, array $tokens, bool $strict = true): float
    {
        $name = mb_strtolower((string) ($product['name'] ?? ''));
        $colors = mb_strtolower(implode(' ', array_map('strval', (array) ($product['colors'] ?? []))));
        $cat = mb_strtolower((string) ($product['category'] ?? ''));
        $sub = mb_strtolower((string) ($product['subcategory'] ?? ''));
        $mat = mb_strtolower((string) ($product['material'] ?? ''));
        $fit = mb_strtolower((string) ($product['fit'] ?? ''));
        $sku = mb_strtolower((string) ($product['sku'] ?? ''));
        $desc = mb_strtolower((string) ($product['description'] ?? ''));

        $score = 0.0;
        $anyMatched = false;

        foreach ($tokens as $token) {
            $best = 0.0;

            foreach (self::variants($token) as $variant) {
                if ($variant === '') {
                    continue;
                }
                if ($name !== '' && str_contains($name, $variant)) {
                    $best = max($best, str_starts_with($name, $variant) ? 100.0 : 80.0);
                }
                if ($colors !== '' && str_contains($colors, $variant)) {
                    $best = max($best, self::isColor($token) ? 90.0 : 70.0);
                }
                if ($cat !== '' && str_contains($cat, $variant)) {
                    $best = max($best, 60.0);
                }
                if ($sub !== '' && str_contains($sub, $variant)) {
                    $best = max($best, 50.0);
                }
                if ($mat !== '' && str_contains($mat, $variant)) {
                    $best = max($best, 45.0);
                }
                if (($fit !== '' && str_contains($fit, $variant)) || ($sku !== '' && str_contains($sku, $variant))) {
                    $best = max($best, 40.0);
                }
                if ($desc !== '' && str_contains($desc, $variant)) {
                    $best = max($best, 25.0);
                }
                if ($best >= 80.0) {
                    break;
                }
            }

            if ($best === 0.0 && $strict) {
                return 0.0;
            }
            if ($best > 0.0) {
                $anyMatched = true;
            }
            $score += $best;
        }

        // OR-mode fallback: drop products where no token matched at all.
        if (!$anyMatched) {
            return 0.0;
        }

        $score += min(20.0, (float) ($product['rating'] ?? 0) * 4);
        if (!empty($product['best_seller'])) {
            $score += 8.0;
        }
        if (!empty($product['trending'])) {
            $score += 6.0;
        }
        if (!empty($product['featured'])) {
            $score += 4.0;
        }
        $score += min(10.0, (float) ($product['discount'] ?? 0) / 5);

        return $score;
    }

    /**
     * Filter (by relevance) + rank catalog arrays for a query.
     * Empty query → untouched order (browse mode).
     *
     * @return array<int, array>
     */
    public static function rank(array $products, string $search, bool $strict = true): array
    {
        $tokens = self::tokenize($search);
        if ($tokens === []) {
            return array_values($products);
        }

        $scored = [];
        foreach ($products as $product) {
            $s = self::score($product, $tokens, $strict);
            if ($s > 0) {
                $scored[] = [$product, $s];
            }
        }

        usort($scored, function ($a, $b) {
            if ($a[1] !== $b[1]) {
                return $b[1] <=> $a[1];
            }
            $ra = (float) ($a[0]['rating'] ?? 0);
            $rb = (float) ($b[0]['rating'] ?? 0);
            if ($ra !== $rb) {
                return $rb <=> $ra;
            }
            return ((int) ($b[0]['id'] ?? 0)) <=> ((int) ($a[0]['id'] ?? 0));
        });

        return array_map(fn ($row) => $row[0], $scored);
    }
}
