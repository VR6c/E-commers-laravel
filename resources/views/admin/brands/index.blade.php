@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header :title="'Brands'"
    :create-route="route('admin.brands.create')"
    :create-label="'Add New'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="brands-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Name' }}</th>
                    <th>{{ 'Logo' }}</th>
                    <th>{{ 'Status' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteBrandModal" confirm-id="confirmDeleteBrand"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this brand?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#brands-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.brands.getData') }}",
            type: 'GET',
            data: function(d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            {
                data: 'logo_url', name: 'logo_url', orderable: false, searchable: false,
                render: function(data) {
                    if (data) {
                        var src = data.startsWith('http') ? data : '/storage/' + data;
                        return `<img src="${src}" alt="Logo" class="dt-product-thumb">`;
                    }
                    return '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'status', name: 'status',
                render: function(data, type, row) {
                    var isChecked = data ? 'checked' : '';
                    return `<label class="switch">
                        <input type="checkbox" class="toggle-status" data-id="${row.id}" ${isChecked} aria-label="Toggle brand status">
                        <span class="slider round"></span>
                    </label>`;
                }
            },
            {
                data: 'action', orderable: false, searchable: false,
                render: function(data, type, row) {
                    return `<div class="dt-actions">
                        <a href="/admin/brands/${row.id}/edit" class="btn-action btn-action-edit" title="Edit" aria-label="Edit brand ${row.id}"><i class="bi bi-pencil-fill"></i></a>
                        <button type="button" class="btn-action btn-action-delete" onclick="deleteBrand(${row.id})" title="Delete" aria-label="Delete brand ${row.id}"><i class="bi bi-trash-fill"></i></button>
                    </div>`;
                }
            }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });

    $(document).on('change', '.toggle-status', function() {
        var brandId = $(this).data('id');
        var isActive = $(this).prop('checked') ? 1 : 0;
        $.ajax({
            url: '{{ route('admin.brands.updateStatus') }}',
            method: 'POST',
            data: { _token: "{{ csrf_token() }}", id: brandId, status: isActive },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                } else {
                    showToast('error', response.message);
                }
            }
        });
    });
});

let brandToDeleteId = null;

function deleteBrand(id) {
    brandToDeleteId = id;
    $('#deleteBrandModal').modal('show');
    $('#confirmDeleteBrand').off('click').on('click', function() {
        if (brandToDeleteId !== null) {
            $.ajax({
                url: '{{ route('admin.brands.destroy', ':id') }}'.replace(':id', brandToDeleteId),
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        $('#brands-table').DataTable().ajax.reload();
                        showToast('success', response.message);
                        $('#deleteBrandModal').modal('hide');
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', 'Error deleting brand!');
                    $('#deleteBrandModal').modal('hide');
                }
            });
        }
    });
}
</script>
@endsection
