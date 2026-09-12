@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header :title="'All Banners'"
    :create-route="route('admin.banners.create')"
    :create-label="'Add New'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="banners-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Image' }}</th>
                    <th>{{ 'Title' }}</th>
                    <th>{{ 'Status' }}</th>
                    <th class="text-end">{{ 'Actions' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteBannerModal" confirm-id="confirmDeleteBanner"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this banner?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#banners-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.banners.data') }}",
            type: 'POST',
            data: function(d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            { data: 'id', name: 'id' },
            {
                data: 'image', name: 'image', orderable: false, searchable: false,
                render: function(data) {
                    if (!data) return '<span class="text-muted">—</span>';
                    return `<img src="${data}" class="dt-product-thumb" alt="banner" onerror="this.onerror=null;this.src='/images/no-product.png';">`;
                }
            },
            { data: 'title', name: 'title' },
            { data: 'status', name: 'status' },
            {
                data: 'action', name: 'action', orderable: false, searchable: false,
                render: function(data, type, row) {
                    return `<div class="dt-actions">
                        <a href="/admin/banners/${row.id}/edit" class="btn-action btn-action-edit" title="Edit" aria-label="Edit banner ${row.id}"><i class="bi bi-pencil-fill"></i></a>
                        <button type="button" class="btn-action btn-action-delete" onclick="deleteBanner(${row.id})" title="Delete" aria-label="Delete banner ${row.id}"><i class="bi bi-trash-fill"></i></button>
                    </div>`;
                }
            }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });
});

let bannerToDeleteId = null;

function deleteBanner(id) {
    bannerToDeleteId = id;
    $('#deleteBannerModal').modal('show');
    $('#confirmDeleteBanner').off('click').on('click', function() {
        $.ajax({
            url: '{{ route('admin.banners.destroy', ':id') }}'.replace(':id', bannerToDeleteId),
            method: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#deleteBannerModal').modal('hide');
                $('#banners-table').DataTable().ajax.reload();
                showToast('success', "Banner deleted successfully");
            },
            error: function() { showToast('error', "Error deleting banner"); }
        });
    });
}
</script>
@endsection
