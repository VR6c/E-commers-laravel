@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header :title="'Categories'"
    :create-route="route('admin.categories.create')"
    :create-label="'Add New'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="categories-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Name' }}</th>
                    <th>{{ 'Status' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteCategoryModal" confirm-id="confirmDeleteCategory"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this category?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#categories-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.categories.data') }}",
            type: 'POST',
            data: function(d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            {
                data: 'status', name: 'status',
                render: function(data, type, row) {
                    var isChecked = data ? 'checked' : '';
                    return `<label class="switch">
                        <input type="checkbox" class="toggle-status" data-id="${row.id}" ${isChecked} aria-label="Toggle category status">
                        <span class="slider round"></span>
                    </label>`;
                }
            },
            {
                data: 'action', orderable: false, searchable: false,
                render: function(data, type, row) {
                    return `<div class="dt-actions">
                        <a href="/admin/categories/${row.id}/edit" class="btn-action btn-action-edit" title="Edit" aria-label="Edit category ${row.id}"><i class="bi bi-pencil-fill"></i></a>
                        <button type="button" class="btn-action btn-action-delete" onclick="deleteCategory(${row.id})" title="Delete" aria-label="Delete category ${row.id}"><i class="bi bi-trash-fill"></i></button>
                    </div>`;
                }
            }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });

    $(document).on('change', '.toggle-status', function() {
        var categoryId = $(this).data('id');
        var isActive = $(this).prop('checked') ? 1 : 0;
        $.ajax({
            url: '{{ route('admin.categories.updateStatus') }}',
            method: 'POST',
            data: { _token: "{{ csrf_token() }}", id: categoryId, status: isActive },
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

let categoryToDeleteId = null;

function deleteCategory(id) {
    categoryToDeleteId = id;
    $('#deleteCategoryModal').modal('show');
    $('#confirmDeleteCategory').off('click').on('click', function() {
        if (categoryToDeleteId !== null) {
            $.ajax({
                url: '{{ route('admin.categories.destroy', ':id') }}'.replace(':id', categoryToDeleteId),
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        $('#categories-table').DataTable().ajax.reload();
                        showToast('success', response.message);
                        $('#deleteCategoryModal').modal('hide');
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', 'Error deleting category!');
                    $('#deleteCategoryModal').modal('hide');
                }
            });
        }
    });
}
</script>
@endsection
