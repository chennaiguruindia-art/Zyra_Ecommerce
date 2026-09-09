@props(['items' => [], 'showHeading' => false, 'showDelete' => false])

@once
@push('styles')
<style>
    .ig-rail-wrap { position: relative; }
    .ig-rail, .ig-thumb-rail {
        display: flex; gap: 18px; overflow-x: auto; scroll-behavior: smooth;
        padding: 20px 4px; scrollbar-width: thin;
    }
    .ig-slot {
        flex: 0 0 auto; width: 210px; height: 280px; overflow: hidden;
        background: #fff; border-radius: 12px; cursor: pointer; position: relative;
        box-shadow: 0 2px 10px rgba(0,0,0,.06); border: 1px solid #eee;
    }
    .ig-slot .instagram-media {
        transform: scale(.42); transform-origin: top left;
        width: 500px !important; margin: 0 !important;
    }
    .ig-arrow {
        position: absolute; top: 50%; transform: translateY(-50%); z-index: 5;
        width: 38px; height: 38px; border-radius: 50%; border: 1px solid #ddd;
        background: #fff; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,.12); cursor: pointer;
    }
    .ig-arrow.prev { left: -6px; }
    .ig-arrow.next { right: -6px; }
    .ig-remove {
        position: absolute; top: 6px; right: 6px; z-index: 6;
        width: 26px; height: 26px; border-radius: 50%; border: 0;
        background: rgba(0,0,0,.55); color: #fff; line-height: 1; font-size: 14px;
    }
    .ig-lightbox {
        position: fixed; inset: 0; z-index: 2000; display: none;
        background: rgba(0,0,0,.92); align-items: center; justify-content: center;
        flex-direction: column; padding: 20px;
    }
    .ig-lightbox.open { display: flex; }
    .ig-stage {
        position: relative; max-width: min(560px, 94vw); max-height: 62vh;
        overflow: auto; background: #fff; border-radius: 12px;
    }
    .ig-stage .instagram-media {
        width: 500px !important; margin: 0 !important; max-width: 100%;
    }
    .ig-close {
        position: absolute; top: 12px; right: 16px; z-index: 10;
        background: none; border: 0; color: #fff; font-size: 32px; cursor: pointer;
    }
    .ig-nav {
        position: absolute; top: 50%; transform: translateY(-50%); z-index: 10;
        width: 46px; height: 46px; border-radius: 50%; border: 0; background: #fff;
        font-size: 22px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.3);
    }
    .ig-nav.prev { left: 14px; }
    .ig-nav.next { right: 14px; }
    .ig-thumb-rail { max-width: 92vw; margin-top: 16px; }
    .ig-thumb {
        flex: 0 0 auto; width: 130px; height: 170px; overflow: hidden;
        border-radius: 8px; cursor: pointer; border: 2px solid transparent;
        background: #fff; opacity: .75;
    }
    .ig-thumb.active { border-color: #fff; opacity: 1; }
    .ig-thumb .instagram-media {
        transform: scale(.26); transform-origin: top left;
        width: 500px !important; margin: 0 !important;
    }
</style>
@endpush
@endonce

@if ($showHeading)
    <div class="text-center mb-4">
        <span class="text-uppercase small fw-bold text-muted">Tag Us @zyralifestyle46</span>
        <h2 class="display-6 fw-bold mt-1">Fashion Gallery</h2>
        <p class="text-muted small">Share your style moments using #ZyraWoman</p>
    </div>
@endif

@if (count($items) === 0)
    <div class="text-center py-5 text-muted border rounded">
        <i class="bi bi-instagram fs-1 d-block mb-2"></i>
        Gallery is empty. Paste an Instagram link at the Fashion Gallery page to start the gallery.
    </div>
@else
    <div class="ig-rail-wrap" id="igPageRail">
        <button type="button" class="ig-arrow prev" aria-label="Scroll left">&#10094;</button>
        <div class="ig-rail" id="igRail">
            @foreach ($items as $item)
                <div class="ig-slot" data-slot="{{ $loop->index }}">
                    @if ($showDelete)
                        <form method="POST" action="{{ route('instagram.destroy', ['id' => $item['id']]) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ig-remove" title="Remove link" onclick="event.stopPropagation();">&times;</button>
                        </form>
                    @endif
                    <blockquote class="instagram-media" data-instgrm-permalink="{{ $item['url'] }}" data-instgrm-version="14"></blockquote>
                </div>
            @endforeach
        </div>
        <button type="button" class="ig-arrow next" aria-label="Scroll right">&#10095;</button>
    </div>

    <div class="ig-lightbox" id="igLightbox">
        <button type="button" class="ig-close" id="igClose">&times;</button>
        <button type="button" class="ig-nav prev" id="igPrev">&#10094;</button>
        <button type="button" class="ig-nav next" id="igNext">&#10095;</button>
        <div class="ig-stage" id="igStage"></div>
        <div class="ig-thumb-rail" id="igThumbs"></div>
    </div>

    @once
    <script async src="https://www.instagram.com/embed.js"></script>
    @push('scripts')
    <script>
        (function () {
            const rail = document.getElementById('igRail');
            const lightbox = document.getElementById('igLightbox');
            const stage = document.getElementById('igStage');
            const thumbs = document.getElementById('igThumbs');

            if (!rail) return;

            const items = Array.from(document.querySelectorAll('.ig-slot')).map(slot => ({
                slot: slot,
                blockquote: slot.querySelector('blockquote'),
            }));
            let current = 0;

            function scrollRail(by) {
                rail.scrollBy({ left: by, behavior: 'smooth' });
            }
            document.querySelectorAll('#igPageRail .ig-arrow.prev').forEach(b => b.addEventListener('click', () => scrollRail(-380)));
            document.querySelectorAll('#igPageRail .ig-arrow.next').forEach(b => b.addEventListener('click', () => scrollRail(380)));

            items.forEach((item, i) => {
                item.slot.addEventListener('click', (e) => {
                    if (e.target.closest('form')) return;
                    open(i);
                });
            });

            function buildThumbs() {
                thumbs.innerHTML = '';
                items.forEach((item, i) => {
                    const t = document.createElement('div');
                    t.className = 'ig-thumb' + (i === current ? ' active' : '');
                    t.dataset.idx = i;
                    t.appendChild(item.blockquote);
                    t.addEventListener('click', () => open(i));
                    thumbs.appendChild(t);
                });
            }

            function showActive() {
                const t = thumbs.querySelector('.ig-thumb.active');
                if (t) t.classList.remove('active');
                const nt = thumbs.querySelector('.ig-thumb[data-idx="' + current + '"]');
                if (nt) nt.classList.add('active');
                requestAnimationFrame(() => { if (nt) nt.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' }); });
                stage.innerHTML = '';
                stage.appendChild(items[current].blockquote);
            }

            function open(i) {
                if (thumbs.children.length === 0) buildThumbs();
                current = i;
                showActive();
                lightbox.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function closeLb() {
                lightbox.classList.remove('open');
                document.body.style.overflow = '';
                thumbs.querySelectorAll('.ig-thumb').forEach(t => t.appendChild(t.querySelector('blockquote')));
                items.forEach(item => item.slot.appendChild(item.blockquote));
            }

            function step(dir) {
                current = (current + dir + items.length) % items.length;
                showActive();
            }

            document.getElementById('igClose').addEventListener('click', closeLb);
            document.getElementById('igPrev').addEventListener('click', () => step(-1));
            document.getElementById('igNext').addEventListener('click', () => step(1));
            lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLb(); });
            document.addEventListener('keydown', (e) => {
                if (!lightbox.classList.contains('open')) return;
                if (e.key === 'Escape') closeLb();
                if (e.key === 'ArrowLeft') step(-1);
                if (e.key === 'ArrowRight') step(1);
            });
        })();
    </script>
    @endpush
    @endonce
@endif