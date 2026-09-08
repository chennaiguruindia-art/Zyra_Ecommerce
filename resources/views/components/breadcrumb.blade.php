@props(['items' => []])

<nav aria-label="breadcrumb" class="zyra-breadcrumb-nav">
    <div class="container">
        <ol class="breadcrumb zyra-breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}" class="text-decoration-none text-muted">
                    <i class="bi bi-house-door me-1"></i> Home
                </a>
            </li>
            @foreach($items as $item)
                @if(!$loop->last && !empty($item['url']))
                    <li class="breadcrumb-item">
                        <a href="{{ $item['url'] }}" class="text-decoration-none text-muted">{{ $item['label'] }}</a>
                    </li>
                @else
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">
                        {{ $item['label'] }}
                    </li>
                @endif
            @endforeach
        </ol>
    </div>
</nav>
