@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header
    :title="'Manage Attributes'"
    icon="bi bi-sliders"
    :subtitle="'Configure product variant attributes like size, color, and material'"
    :breadcrumbs="['Attributes' => '#']"
    :create-route="route('admin.attributes.create')"
    :create-label="'Add Attribute'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="attributes-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Name' }}</th>
                    <th>{{ 'Values' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteAttributeModal" confirm-id="confirmDeleteAttribute"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this attribute?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#attributes-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.attributes.data') }}",
            type: 'POST',
            data: function(d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'values', name: 'values', orderable: false, searchable: false },
            {
                data: 'action', orderable: false, searchable: false,
                render: function(data, type, row) {
                    return `<div class="dt-actions">
                        <a href="/admin/attributes/${row.id}/edit" class="btn-action btn-action-edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                        <button type="button" class="btn-action btn-action-delete" onclick="deleteAttribute(${row.id})" title="Delete"><i class="bi bi-trash-fill"></i></button>
                    </div>`;
                }
            }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });
});

let attributeToDeleteId = null;

function deleteAttribute(id) {
    attributeToDeleteId = id;
    $('#deleteAttributeModal').modal('show');
    $('#confirmDeleteAttribute').off('click').on('click', function() {
        if (attributeToDeleteId !== null) {
            $.ajax({
                url: '{{ route('admin.attributes.destroy', ':id') }}'.replace(':id', attributeToDeleteId),
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        $('#attributes-table').DataTable().ajax.reload();
                        showToast('success', response.message);
                        $('#deleteAttributeModal').modal('hide');
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', "Error deleting attribute! Please try again.");
                    $('#deleteAttributeModal').modal('hide');
                }
            });
        }
    });
}
</script>
@endsection
