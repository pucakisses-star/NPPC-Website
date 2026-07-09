{{-- Slide-out cart drawer. Include once on any page with an add-to-cart form:
     the drawer opens (instead of navigating to /cart) when a form with
     action="/cart/add" is submitted, shows the live cart with quantity and
     remove controls, and a "You just added …" note appears under the buy
     button (in #cart-added-msg if present, else injected after the form). --}}
<style>
    /* Above the site header/nav layers (which run up to z-index 999999). */
    .cdw-backdrop { position: fixed; inset: 0; background: rgba(10,10,14,0.45); opacity: 0; pointer-events: none; transition: opacity 0.3s ease; z-index: 1000000; }
    .cdw-backdrop.open { opacity: 1; pointer-events: auto; }
    .cdw { position: fixed; top: 0; right: 0; bottom: 0; width: min(400px, 92vw); background: var(--store-surface, #fff); color: var(--store-fg, #17161d); box-shadow: -18px 0 50px rgba(0,0,0,0.22); transform: translateX(105%); transition: transform 0.32s cubic-bezier(0.22, 0.7, 0.3, 1); z-index: 1000001; display: flex; flex-direction: column; }
    .cdw.open { transform: translateX(0); }
    .cdw-head { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.08); }
    .cdw-title { font-size: 18px; font-weight: 800; }
    .cdw-title span { font-weight: 400; opacity: 0.55; }
    .cdw-close { background: none; border: 0; font-size: 26px; line-height: 1; cursor: pointer; color: inherit; opacity: 0.6; padding: 2px 6px; }
    .cdw-close:hover { opacity: 1; }
    .cdw-items { flex: 1; overflow-y: auto; padding: 8px 24px; }
    .cdw-empty { padding: 40px 0; text-align: center; opacity: 0.55; font-size: 14px; }
    .cdw-row { display: flex; gap: 14px; padding: 16px 0; border-bottom: 1px solid rgba(0,0,0,0.06); }
    .cdw-thumb { width: 64px; height: 64px; flex: 0 0 64px; border-radius: 6px; background: rgba(0,0,0,0.05); overflow: hidden; }
    .cdw-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .cdw-info { flex: 1; min-width: 0; }
    .cdw-name { font-size: 14px; font-weight: 700; text-decoration: none; color: inherit; display: block; }
    .cdw-meta { font-size: 12px; opacity: 0.55; margin-top: 2px; }
    .cdw-controls { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
    .cdw-qty { display: inline-flex; align-items: center; border: 1px solid rgba(0,0,0,0.18); border-radius: 6px; }
    .cdw-qty button { background: none; border: 0; width: 26px; height: 26px; font-size: 15px; cursor: pointer; color: inherit; opacity: 0.65; }
    .cdw-qty button:hover { opacity: 1; }
    .cdw-qty b { font-size: 13px; font-weight: 600; min-width: 22px; text-align: center; }
    .cdw-remove { background: none; border: 0; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; color: inherit; opacity: 0.45; }
    .cdw-remove:hover { opacity: 0.9; }
    .cdw-price { font-size: 14px; font-weight: 700; white-space: nowrap; }
    .cdw-foot { border-top: 1px solid rgba(0,0,0,0.1); padding: 18px 24px 20px; }
    .cdw-subtotal { display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; }
    .cdw-taxnote { font-size: 12px; opacity: 0.5; margin: 6px 0 14px; }
    .cdw-checkout { display: block; width: 100%; background: var(--store-accent, #17161d); color: var(--store-on-accent, #fff); border: 0; border-radius: 8px; padding: 15px 0; font-size: 15px; font-weight: 700; cursor: pointer; }
    .cdw-checkout:hover { opacity: 0.92; }
    .cdw-viewcart { display: block; text-align: center; font-size: 13px; margin-top: 12px; color: inherit; opacity: 0.6; }
    .cdw-viewcart:hover { opacity: 1; }
    .cdw-added-msg { font-size: 14px; margin-top: 14px; color: var(--store-accent-2, #2f7a4d); font-weight: 600; opacity: 0; transition: opacity 0.3s; }
    .cdw-added-msg.show { opacity: 1; }
</style>

<div class="cdw-backdrop" id="cdw-backdrop"></div>
<aside class="cdw" id="cdw" aria-label="Shopping cart" aria-hidden="true">
    <div class="cdw-head">
        <div class="cdw-title">Your Cart <span id="cdw-count">(0)</span></div>
        <button class="cdw-close" id="cdw-close" aria-label="Close cart">&times;</button>
    </div>
    <div class="cdw-items" id="cdw-items"></div>
    <div class="cdw-foot">
        <div class="cdw-subtotal"><span>Subtotal</span><span id="cdw-subtotal">$0.00</span></div>
        <div class="cdw-taxnote">Shipping &amp; taxes calculated at checkout.</div>
        <form method="POST" action="/cart/checkout">
            @csrf
            <button type="submit" class="cdw-checkout" id="cdw-checkout">Checkout</button>
        </form>
        <a class="cdw-viewcart" href="/cart">View full cart</a>
    </div>
</aside>

<script>
(function () {
    var drawer = document.getElementById('cdw');
    var backdrop = document.getElementById('cdw-backdrop');
    var itemsEl = document.getElementById('cdw-items');
    var countEl = document.getElementById('cdw-count');
    var subtotalEl = document.getElementById('cdw-subtotal');
    var csrf = (document.querySelector('input[name="_token"]') || {}).value;
    if (!drawer || !csrf) return;

    function money(n) { return '$' + Number(n).toFixed(2); }

    function open() { drawer.classList.add('open'); backdrop.classList.add('open'); drawer.setAttribute('aria-hidden', 'false'); }
    function close() { drawer.classList.remove('open'); backdrop.classList.remove('open'); drawer.setAttribute('aria-hidden', 'true'); }
    document.getElementById('cdw-close').addEventListener('click', close);
    backdrop.addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

    function post(url, fields) {
        var body = new URLSearchParams(fields);
        body.set('_token', csrf);
        return fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        }).then(function (r) { if (!r.ok) throw new Error('cart request failed'); return r.json(); });
    }

    function render(cart) {
        countEl.textContent = '(' + cart.count + ')';
        subtotalEl.textContent = money(cart.subtotal);
        itemsEl.innerHTML = '';
        if (!cart.items.length) {
            var empty = document.createElement('div');
            empty.className = 'cdw-empty';
            empty.textContent = 'Your cart is empty.';
            itemsEl.appendChild(empty);
            return;
        }
        cart.items.forEach(function (it) {
            var row = document.createElement('div'); row.className = 'cdw-row';
            var thumb = document.createElement('a'); thumb.className = 'cdw-thumb'; thumb.href = '/store/' + it.slug;
            if (it.image_url) { var img = document.createElement('img'); img.src = it.image_url; img.alt = it.name; thumb.appendChild(img); }
            var info = document.createElement('div'); info.className = 'cdw-info';
            var name = document.createElement('a'); name.className = 'cdw-name'; name.href = '/store/' + it.slug; name.textContent = it.name;
            var meta = document.createElement('div'); meta.className = 'cdw-meta';
            meta.textContent = (it.size ? 'Size: ' + it.size + ' · ' : '') + money(it.price) + ' each';
            var controls = document.createElement('div'); controls.className = 'cdw-controls';
            var qty = document.createElement('span'); qty.className = 'cdw-qty';
            var minus = document.createElement('button'); minus.type = 'button'; minus.textContent = '−'; minus.setAttribute('aria-label', 'Decrease quantity');
            var num = document.createElement('b'); num.textContent = it.quantity;
            var plus = document.createElement('button'); plus.type = 'button'; plus.textContent = '+'; plus.setAttribute('aria-label', 'Increase quantity');
            minus.addEventListener('click', function () { post('/cart/update', { key: it.key, quantity: it.quantity - 1 }).then(render).catch(function () { window.location.href = '/cart'; }); });
            plus.addEventListener('click', function () { post('/cart/update', { key: it.key, quantity: it.quantity + 1 }).then(render).catch(function () { window.location.href = '/cart'; }); });
            qty.appendChild(minus); qty.appendChild(num); qty.appendChild(plus);
            var remove = document.createElement('button'); remove.type = 'button'; remove.className = 'cdw-remove'; remove.textContent = 'Remove';
            remove.addEventListener('click', function () { post('/cart/remove', { key: it.key }).then(render).catch(function () { window.location.href = '/cart'; }); });
            controls.appendChild(qty); controls.appendChild(remove);
            info.appendChild(name); info.appendChild(meta); info.appendChild(controls);
            var price = document.createElement('div'); price.className = 'cdw-price'; price.textContent = money(it.line_total);
            row.appendChild(thumb); row.appendChild(info); row.appendChild(price);
            itemsEl.appendChild(row);
        });
    }

    // "You just added 1 XL NPPC Shirt to your cart."
    function addedText(added) {
        return 'You just added ' + added.quantity + ' ' + (added.size ? added.size + ' ' : '') + added.name
            + (added.quantity > 1 ? 's' : '') + ' to your cart.';
    }

    document.querySelectorAll('form[action="/cart/add"]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var fields = {};
            new FormData(form).forEach(function (v, k) { fields[k] = v; });
            post('/cart/add', fields).then(function (cart) {
                render(cart);
                open();
                var msg = document.getElementById('cart-added-msg');
                if (!msg) {
                    msg = document.createElement('div');
                    msg.id = 'cart-added-msg';
                    msg.className = 'cdw-added-msg';
                    form.appendChild(msg);
                }
                msg.classList.add('cdw-added-msg');
                if (cart.added) {
                    msg.textContent = addedText(cart.added);
                    msg.classList.add('show');
                    window.clearTimeout(msg._t);
                    msg._t = window.setTimeout(function () { msg.classList.remove('show'); }, 6000);
                }
            }).catch(function () { form.submit(); });   // fall back to the classic redirect
        });
    });
})();
</script>
