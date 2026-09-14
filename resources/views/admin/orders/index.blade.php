@extends('admin.layouts.admin')

@section('title', ($pageTitle ?? 'Orders') . ' — Admin')

@section('content')

<x-admin.page-header
    :title="$pageTitle ?? 'Manage Orders'"
    icon="bi bi-bag-check-fill"
    :subtitle="'Track and manage customer orders, fulfillment and payment statuses'"
    :breadcrumbs="['Orders' => route('admin.orders.index')]">
    <x-slot:actions>
        <div class="btn-group shadow-xs rounded-3 overflow-hidden" role="group" aria-label="Order status filter">
            <a href="{{ route('admin.orders.index') }}"
               class="btn btn-sm {{ Route::currentRouteName() == 'admin.orders.index' ? 'btn-primary' : 'btn-light border' }}">
                All
            </a>
            <a href="{{ route('admin.orders.pending') }}"
               class="btn btn-sm {{ Route::currentRouteName() == 'admin.orders.pending' ? 'btn-primary' : 'btn-light border' }}">
                Pending
            </a>
            <a href="{{ route('admin.orders.completed') }}"
               class="btn btn-sm {{ Route::currentRouteName() == 'admin.orders.completed' ? 'btn-primary' : 'btn-light border' }}">
                Completed
            </a>
        </div>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.data-card>
    <div class="table-responsive">
        <table id="orders-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'Order ID' }}</th>
                    <th>{{ 'Customer' }}</th>
                    <th>{{ 'Order Date' }}</th>
                    <th>{{ 'Status' }}</th>
                    <th>{{ 'Total Price' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteOrderModal" confirm-id="confirmDeleteOrder"
    :title="'Confirm Delete Order'"
    :message="'Are you sure you want to delete this order? All associated transaction logs will be removed.'"
    :confirm-label="'Delete Order'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    const table = $('#orders-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.orders.data') }}",
            type: 'POST',
            data: function(d) {
                d._token = "{{ csrf_token() }}";
                d.status_filter = "{{ $statusFilter ?? '' }}";
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'customer', name: 'customer', orderable: false, searchable: true },
            { data: 'order_date', name: 'order_date', orderable: false, searchable: false },
            { data: 'status', name: 'status' },
            { data: 'total_price', name: 'total_price', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });

    let orderToDeleteId = null;

    window.deleteOrder = function(id) {
        orderToDeleteId = id;
        $('#deleteOrderModal').modal('show');
    };

    $('#confirmDeleteOrder').on('click', function() {
        if (orderToDeleteId === null) return;
        $.ajax({
            url: '{{ route("admin.orders.destroy", ":id") }}'.replace(':id', orderToDeleteId),
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                if (res.success) {
                    table.ajax.reload(null, false);
                    showToast('success', res.message);
                    $('#deleteOrderModal').modal('hide');
                    orderToDeleteId = null;
                } else {
                    showToast('error', res.message || 'Failed to delete order');
                }
            },
            error: function() {
                showToast('error', 'An error occurred while deleting the order');
                $('#deleteOrderModal').modal('hide');
            }
        });
    });
});
</script>
@endsection
