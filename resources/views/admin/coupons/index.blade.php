@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header
    :title="'Coupons'"
    icon="bi bi-ticket-perforated-fill"
    :subtitle="'Manage promotional discounts, coupon codes and expiry rules'"
    :breadcrumbs="['Coupons' => '#']"
    :create-route="route('admin.coupons.create')"
    :create-label="'Add Coupon'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="coupons-table" class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ 'Code' }}</th>
                    <th>{{ 'Discount' }}</th>
                    <th>{{ 'Type' }}</th>
                    <th>{{ 'Expires At' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteCouponModal" confirm-id="confirmDeleteCoupon"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this coupon?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#coupons-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.coupons.data') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}" }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'code', name: 'code' },
            { data: 'discount', name: 'discount' },
            { data: 'type', name: 'type' },
            { data: 'expires_at', name: 'expires_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });
});

function deleteCoupon(id) {
    $('#deleteCouponModal').modal('show');
    $('#confirmDeleteCoupon').off('click').on('click', function() {
        $.ajax({
            url: "{{ route('admin.coupons.index') }}/" + id,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                $('#deleteCouponModal').modal('hide');
                $('#coupons-table').DataTable().ajax.reload();
                showToast('success', res.message);
            }
        });
    });
}
</script>
@endsection
