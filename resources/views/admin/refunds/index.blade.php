@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header
    :title="'Refunds'"
    icon="bi bi-arrow-counterclockwise"
    :subtitle="'Manage customer return requests, refund approvals and transaction reversals'"
    :breadcrumbs="['Refunds' => '#']" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="refunds-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Order ID' }}</th>
                    <th>{{ 'Amount' }}</th>
                    <th>{{ 'Status' }}</th>
                    <th>{{ 'Created At' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#refunds-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.refunds.getData') }}",
            type: 'GET',
            data: function(d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'order_id', name: 'order_id' },
            { data: 'amount', name: 'amount' },
            { data: 'status', name: 'status' },
            { data: 'created_at', name: 'created_at' },
            {
                data: 'action', name: 'action', orderable: false, searchable: false,
                render: function(data, type, row) {
                    return `<div class="dt-actions">
                        <a href="/admin/refunds/${row.id}" class="btn-action btn-action-edit" title="View"><i class="bi bi-eye-fill"></i></a>
                    </div>`;
                }
            }
        ],
        pageLength: 10,
        language: @json($datatableLang),
        order: [[0, 'desc']]
    });
});
</script>
@endsection
