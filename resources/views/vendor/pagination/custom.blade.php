@if ($paginator->hasPages())
<nav aria-label="Page navigation" class="custom-pagination-nav">
    <ul class="custom-pagination">

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item-custom disabled">
                <span class="page-link-custom" aria-disabled="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </span>
            </li>
        @else
            <li class="page-item-custom">
                <a class="page-link-custom" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="page-item-custom disabled"><span class="page-link-custom dots">{{ $element }}</span></li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item-custom active" aria-current="page">
                            <span class="page-link-custom">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item-custom">
                            <a class="page-link-custom" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item-custom">
                <a class="page-link-custom" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </a>
            </li>
        @else
            <li class="page-item-custom disabled">
                <span class="page-link-custom" aria-disabled="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </span>
            </li>
        @endif

    </ul>
</nav>

<style>
.custom-pagination-nav {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.custom-pagination {
    display: flex;
    align-items: center;
    gap: 6px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.page-link-custom {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 10px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    color: #555;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    transition: all 0.2s ease;
    cursor: pointer;
    user-select: none;
}

.page-item-custom a.page-link-custom:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #222;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.page-item-custom.active .page-link-custom {
    background: #333;
    border-color: #333;
    color: #fff;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0,0,0,0.18);
}

.page-item-custom.disabled .page-link-custom {
    color: #ccc;
    border-color: #eee;
    cursor: not-allowed;
    background: #fafafa;
}

.page-link-custom.dots {
    border: none;
    background: transparent;
    color: #aaa;
    letter-spacing: 1px;
}
</style>
@endif
