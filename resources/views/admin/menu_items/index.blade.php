@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header
    :title="'Menu Items'"
    icon="bi bi-link-45deg"
    :subtitle="'Manage hierarchy and links for menu: ' . ($menu ? ($menu->title ?? '#' . $menu->id) : 'All Menus')"
    :breadcrumbs="['Menus' => route('admin.menus.index'), ($menu->title ?? 'Menu') => ($menu ? route('admin.menus.edit', $menu->id) : '#'), 'Items' => '#']"
    :create-route="$menu ? route('admin.menus.items.create', $menu->id) : route('admin.menus.create')"
    :create-label="'Add Item'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="menu-items-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Slug' }}</th>
                    <th>{{ 'Order' }}</th>
                    <th class="text-end">{{ 'Actions' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteMenuItemModal" confirm-id="confirmDeleteMenuItem"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this menu item?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#menu-items-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.menus.item.getData') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}" }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'slug', name: 'slug' },
            { data: 'order_number', name: 'order_number' },
            {
                data: 'action', orderable: false, searchable: false,
                render: function(data, type, row) {
                    return `<div class="dt-actions">
                        <a href="/admin/items/${row.id}/edit" class="btn-action btn-action-edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                        <button type="button" class="btn-action btn-action-delete" onclick="deleteMenuItem(${row.id})" title="Delete"><i class="bi bi-trash-fill"></i></button>
                    </div>`;
                }
            }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });
});

let menuItemToDeleteId = null;

function deleteMenuItem(id) {
    menuItemToDeleteId = id;
    $('#deleteMenuItemModal').modal('show');
    $('#confirmDeleteMenuItem').off('click').on('click', function() {
        if (menuItemToDeleteId !== null) {
            $.ajax({
                url: '{{ route('admin.items.destroy', ':id') }}'.replace(':id', menuItemToDeleteId),
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        $('#menu-items-table').DataTable().ajax.reload();
                        showToast('success', response.message);
                        $('#deleteMenuItemModal').modal('hide');
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', "Error deleting menu item! Please try again.");
                    $('#deleteMenuItemModal').modal('hide');
                }
            });
        }
    });
}
</script>
@endsection
