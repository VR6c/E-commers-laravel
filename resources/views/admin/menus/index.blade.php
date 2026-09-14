@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header
    :title="'All Menus'"
    icon="bi bi-list-nested"
    :subtitle="'Configure navigation menus, menu items, and location placements'"
    :breadcrumbs="['Menus' => '#']"
    :create-route="route('admin.menus.create')"
    :create-label="'Add Menu'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="menus-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Title' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteMenuModal" confirm-id="confirmDeleteMenu"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this menu?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#menus-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.menus.data') }}",
            type: 'POST',
            data: function(d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'title', name: 'title' },
            {
                data: 'action', orderable: false, searchable: false,
                render: function(data, type, row) {
                    return `<div class="dt-actions">
                        <a href="/admin/menus/${row.id}/edit" class="btn-action btn-action-edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                        <button type="button" class="btn-action btn-action-delete" onclick="deleteMenu(${row.id})" title="Delete"><i class="bi bi-trash-fill"></i></button>
                    </div>`;
                }
            }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });
});

let menuToDeleteId = null;

function deleteMenu(id) {
    menuToDeleteId = id;
    $('#deleteMenuModal').modal('show');
    $('#confirmDeleteMenu').off('click').on('click', function() {
        if (menuToDeleteId !== null) {
            $.ajax({
                url: '{{ route('admin.menus.destroy', ':id') }}'.replace(':id', menuToDeleteId),
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        $('#menus-table').DataTable().ajax.reload();
                        showToast('success', response.message);
                        $('#deleteMenuModal').modal('hide');
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', "Error deleting menu! Please try again.");
                    $('#deleteMenuModal').modal('hide');
                }
            });
        }
    });
}
</script>
@endsection
