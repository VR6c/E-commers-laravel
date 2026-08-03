@extends('admin.layouts.admin')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css"
    rel="stylesheet">
@endsection

@section('content')

<x-admin.page-header :title="'Manage Products'"
    :create-route="route('admin.products.create')"
    :create-label="'Add New'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="products-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Image' }}</th>
                    <th>{{ 'Name' }}</th>
                    <th>{{ 'Category' }}</th>
                    <th>{{ 'Price' }}</th>
                    <th>{{ 'Stock' }}</th>
                    <th>{{ 'Status' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

@push('modals')
<x-admin.delete-modal id="deleteProductModal" confirm-id="confirmDeleteProduct"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this product?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />
@endpush

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php
$datatableLang = null;
@endphp

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#products-table').DataTable({
            processing: true,
            serverSide: true,
            // Custom DOM: toolbar row on top, table in middle, footer row on bottom
            dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>' +
                 'rt' +
                 '<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
            ajax: {
                url: "{{ route('admin.products.data') }}",
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                {
                    data: 'image',
                    name: 'image',
                    orderable: false,
                    searchable: false,
                },
                { data: 'name', name: 'name' },
                {
                    data: 'category',
                    name: 'category',
                    orderable: false,
                    searchable: false
                },
                { data: 'price', name: 'price' },
                {
                    data: 'stock',
                    name: 'stock',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var isChecked = data ? 'checked' : '';
                        return `<label class="switch">
                                    <input type="checkbox" class="toggle-status" data-id="${row.id}" ${isChecked} aria-label="Toggle product status">
                                    <span class="slider round"></span>
                                </label>`;
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<div class="dt-actions">
                                    <a href="/admin/products/${row.id}/edit" class="btn-action btn-action-edit" title="Edit" aria-label="Edit product ${row.id}">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <button type="button" class="btn-action btn-action-delete" onclick="deleteProduct(${row.id})" title="Delete" aria-label="Delete product ${row.id}">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>`;
                    }
                }
            ],
            pageLength: 10,
            language: @json($datatableLang)
        });

        $(document).on('change', '.toggle-status', function() {
            var productId = $(this).data('id');
            var newStatus = $(this).prop('checked') ? 1 : 0;
            updateProductStatus(productId, newStatus);
        });

    });

    let productToDeleteId = null;

    function deleteProduct(id) {
        productToDeleteId = id;
        var modalEl = document.getElementById('deleteProductModal');
        var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();

        $('#confirmDeleteProduct').off('click').on('click', function() {
            if (productToDeleteId !== null) {
                $.ajax({
                    url: '{{ route('admin.products.destroy', ':id') }}'.replace(':id', productToDeleteId),
                    method: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#products-table').DataTable().ajax.reload();
                            showToast('success', response.message);
                            modalInstance.hide();
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', 'Error deleting product!');
                        modalInstance.hide();
                    }
                });
            }
        });
    }

    function updateProductStatus(id, status) {
        $.ajax({
            url: '{{ route('admin.products.updateStatus') }}',
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    $('#products-table').DataTable().ajax.reload();
                    showToast('success', response.message);
                } else {
                    showToast('error', response.message);
                }
            },
            error: function(xhr) {
                showToast('error', "Error updating product status! Please try again.");
            }
        });
    }

</script>

@endsection