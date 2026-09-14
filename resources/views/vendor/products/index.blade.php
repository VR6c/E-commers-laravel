@extends('vendor.layouts.master')

@section('title', 'Products')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
@endsection

@section('content')

<x-admin.page-header
    :title="'Manage Products'"
    icon="bi bi-box-seam"
    :subtitle="'Manage your product catalog, pricing, and stock variants'"
    :breadcrumbs="['Products' => '#']"
    :create-route="route('vendor.products.create')"
    :create-label="'Add Product'" />

<x-admin.data-card label="Vendor Products Table">
    <div class="table-responsive">
        <table id="products-table" class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal
    id="deleteProductModal"
    confirm-id="confirmDeleteProduct"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this product? All variants and images will be permanently removed.'"
    :confirm-label="'Delete Product'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    var table = $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('vendor.products.data') }}",
            type: 'POST',
            data: function (d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            {
                data: 'id', name: 'id',
                render: d => `<span class="fw-bold text-body">#${d}</span>`
            },
            {
                data: 'name', name: 'name',
                render: d => `<span class="fw-semibold text-dark">${d}</span>`
            },
            {
                data: 'price', name: 'price',
                render: d => `<span class="fw-semibold text-primary">${d}</span>`
            },
            {
                data: 'status', name: 'status',
                orderable: false, searchable: false,
                render: (data, type, row) =>
                    `<label class="switch">
                        <input type="checkbox" class="toggle-status" data-id="${row.id}" ${data ? 'checked' : ''}>
                        <span class="slider round"></span>
                    </label>`
            },
            {
                data: 'action', name: 'action',
                orderable: false, searchable: false,
                className: 'text-end',
                render: (data, type, row) => data ||
                    `<div class="dt-actions">
                        <a href="/vendor/products/${row.id}/edit"
                           class="btn-action btn-action-edit" title="Edit">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <button type="button"
                                class="btn-action btn-action-delete"
                                onclick="deleteProduct(${row.id})" title="Delete">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>`
            }
        ],
        pageLength: 10,
        language: {
            search: '',
            searchPlaceholder: 'Search products…',
            lengthMenu: 'Show _MENU_',
            zeroRecords: 'No matching products found.',
            info: 'Showing _START_–_END_ of _TOTAL_ entries',
            infoEmpty: 'No products available',
            infoFiltered: '(filtered from _MAX_ total)',
        }
    });

    $(document).on('change', '.toggle-status', function () {
        updateProductStatus($(this).data('id'), $(this).prop('checked') ? 1 : 0);
    });
});

let productToDeleteId = null;

function deleteProduct(id) {
    productToDeleteId = id;
    $('#deleteProductModal').modal('show');
    $('#confirmDeleteProduct').off('click').on('click', function () {
        if (productToDeleteId !== null) {
            $.ajax({
                url: '{{ route('vendor.products.destroy', ':id') }}'.replace(':id', productToDeleteId),
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function (response) {
                    $('#deleteProductModal').modal('hide');
                    $('#products-table').DataTable().ajax.reload();
                    showToast('success', response.message ?? 'Product deleted successfully.');
                },
                error: function () {
                    $('#deleteProductModal').modal('hide');
                    showToast('error', 'Error deleting product.');
                }
            });
        }
    });
}

function updateProductStatus(id, status) {
    $.ajax({
        url: '{{ route('vendor.products.updateStatus') }}',
        method: 'POST',
        data: { _token: "{{ csrf_token() }}", id: id, status: status },
        success: function (response) { showToast('success', response.message ?? 'Status updated.'); },
        error: function () { showToast('error', 'Failed to update status.'); }
    });
}
</script>
@endsection
