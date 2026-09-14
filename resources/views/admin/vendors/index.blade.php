@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header
    :title="'Vendors'"
    icon="bi bi-shop-window"
    :subtitle="'Manage marketplace vendors, shop profiles, and seller statuses'"
    :breadcrumbs="['Vendors' => '#']"
    :create-route="route('admin.vendors.create')"
    :create-label="'Add Vendor'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="vendors-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Vendor Name' }}</th>
                    <th>{{ 'Email' }}</th>
                    <th>{{ 'Status' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteVendorModal" confirm-id="confirmDeleteVendor"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this vendor?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#vendors-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.vendors.data') }}",
            type: 'POST',
            data: function(d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            { data: 'id', name: 'id' },
            {
                data: 'name', name: 'name',
                render: function(data, type, row) {
                    return `<div>
                        <div class="fw-semibold">${data}</div>
                        <div class="small text-muted">${row.phone || ''}</div>
                    </div>`;
                }
            },
            { data: 'email', name: 'email' },
            {
                data: 'status', name: 'status',
                render: function(data) {
                    const map = {
                        'active':   { label: '{{ 'Active' }}',   cls: 'bg-success-soft text-success' },
                        'inactive': { label: '{{ 'Inactive' }}', cls: 'bg-secondary-soft text-secondary' },
                        'banned':   { label: '{{ 'Banned' }}',   cls: 'bg-danger-soft text-danger' },
                    };
                    const s = map[data] || { label: data, cls: 'bg-secondary-soft text-secondary' };
                    return `<span class="badge ${s.cls} px-3 fw-semibold text-capitalize">${s.label}</span>`;
                }
            },
            {
                data: 'action', name: 'action', orderable: false, searchable: false,
                render: function(data, type, row) {
                    return `<div class="dt-actions">
                        <a href="/admin/vendors/${row.id}/edit" class="btn-action btn-action-edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                        <button type="button" class="btn-action btn-action-delete" onclick="deleteVendor(${row.id})" title="Delete"><i class="bi bi-trash-fill"></i></button>
                    </div>`;
                }
            }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });
});

let vendorToDeleteId = null;

function deleteVendor(id) {
    vendorToDeleteId = id;
    $('#deleteVendorModal').modal('show');
    $('#confirmDeleteVendor').off('click').on('click', function() {
        $.ajax({
            url: '{{ route('admin.vendors.destroy', ':id') }}'.replace(':id', vendorToDeleteId),
            method: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#deleteVendorModal').modal('hide');
                $('#vendors-table').DataTable().ajax.reload();
                showToast('success', response.message || "{{ 'Vendor deleted.' }}");
            },
            error: function() { showToast('error', "{{ 'Error deleting vendor.' }}"); }
        });
    });
}
</script>
@endsection
