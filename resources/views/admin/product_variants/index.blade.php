@extends('admin.layouts.admin')

@section('content')

{{-- Page header using upgraded vp-* component --}}
<x-admin.page-header
    :title="'Product Variants'"
    icon="bi bi-layers-fill"
    :subtitle="'Manage all product size, colour and attribute variants'"
    :breadcrumbs="['Product Variants' => '#']"
    :create-route="route('admin.product_variants.create')"
    :create-label="'Add Variant'" />

{{-- Data table card — 100% consistent with all admin CRUD tables --}}
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
        </table>
    </div>
</x-admin.data-card>

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    $('#product-variants-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.product_variants.data') }}",
            type: 'POST',
            data: function(d) {
                d._token = "{{ csrf_token() }}";
            }
        },
        columns: [
            { data: 'product', name: 'product.name' },
            { data: 'variant_name', name: 'name' },
            { data: 'price', name: 'price' },
            { data: 'stock', name: 'stock' },
            { data: 'sku', name: 'SKU' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
        ],
        pageLength: 10,
        language: {
            search: '',
            searchPlaceholder: 'Search variants…',
            lengthMenu: 'Show _MENU_',
            zeroRecords: 'No matching variants found.',
            info: 'Showing _START_–_END_ of _TOTAL_ entries',
            infoEmpty: 'No variants available',
            infoFiltered: '(filtered from _MAX_ total)',
        }
    });
});

function deleteVariant(id) {
    if (!confirm('Are you sure you want to delete this variant?')) {
        return;
    }
    $.ajax({
        url: "{{ url('admin/product_variants') }}/" + id,
        type: 'DELETE',
        data: {
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            $('#product-variants-table').DataTable().ajax.reload();
        },
        error: function(xhr) {
            alert('Failed to delete variant.');
        }
    });
}
</script>
@endsection
