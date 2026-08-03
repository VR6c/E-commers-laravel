@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header :title="'Customers'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="customers-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Name' }}</th>
                    <th>{{ 'Email' }}</th>
                    <th>{{ 'Phone' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteCustomerModal" confirm-id="confirmDeleteCustomer"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this customer?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.customers.data') }}",
            type: 'POST',
            data: function(d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            {
                data: 'action', name: 'action', orderable: false, searchable: false,
                render: function(data, type, row) {
                    return `<div class="dt-actions">
                        <button type="button" class="btn-action btn-action-delete" onclick="deleteCustomer(${row.id})" title="Delete" aria-label="Delete customer ${row.id}"><i class="bi bi-trash-fill"></i></button>
                    </div>`;
                }
            }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });
});

let customerToDeleteId = null;

function deleteCustomer(id) {
    customerToDeleteId = id;
    $('#deleteCustomerModal').modal('show');
    $('#confirmDeleteCustomer').off('click').on('click', function() {
        $.ajax({
            url: '{{ route('admin.customers.destroy', ':id') }}'.replace(':id', customerToDeleteId),
            method: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#deleteCustomerModal').modal('hide');
                $('#customers-table').DataTable().ajax.reload();
                showToast('success', "Customer deleted successfully");
            },
            error: function() { showToast('error', "Error deleting customer"); }
        });
    });
}
</script>
@endsection
