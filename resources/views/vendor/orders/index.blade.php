@extends('vendor.layouts.master')

@section('title', 'Orders')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
@endsection

@section('content')

<x-admin.page-header
    :title="'Orders'"
    icon="bi bi-bag-check-fill"
    :subtitle="'View customer purchases and fulfillment status for your products'"
    :breadcrumbs="['Orders' => '#']" />

<x-admin.data-card label="Vendor Orders Table">
    <div class="table-responsive">
        <table id="orders-table" class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Total Price</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal
    id="deleteOrderModal"
    confirm-id="confirmDeleteOrder"
    :title="'Remove Order Items'"
    :message="'Are you sure you want to remove your items from this order? If other vendors have items in this order, their items will remain intact.'"
    :confirm-label="'Remove Items'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(function () {
    $('#orders-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('vendor.orders.data') }}",
            type: 'POST',
            data: function (d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            {
                data: 'id', name: 'id',
                render: d => `<span class="fw-bold text-body">#${String(d).padStart(5, '0')}</span>`
            },
            {
                data: 'customer', name: 'customer',
                orderable: false, searchable: false
            },
            {
                data: 'order_date', name: 'order_date',
                orderable: false, searchable: false
            },
            {
                data: 'status', name: 'status',
                orderable: false, searchable: false
            },
            {
                data: 'total_price', name: 'total_price',
                orderable: false, searchable: false,
                render: d => `<span class="fw-semibold text-primary">${d}</span>`
            },
            {
                data: 'action', orderable: false, searchable: false,
                className: 'text-end',
                render: (data, type, row) => data ||
                    `<div class="dt-actions">
                        <a href="/vendor/orders/${row.id}"
                           class="btn-action btn-action-view" title="View Details">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                        <button type="button"
                                class="btn-action btn-action-delete"
                                onclick="deleteOrder(${row.id})" title="Remove Items">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>`
            }
        ],
        pageLength: 10,
        language: {
            search: '',
            searchPlaceholder: 'Search orders…',
            lengthMenu: 'Show _MENU_',
            zeroRecords: 'No matching orders found.',
            info: 'Showing _START_–_END_ of _TOTAL_ entries',
            infoEmpty: 'No orders available',
            infoFiltered: '(filtered from _MAX_ total)',
        }
    });
});

let orderToDeleteId = null;

function deleteOrder(id) {
    orderToDeleteId = id;
    $('#deleteOrderModal').modal('show');
    $('#confirmDeleteOrder').off('click').on('click', function () {
        if (orderToDeleteId !== null) {
            $.ajax({
                url: '{{ route('vendor.orders.destroy', ':id') }}'.replace(':id', orderToDeleteId),
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function (response) {
                    $('#deleteOrderModal').modal('hide');
                    if (response.success) {
                        $('#orders-table').DataTable().ajax.reload();
                        showToast('success', response.message ?? 'Order items removed successfully.');
                    } else {
                        showToast('error', response.message ?? 'Failed to remove order items.');
                    }
                },
                error: function () {
                    $('#deleteOrderModal').modal('hide');
                    showToast('error', 'An error occurred while removing order items.');
                }
            });
        }
    });
}
</script>
@endsection
