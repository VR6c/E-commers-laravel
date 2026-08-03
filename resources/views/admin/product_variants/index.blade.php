@extends('admin.layouts.admin')

@section('content')

{{-- Page header using upgraded vp-* component --}}
<x-admin.page-header
    :title="'Product Variants'"
    icon="bi bi-layers-fill"
    :subtitle="'Manage all product size, colour and attribute variants'"
    :create-route="route('admin.product_variants.create')"
    :create-label="'Add Variant'" />

{{-- Data table card — same pattern as products/index --}}
<x-admin.data-card label="Product Variants Table">
    <div class="table-responsive">
        <table id="product-variants-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'Product' }}</th>
                    <th>{{ 'Variant Name' }}</th>
                    <th>{{ 'Price' }}</th>
                    <th>{{ 'Stock' }}</th>
                    <th>{{ 'SKU' }}</th>
                    <th class="text-end">{{ 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productVariants as $productVariant)
                <tr class="vp-anim-fade-in">
                    <td>
                        <span class="fw-semibold" style="color: var(--vp-text);">
                            {{ $productVariant->product->name ?? 'Unknown Product' }}
                        </span>
                    </td>

                    <td>
                        <span class="vp-status-badge inactive" style="background: var(--vp-primary-bg); color: var(--vp-primary);">
                            {{ $productVariant->name ?? '—' }}
                        </span>
                    </td>

                    <td>
                        <span style="font-weight: 700; color: var(--vp-primary); font-size: .875rem;">
                            ${{ number_format($productVariant->price, 2) }}
                        </span>
                    </td>

                    <td>
                        @if($productVariant->stock <= 0)
                            <span class="vp-status-badge cancelled">
                                {{ $productVariant->stock }} — Out
                            </span>
                        @elseif($productVariant->stock <= 5)
                            <span class="vp-status-badge pending">
                                {{ $productVariant->stock }} — Low
                            </span>
                        @else
                            <span class="vp-status-badge active">
                                {{ $productVariant->stock }}
                            </span>
                        @endif
                    </td>

                    <td>
                        <code style="font-size: .78rem; background: var(--vp-surface-muted); padding: 3px 8px; border-radius: var(--vp-r-xs); border: 1px solid var(--vp-border-sub); color: var(--vp-text-2);">
                            {{ $productVariant->SKU }}
                        </code>
                    </td>

                    <td>
                        <div class="dt-actions">
                            <a href="{{ route('admin.product_variants.edit', $productVariant->id) }}"
                               class="vp-action-btn vp-action-btn--edit"
                               title="Edit variant"
                               aria-label="Edit variant {{ $productVariant->name }}">
                                <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                            </a>

                            <form action="{{ route('admin.product_variants.destroy', $productVariant->id) }}"
                                  method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Delete this variant?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="vp-action-btn vp-action-btn--delete"
                                        title="Delete variant"
                                        aria-label="Delete variant {{ $productVariant->name }}">
                                    <i class="bi bi-trash-fill" aria-hidden="true"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="vp-empty-state">
                            <div class="vp-empty-state__icon"><i class="bi bi-layers"></i></div>
                            <p class="vp-empty-state__text">No product variants found. Click <strong>Add Variant</strong> to create one.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.data-card>

{{-- Laravel pagination --}}
@if($productVariants->hasPages())
<div class="mt-3 d-flex justify-content-end">
    {{ $productVariants->links() }}
</div>
@endif

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    $('#product-variants-table').DataTable({
        paging:    false,
        searching: true,
        ordering:  true,
        info:      false,
        // Custom DOM matching admin-data-card layout
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        language: {
            search:         '',
            searchPlaceholder: 'Search variants…',
            lengthMenu:     'Show _MENU_',
            zeroRecords:    'No matching variants found.',
            info:           'Showing _START_–_END_ of _TOTAL_',
            infoEmpty:      'No variants available',
            infoFiltered:   '(filtered from _MAX_ total)',
        },
    });
});
</script>
@endsection
