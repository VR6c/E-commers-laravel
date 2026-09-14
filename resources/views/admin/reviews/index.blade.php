@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header
    :title="'Product Reviews'"
    icon="bi bi-star-fill"
    :subtitle="'Moderate customer product feedback, approval statuses and ratings'"
    :breadcrumbs="['Reviews' => '#']" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="reviews-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Product' }}</th>
                    <th>{{ 'Customer' }}</th>
                    <th>{{ 'Rating' }}</th>
                    <th>{{ 'Status' }}</th>
                    <th class="text-end">{{ 'Actions' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteReviewModal" confirm-id="confirmDeleteReview"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this review?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#reviews-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.reviews.data') }}",
            type: 'POST',
            data: function(d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'product', name: 'product' },
            { data: 'customer', name: 'customer' },
            {
                data: 'rating', name: 'rating',
                render: function(data) {
                    let stars = '';
                    for (let i = 1; i <= 5; i++) {
                        stars += `<i class="bi bi-star${i <= data ? '-fill text-warning' : ' text-muted'}"></i>`;
                    }
                    return stars;
                }
            },
            { data: 'status', name: 'status' },
            {
                data: 'action', name: 'action', orderable: false, searchable: false,
                render: function(data, type, row) {
                    return `<div class="dt-actions">
                        <a href="/admin/reviews/${row.id}" class="btn-action btn-action-edit" title="View"><i class="bi bi-eye-fill"></i></a>
                        <button type="button" class="btn-action btn-action-delete" onclick="deleteReview(${row.id})" title="Delete"><i class="bi bi-trash-fill"></i></button>
                    </div>`;
                }
            }
        ],
        pageLength: 10,
        language: @json($datatableLang),
        order: [[0, 'desc']]
    });
});

let reviewToDeleteId = null;

function deleteReview(id) {
    reviewToDeleteId = id;
    $('#deleteReviewModal').modal('show');
    $('#confirmDeleteReview').off('click').on('click', function() {
        $.ajax({
            url: '{{ route('admin.reviews.destroy', ':id') }}'.replace(':id', reviewToDeleteId),
            method: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#deleteReviewModal').modal('hide');
                $('#reviews-table').DataTable().ajax.reload();
                showToast('success', "Review deleted successfully");
            },
            error: function() { showToast('error', "Error deleting review"); }
        });
    });
}
</script>
@endsection
