<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Rule-based shop assistant (no AI integration).
 *
 * Understands everyday customer language via intent patterns and answers
 * from live database data: real orders, product catalog (names, prices,
 * stock, material...), categories and active coupons. Replies are
 * server-generated HTML with links to real store pages.
 */
class ChatBotController extends Controller
{
    public function reply(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $raw = trim($data['message']);
        $text = mb_strtolower($raw);

        // 1) Explicit order number anywhere (e.g. ZYRA-AB12CD).
        if (preg_match('/\bzyra-[a-z0-9]{6}\b/i', $raw, $m)) {
            return $this->json($this->orderByNumber(strtoupper($m[0])));
        }

        // 2) Order tracking.
        if ($this->matches($text, ['where.*(order|parcel|package|courier)', '(order|parcel|package).*(status|track|where|reach|arrive|dispatch|delay|late|stuck|help)', 'help.*(order|parcel|package)', 'track.*(order|parcel|package)', 'delivery status', 'has.*(shipped|dispatched)', 'when.*(arrive|deliver|receive|get).*(order|parcel|package)'])) {
            return $this->json($this->orderStatus());
        }

        // 3) Order history / purchases.
        if ($this->matches($text, ['my orders', 'my purchas', 'order history', 'past orders', 'purchase history', 'all.*orders', 'show.*orders', 'list.*orders', 'i bought', 'what.*i.*(ordered|bought)'])) {
            return $this->json($this->myOrders());
        }

        // 4) Coupons & offers (live from DB).
        if ($this->matches($text, ['coupon', 'promo', 'voucher', 'firstorder', 'welcome10', 'fashion15', 'save20', 'offers?', 'deals?', 'cashback', 'discount code', 'coupon code', '\bpromo code\b'])) {
            return $this->json($this->couponReply());
        }

        // 5) Returns / refunds / exchange.
        if ($this->matches($text, ['return', 'refund', 'exchange', 'replace.*(item|product|size)', 'money back', 'damage|defective|torn|stain', 'wrong.*(item|product|colour|color)', 'received.*wrong'])) {
            return $this->json(
                'We offer <b>easy 7-day returns &amp; exchanges</b> — unworn, with tags, in original condition.<br>' .
                '• Refunds reach your source account within <b>5–7 business days</b> after pickup<br>' .
                '• Size exchange? Same 7-day window, free guidance<br><br>' .
                'Start from your <a href="/my-orders">My Orders</a> page or <a href="/contact">contact us here</a> with your order number.'
            );
        }

        // 6) Cancellation.
        if ($this->matches($text, ['cancel.*order', 'stop.*order', 'don\'t want.*order', 'cancel my'])) {
            return $this->json(
                'You can cancel while the order is still <b>Pending / Processing</b> — just <a href="/contact">contact us here</a> with your order number.<br><br>' .
                'Once shipped it can\'t be cancelled, but the 7-day return still applies.'
            );
        }

        // 7) Failed payment / money deducted.
        if ($this->matches($text, ['payment.*(fail|declin|error|stuck|issue)', 'money.*deduct', 'deduct.*(money|amount|account)', 'paid.*but', 'amount.*(debit|gone)', 'transaction.*fail', 'debited'])) {
            return $this->json(
                'If money was deducted but no order was placed, don\'t worry — failed payments are <b>auto-refunded within 5–7 business days</b> by your bank/Razorpay.<br><br>' .
                'Check <a href="/my-orders">My Orders</a>: if the order isn\'t there, it didn\'t go through and you can safely order again. Still stuck? <a href="/contact">Contact us</a> with a screenshot.'
            );
        }

        // 8) Address change.
        if ($this->matches($text, ['change.*address', 'wrong address', 'update.*address', 'edit.*address', 'address.*(change|wrong|mistake|update)', 'different address'])) {
            return $this->json(
                'If your order is still <b>Pending / Processing</b>, <a href="/contact">contact us immediately</a> with the correct address and we\'ll fix it.<br><br>' .
                'Once shipped the address can\'t be changed — please watch for the courier\'s call.'
            );
        }

        // 9) Invoice / bill.
        if ($this->matches($text, ['invoice', '\bbill\b', 'receipt', '\bgst\b'])) {
            return $this->json('Every parcel carries a proper bill. Need a GST invoice? <a href="/contact">Contact us</a> with your order number and GSTIN and we\'ll email it.');
        }

        // 10) Pincode / serviceability.
        if ($this->matches($text, ['pincode', 'pin code', 'postal', 'serviceable', 'deliver.*(my|to).*(city|area|place|location|town|village)'])) {
            return $this->json(
                'We deliver <b>across India in 3–5 business days</b>. Just enter your pincode at checkout — it instantly confirms delivery and COD for your area.<br><br>' .
                'Unsure about your location? <a href="/contact">Ask us here</a>.'
            );
        }

        // 11) Fabric (generic).
        if ($this->matches($text, ['what.*(fabric|material).*use', 'which.*fabric', 'is.*(it|this).*cotton', 'pure cotton', 'fabric quality', 'cloth quality'])) {
            return $this->json('Our collection focuses on soft, breathable everyday fabrics like cotton — and every product page lists its exact fabric under Details. <a href="/shop">Browse the collection →</a>');
        }

        // 12) Wash care (generic).
        if ($this->matches($text, ['\bwash\b', 'washing', 'laundry', 'dry clean', 'care.*(cloth|fabric|garment|kurti)'])) {
            return $this->json('Easy care: <b>machine-wash cold with similar colours, dry in shade</b>. Each product page also lists its own care instructions under Details.');
        }

        // 13) Account / login help.
        if ($this->matches($text, ['login', 'log in', 'sign in', 'register', 'sign up', 'create.*account', 'password', '\botp\b', 'my account'])) {
            return $this->json('Manage everything from your account: <a href="/login">Log in →</a> • <a href="/my-orders">My Orders →</a><br><br>New here? Registration takes a minute on the login page.');
        }

        // 14) Bag / cart help.
        if ($this->matches($text, ['\bcart\b', '\bbag\b', 'basket', 'add to (bag|cart)', 'checkout.*(help|issue|problem|stuck)', 'items.*cart'])) {
            return $this->json('Your bag is saved automatically. <a href="/cart">Open your bag →</a><br><br>Tip: type coupon codes at checkout — ask me <i>“any offers?”</i> for live codes.');
        }

        // 15) Shipping info.
        if ($this->matches($text, ['ship', 'deliver', 'dispatch', 'how long', 'how many days', 'delivery time', 'charges', 'shipping cost', 'free.*(ship|deliver)'])) {
            return $this->json(
                'Shipping is <b>FREE above ₹999</b> (small fee below that). Delivery takes about <b>3–5 business days</b>, pan-India, and <b>COD is available</b>.<br><br>' .
                'Track any order from <a href="/my-orders">My Orders</a>.'
            );
        }

        // 16) COD.
        if ($this->matches($text, ['cod', 'cash on delivery', 'cash.*delivery', 'pay.*(deliver|receive|parcel)'])) {
            return $this->json('Yes — <b>Cash on Delivery is available</b>. Choose COD at checkout and pay in cash/UPI when your parcel arrives.');
        }

        // 17) Payment methods.
        if ($this->matches($text, ['payment', '\bpay\b', 'upi', 'card', 'netbanking', 'net banking', 'razorpay', 'emi', 'how.*pay'])) {
            return $this->json('We accept <b>UPI, Credit/Debit Cards and Net Banking</b> (via Razorpay), plus <b>Cash on Delivery</b>.');
        }

        // 18) Product questions (DB-driven: search, price, stock, categories...).
        $productAnswer = $this->productAnswer($raw, $text);
        if ($productAnswer !== null) {
            return $this->json($productAnswer);
        }

        // 19) Size guide (generic — product-specific sizes are answered with product data above).
        if ($this->matches($text, ['size', '\bfit\b', 'measurement', 'size chart', 'size guide', 'which size'])) {
            return $this->json('Our fits run <b>true to size</b> — check the size table on any product page. Between sizes? Take the larger one.<br><br>Wrong size? Easy exchange within 7 days — <a href="/contact">contact us</a>.');
        }

        // 20) Store info.
        if ($this->matches($text, ['\bstore\b', 'offline', 'outlet', 'visit.*shop', 'where.*located', 'physical.*(store|shop)', 'shop.*(address|location)'])) {
            return $this->json('ZYRA is an <b>online store delivering across India</b> — shop anytime at <a href="/shop">zyralifestyle.in/shop</a>. Anything else? <a href="/contact">Contact us →</a>');
        }

        // 21) Bulk / wholesale.
        if ($this->matches($text, ['bulk', 'wholesale', 'reseller', 'boutique', 'large quantity', 'many pieces', 'wedding order', 'event order'])) {
            return $this->json('For bulk orders, <a href="/contact">contact us here</a> with your quantities — we\'ll work out the best price for you.');
        }

        // 22) Courier / tracking id.
        if ($this->matches($text, ['courier', 'delhivery', 'ekart', 'xpressbees', 'shiprocket', 'tracking (id|number)', '\bawb\b', 'lr number', 'which.*(courier|carrier)'])) {
            return $this->json('We ship via trusted courier partners. Once shipped, the <b>tracking (AWB) number appears on your <a href="/my-orders">My Orders</a> page</b> — track it there. <a href="/contact">Need help? →</a>');
        }

        // 23) Human / contact.
        if ($this->matches($text, ['contact', 'support', 'help', 'human', 'agent', 'call', 'phone', 'email', 'whatsapp', 'instagram', 'complaint', 'talk.*(someone|person)', 'customer care', 'customer support'])) {
            return $this->json(
                'Reach our team:<br>' .
                '• Phone: <b>+91 9884125555</b><br>' .
                '• Email: <b>order@shopwithzyra.in</b><br>' .
                '• Instagram: <b>@zyraofficial46</b><br><br>' .
                '<a href="/contact">Send us a message →</a>'
            );
        }

        // 24) Small talk (only for short messages, so real questions always win).
        if (mb_strlen($text) < 30) {
            if ($this->matches($text, ['thank', 'thanks', 'thx', 'nandri'])) {
                return $this->json('You are most welcome! Anything else — orders, products, offers or sizes?');
            }
            if ($this->matches($text, ['bye', 'see you', 'good ?night'])) {
                return $this->json('Bye for now — happy shopping! <a href="/shop">New collection →</a>');
            }
            if ($this->matches($text, ['^(hi|hello|hey|hai|vanakkam|good ?(morning|afternoon|evening|day))\\b'])) {
                return $this->json('Hello, and welcome to ZYRA! Try <i>“Show kurtis”</i>, <i>“Where is my order?”</i> or <i>“Any offers?”</i> — or tap a suggestion below.');
            }
        }

        // 25) Fallback.
        return $this->json(
            'I want to get this right! I can help with:<br>' .
            '• <b>Products</b> — try <i>“Show kurtis”</i> or <i>“Dresses under 1500”</i><br>' .
            '• <b>Orders</b> — <i>“Where is my order?”</i> (log in and I fetch it live)<br>' .
            '• <b>Offers, shipping, returns, COD, sizes</b><br><br>' .
            'Or <a href="/contact">talk to our team here →</a>'
        );
    }

    // ------------------------------------------------------------------
    // Product brain (live catalog data)
    // ------------------------------------------------------------------

    protected function productAnswer(string $raw, string $text): ?string
    {
        $norm = $this->norm($raw);
        $category = $this->detectCategory($norm);

        [$minPrice, $maxPrice] = $this->detectPrice($text);

        $isArrival = $this->matches($text, ['new arrival', 'new collection', 'just launch', 'just arrived', 'recently added', '\\bwhat.*new\\b', 'latest.*(product|collection|design)']);
        $isBest = $this->matches($text, ['best.?seller', 'best selling', 'most popular', 'trending', 'top rated', 'popular']);
        $isSale = $this->matches($text, ['\\bsale\\b', 'clearance']);

        $keywords = $this->extractKeywords($norm);

        $trigger = $category !== null || $maxPrice !== null || $minPrice !== null
            || $isArrival || $isBest || $isSale
            || $this->matches($text, ['show', '\\bbuy\\b', 'purchase', 'need', 'want', 'looking', 'search', 'find', 'have', 'got', '\\bsell\\b', 'price', 'cost', 'stock', 'fabric', 'material', 'collection', 'display', 'order a']);

        if (!$trigger) {
            return null;
        }

        // Bare generic words (size? delivery?) with no product context -> let other intents answer.
        $defer = ['size', 'sizes', 'fit', 'fits', 'fitting', 'delivery', 'deliver', 'shipping', 'shipment', 'return', 'returns', 'refund', 'refunds', 'exchange', 'contact', 'payment', 'price', 'prices', 'cost', 'costs'];
        if ($keywords !== [] && $category === null && $maxPrice === null && $minPrice === null && !$isArrival && !$isBest && !$isSale
            && empty(array_diff($keywords, $defer))) {
            return null;
        }

        // Dedicated lists.
        $flagWords = ['new', 'arrival', 'arrivals', 'latest', 'best', 'seller', 'sellers', 'selling', 'bestseller', 'bestsellers', 'trending', 'popular', 'top', 'rated', 'sale', 'clearance', 'collection', 'collections'];
        $core = array_values(array_diff($keywords, $flagWords));

        if ($isArrival && $core === [] && $category === null) {
            $items = Product::query()->latest()->limit(5)->get();
            if ($items->isEmpty()) {
                return 'Nothing new just yet — but the <a href="/shop">current collection</a> is full of favourites.';
            }

            return 'Newest in store:<br>' . $this->productLines($items) . '<br><br><a href="/shop">See everything →</a>';
        }

        if ($isBest && $core === [] && $category === null) {
            $items = Product::query()->where('is_best_seller', true)->limit(5)->get();
            if ($items->isEmpty()) {
                $items = Product::query()->orderByDesc('rating')->limit(5)->get();
            }
            if ($items->isEmpty()) {
                return 'Our bestsellers keep selling out! Browse the <a href="/shop">full collection →</a>';
            }

            return 'Our most loved pieces:<br>' . $this->productLines($items) . '<br><br><a href="/shop">Shop all →</a>';
        }

        if ($isSale && $core === [] && $category === null) {
            $items = Product::query()
                ->where(function ($q) {
                    $q->whereColumn('old_price', '>', 'price')->orWhere('discount', '>', 0);
                })
                ->orderByDesc('discount')->limit(5)->get();
            if ($items->isEmpty()) {
                return 'No sale running right now — but do ask me <i>“any offers?”</i> for live coupon codes. <a href="/shop">Shop →</a>';
            }

            return 'On sale right now:<br>' . $this->productLines($items) . '<br><br><a href="/shop">Grab them →</a>';
        }

        // Category browse (no keywords).
        if ($category !== null && $keywords === []) {
            $items = $this->searchProducts([], $category->id, $minPrice, $maxPrice);
            $priceNote = $maxPrice !== null ? ' under ₹' . number_format($maxPrice, 0) : '';
            if ($items->isEmpty()) {
                return 'Nothing in <b>' . e($category->name) . '</b>' . $priceNote . ' right now. <a href="/category/' . e($category->slug) . '">See the full ' . e($category->name) . ' range →</a>';
            }

            return 'In <b>' . e($category->name) . '</b>' . $priceNote . ':<br>' . $this->productLines($items)
                . '<br><br><a href="/category/' . e($category->slug) . '">See all ' . e($category->name) . ' →</a>';
        }

        if ($keywords === []) {
            return null;
        }

        // Keyword search: names first, then description/material.
        $items = $this->searchProducts($keywords, $category?->id, $minPrice, $maxPrice);
        if ($items->isEmpty()) {
            $items = $this->searchProducts($keywords, $category?->id, $minPrice, $maxPrice, 5, true);
        }

        if ($items->isEmpty()) {
            // Fall back to the detected category shelf before giving up.
            if ($category !== null) {
                $catItems = $this->searchProducts([], $category->id, $minPrice, $maxPrice);
                if (!$catItems->isEmpty()) {
                    return 'No exact match, but here is our <b>' . e($category->name) . '</b> range:<br>' . $this->productLines($catItems)
                        . '<br><br><a href="/category/' . e($category->slug) . '">See all ' . e($category->name) . ' →</a>';
                }
            }
            $kw = implode(' ', array_slice($keywords, 0, 3));
            $scope = '';
            if ($maxPrice !== null) {
                $scope .= ' under ₹' . number_format($maxPrice, 0);
            }
            if ($minPrice !== null) {
                $scope .= ' above ₹' . number_format($minPrice, 0);
            }
            $more = $category !== null
                ? '<a href="/category/' . e($category->slug) . '">Browse ' . e($category->name) . ' →</a>'
                : '<a href="/shop">Browse everything →</a>';

            return 'Hmm, I couldn\'t find anything for “<b>' . e($kw) . '</b>”' . e($scope) . '. Try fewer words (e.g. <i>“cotton kurti”</i>) — or ' . $more;
        }

        $detailWords = $this->matches($text, ['price', 'cost', 'how much', 'stock', 'available', 'left', 'fabric', 'material', 'made of', 'detail', 'specification', 'info', 'about this', 'size', '\\bfit\\b']);

        if ($items->count() === 1 && $detailWords) {
            return $this->productCard($items->first());
        }

        $prefix = $maxPrice !== null ? 'Top matches under ₹' . number_format($maxPrice, 0) : 'Here\'s what I found';
        $more = $category !== null
            ? '<br><br><a href="/category/' . e($category->slug) . '">More ' . e($category->name) . ' →</a>'
            : '<br><br><a href="/shop">See more →</a>';

        return $prefix . ':<br>' . $this->productLines($items) . $more;
    }

    protected function searchProducts(array $keywords, ?int $categoryId, ?int $minPrice, ?int $maxPrice, int $limit = 5, bool $extended = false)
    {
        $q = Product::query();
        if ($categoryId !== null) {
            $q->where('category_id', $categoryId);
        }
        if ($minPrice !== null) {
            $q->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $q->where('price', '<=', $maxPrice);
        }
        if ($keywords !== []) {
            $q->where(function ($w) use ($keywords, $extended) {
                foreach ($keywords as $i => $k) {
                    $like = '%' . $k . '%';
                    if ($i === 0) {
                        $w->where('name', 'like', $like);
                    } else {
                        $w->orWhere('name', 'like', $like);
                    }
                    if ($extended) {
                        $w->orWhere('description', 'like', $like)->orWhere('material', 'like', $like);
                    }
                }
            });
        }

        return $q->orderByDesc('in_stock')->orderByDesc('rating')->limit($limit)->get();
    }

    protected function productLines($items): string
    {
        return implode('<br>', $items->map(fn ($p) => '• ' . $this->productLine($p))->all());
    }

    protected function productLine(Product $p): string
    {
        $line = '<a href="/product/' . $p->id . '">' . e($p->name) . '</a> — ₹' . number_format((float) $p->price, 0);

        if ($p->old_price && (float) $p->old_price > (float) $p->price) {
            $off = (int) round((1 - (float) $p->price / (float) $p->old_price) * 100);
            $line .= ' <s>₹' . number_format((float) $p->old_price, 0) . '</s> (' . $off . '% off)';
        }

        $line .= ' — ' . $this->stockText($p);

        if ($p->rating) {
            $line .= ' ⭐' . rtrim(rtrim((string) $p->rating, '0'), '.');
        }

        return $line;
    }

    protected function productCard(Product $p): string
    {
        $html = '<b><a href="/product/' . $p->id . '">' . e($p->name) . '</a></b><br>' .
            'Price: <b>₹' . number_format((float) $p->price, 0) . '</b>';

        if ($p->old_price && (float) $p->old_price > (float) $p->price) {
            $off = (int) round((1 - (float) $p->price / (float) $p->old_price) * 100);
            $html .= ' (was ₹' . number_format((float) $p->old_price, 0) . ' — ' . $off . '% off)';
        }

        $html .= '<br>Availability: <b>' . $this->stockText($p) . '</b>';

        if ($p->material) {
            $html .= '<br>Fabric: ' . e($p->material);
        }
        if ($p->fit) {
            $html .= '<br>Fit: ' . e($p->fit);
        }
        if ($p->rating) {
            $html .= '<br>Rated ⭐' . rtrim(rtrim((string) $p->rating, '0'), '.');
            if ($p->reviews_count) {
                $html .= ' (' . (int) $p->reviews_count . ' reviews)';
            }
        }

        return $html . '<br><br><a href="/product/' . $p->id . '">View &amp; order →</a>';
    }

    protected function stockText(Product $p): string
    {
        $units = (int) ($p->stock_units ?? 0);
        $in = (bool) $p->in_stock || $units > 0;

        if (!$in) {
            return 'Out of stock';
        }
        if ($units > 0 && $units <= 5) {
            return 'Only ' . $units . ' left!';
        }

        return 'In stock';
    }

    protected function detectCategory(string $normText): ?Category
    {
        $categories = Category::query()->where('is_active', true)->get();
        if ($categories->isEmpty()) {
            $categories = Category::query()->get();
        }

        $best = null;
        $bestLen = 0;
        foreach ($categories as $c) {
            foreach ([$c->name, $c->slug] as $label) {
                $v = $this->norm((string) $label);
                if ($v === '') {
                    continue;
                }
                foreach (array_unique([$v, rtrim($v, 's')]) as $variant) {
                    if (strlen($variant) < 3) {
                        continue;
                    }
                    $hit = strlen($variant) >= 4
                        ? str_contains($normText, $variant)
                        : (bool) preg_match('/\b' . preg_quote($variant, '/') . '\b/', $normText);
                    if ($hit && strlen($variant) > $bestLen) {
                        $best = $c;
                        $bestLen = strlen($variant);
                    }
                }
            }
        }

        return $best;
    }

    protected function detectPrice(string $text): array
    {
        $num = fn ($s) => (int) str_replace(',', '', $s);

        if (preg_match('/between\s*([\d,]+)\s*(?:rs\.?|₹|inr)?\s*and\s*([\d,]+)/', $text, $m)) {
            $a = $num($m[1]);
            $b = $num($m[2]);
            if ($a >= 10 && $b >= 10) {
                return [min($a, $b), max($a, $b)];
            }
        }
        if (preg_match('/(under|below|less than|within|budget|up\s*to|upto|maximum|\bmax\b|around)\s*(?:rs\.?|₹|inr)?\s*([\d,]+)/', $text, $m)) {
            $v = $num($m[2]);
            if ($v >= 10 && $v <= 1000000) {
                return [null, $v];
            }
        }
        if (preg_match('/(above|over|more than|minimum|\bmin\b)\s*(?:rs\.?|₹|inr)?\s*([\d,]+)/', $text, $m)) {
            $v = $num($m[2]);
            if ($v >= 10 && $v <= 1000000) {
                return [$v, null];
            }
        }

        return [null, null];
    }

    protected function extractKeywords(string $normText): array
    {
        static $stop = ['i', 'me', 'my', 'we', 'you', 'your', 'yours', 'the', 'a', 'an', 'and', 'or', 'for', 'to', 'of', 'in', 'on', 'at', 'is', 'are', 'was', 'were', 'do', 'does', 'did', 'have', 'has', 'had', 'having', 'show', 'please', 'want', 'need', 'needing', 'looking', 'look', 'search', 'find', 'finding', 'buy', 'buying', 'get', 'getting', 'got', 'give', 'tell', 'about', 'what', 'which', 'that', 'this', 'these', 'those', 'with', 'from', 'any', 'some', 'there', 'like', 'just', 'now', 'here', 'our', 'us', 'how', 'should', 'would', 'could', 'take', 'taking', 'choose', 'pick', 'zyra', 'store', 'shop', 'shopping', 'online', 'piece', 'pieces', 'women', 'womens', 'ladies', 'lady', 'girl', 'girls', 'collection', 'collections', 'item', 'items', 'product', 'products', 'wear', 'wearing', 'rs', 'inr', 'rupees', 'all', 'also', 'but', 'not', 'no', 'yes', 'only', 'more', 'most', 'very', 'really', 'kindly', 'sir', 'madam', 'hello', 'hi', 'hey', 'hai', 'dear', 'thank', 'thanks', 'thx', 'bye', 'nandri', 'vanakkam', 'good', 'morning', 'evening', 'afternoon', 'day', 'night', 'there', 'their', 'them', 'they', 'then', 'than', 'when', 'where', 'while', 'will', 'can', 'may', 'might', 'must', 'shall', 'am', 'an', 'as', 'be', 'by', 'so', 'if', 'it', 'its', 'too', 'up', 'yet', 'per', 'sir', 'under', 'below', 'above', 'over', 'within', 'budget', 'between', 'upto', 'around', 'less', 'than', 'more', 'minimum', 'maximum', 'max', 'min', 'help', 'with', 'order', 'orders', 'available', 'cod']; 

        $words = preg_split('/\s+/', $normText) ?: [];
        $out = [];
        foreach ($words as $w) {
            if (strlen($w) < 3 || is_numeric($w) || in_array($w, $stop, true)) {
                continue;
            }
            $out[] = $w;
        }

        return array_values(array_unique(array_slice($out, 0, 6)));
    }

    protected function norm(string $s): string
    {
        $s = mb_strtolower(str_replace('₹', ' ', $s));
        $s = preg_replace('/[^a-z0-9\s]+/', ' ', $s) ?? '';

        return trim(preg_replace('/\s+/', ' ', $s) ?? '');
    }

    // ------------------------------------------------------------------
    // Orders (live customer data)
    // ------------------------------------------------------------------

    protected function couponReply(): string
    {
        $coupons = Coupon::query()
            ->where('status', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now());
            })
            ->orderByDesc('discount_value')
            ->get();

        if ($coupons->isEmpty()) {
            return 'No coupon codes are active right now — but new offers drop often, so check back soon! <a href="/shop">Shop →</a>';
        }

        $lines = [];
        foreach ($coupons as $c) {
            $value = $c->discount_type === 'percent'
                ? rtrim(rtrim((string) $c->discount_value, '0'), '.') . '% off'
                : '₹' . number_format((float) $c->discount_value, 0) . ' off';
            $cond = ((float) $c->min_order_amount > 0) ? ' (on orders above ₹' . number_format((float) $c->min_order_amount, 0) . ')' : '';
            $cap = ($c->max_discount !== null && (float) $c->max_discount > 0) ? ', up to ₹' . number_format((float) $c->max_discount, 0) : '';
            $lines[] = '• <b>' . e($c->code) . '</b> — ' . $value . $cond . $cap;
        }

        return 'Live offers right now:<br>' . implode('<br>', $lines) . '<br><br>Type the code in the coupon box at checkout. <a href="/shop">Start shopping →</a>';
    }

    protected function orderStatus(): string
    {
        $orders = $this->customerOrders(3);

        if ($orders === null) {
            return
                'To check your order live, please <a href="/login">log in</a> first — then ask me again and I will fetch your latest orders.<br><br>' .
                'Already have an order number (looks like <b>ZYRA-XXXXXX</b>)? Just type it here and I will look it up.<br><br>' .
                'Or browse everything on your <a href="/my-orders">My Orders</a> page.';
        }

        if ($orders->isEmpty()) {
            return 'I could not find any orders on your account yet. <a href="/shop">Start shopping →</a>';
        }

        $lines = [];
        foreach ($orders as $order) {
            $lines[] = '• ' . $this->orderLink($order) . ' — <b>' . e($order->order_status) . '</b> (' . $this->statusHint($order) . ')';
        }

        return 'Here is what I found on your account:<br>' . implode('<br>', $lines) . '<br><br>See everything on your <a href="/my-orders">My Orders</a> page.';
    }

    protected function myOrders(): string
    {
        $orders = $this->customerOrders(5);

        if ($orders === null) {
            return 'Your orders live on the <a href="/my-orders">My Orders</a> page — please <a href="/login">log in</a> to open it.';
        }

        if ($orders->isEmpty()) {
            return 'No orders on your account yet. <a href="/shop">Explore the new collection →</a>';
        }

        $lines = [];
        foreach ($orders as $order) {
            $lines[] = '• ' . $this->orderLink($order) . ' — ₹' . number_format((float) $order->total, 0) . ' — <b>' . e($order->order_status) . '</b>';
        }

        return 'Your recent orders:<br>' . implode('<br>', $lines) . '<br><br><a href="/my-orders">Open My Orders →</a>';
    }

    protected function orderByNumber(string $orderNumber): string
    {
        $query = Order::query()->where('order_number', $orderNumber);
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        }

        $order = $query->first();

        if (!$order) {
            return 'I could not find order <b>' . e($orderNumber) . '</b>' .
                (auth()->check() ? ' on your account.' : '.') .
                ' Please check the number (it looks like <b>ZYRA-XXXXXX</b> on your confirmation message).<br><br>Or open your <a href="/my-orders">My Orders</a> page.';
        }

        $placed = $order->created_at ? $order->created_at->timezone('Asia/Kolkata')->format('d M Y') : '';

        return
            'Order ' . $this->orderLink($order) . ' (₹' . number_format((float) $order->total, 0) . ($placed !== '' ? ', placed ' . e($placed) : '') . ') is currently: <b>' . e($order->order_status) . '</b> — ' . $this->statusHint($order) .
            '<br><br><a href="/my-orders">Open My Orders →</a>';
    }

    /**
     * @return \Illuminate\Support\Collection|null null when the visitor is a guest.
     */
    protected function customerOrders(int $limit)
    {
        if (!auth()->check()) {
            return null;
        }

        return Order::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit($limit)
            ->get();
    }

    protected function orderLink(Order $order): string
    {
        return '<a href="/order-success/' . e($order->order_number) . '">' . e($order->order_number) . '</a>';
    }

    protected function statusHint(Order $order): string
    {
        return match ($order->order_status) {
            'Pending' => 'order placed, we are packing it',
            'Processing' => 'packed, waiting for courier pickup',
            'Shipped' => 'on the way' . ($order->courier_name ? ' via ' . e($order->courier_name) : '') . ($order->awb_code ? ' (AWB ' . e($order->awb_code) . ')' : ''),
            'Delivered' => 'delivered — enjoy!',
            'Cancelled' => 'this order was cancelled',
            default => 'being prepared',
        };
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function matches(string $text, array $patterns): bool
    {
        foreach ($patterns as $p) {
            if (preg_match('/' . $p . '/i', $text)) {
                return true;
            }
        }

        return false;
    }

    protected function json(string $html)
    {
        return response()->json(['success' => true, 'reply' => $html]);
    }
}