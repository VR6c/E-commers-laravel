@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header
    :title="'Currencies'"
    icon="bi bi-currency-exchange"
    :subtitle="'Manage active currencies, symbols, and currency exchange rates'"
    :breadcrumbs="['Currencies' => '#']"
    :create-route="route('admin.currencies.create')"
    :create-label="'Add Currency'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="currencies-table" class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ 'Name' }}</th>
                    <th>{{ 'Code' }}</th>
                    <th>{{ 'Symbol' }}</th>
                    <th>{{ 'Exchange Rate' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteCurrencyModal" confirm-id="confirmDeleteCurrency"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this currency?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#currencies-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.currencies.data') }}",
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            { data: 'symbol', name: 'symbol' },
            { data: 'exchange_rate', name: 'exchange_rate' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });
});

function deleteCurrency(id) {
    $('#deleteCurrencyModal').modal('show');
    $('#confirmDeleteCurrency').off('click').on('click', function() {
        $.ajax({
            url: "{{ route('admin.currencies.index') }}/" + id,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                $('#deleteCurrencyModal').modal('hide');
                $('#currencies-table').DataTable().ajax.reload();
                showToast('success', res.message);
            }
        });
    });
}
</script>
@endsection
