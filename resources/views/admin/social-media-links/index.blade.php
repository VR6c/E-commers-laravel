@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header
    :title="'Social Media Links'"
    icon="bi bi-share-fill"
    :subtitle="'Manage official brand social profiles and footer channel links'"
    :breadcrumbs="['Social Media' => '#']"
    :create-route="route('admin.social-media-links.create')"
    :create-label="'Add Link'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="social-links-table" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Platform' }}</th>
                    <th>{{ 'URL' }}</th>
                    <th>{{ 'Type' }}</th>
                    <th class="text-end">{{ 'Action' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deleteLinkModal" confirm-id="confirmDeleteLink"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this link?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
$(document).ready(function() {
    $('#social-links-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.social-media-links.data') }}",
            type: 'POST',
            data: function(d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'platform', name: 'platform' },
            {
                data: 'link', name: 'link',
                render: function(data) {
                    return `<a href="${data}" target="_blank" class="text-muted small text-decoration-none">${data}</a>`;
                }
            },
            {
                data: 'type', name: 'type',
                render: function(data) {
                    return `<span class="badge bg-primary-soft text-primary text-uppercase">${data || '—'}</span>`;
                }
            },
            {
                data: 'action', name: 'action', orderable: false, searchable: false,
                render: function(data, type, row) {
                    return `<div class="dt-actions">
                        <a href="/admin/social-media-links/${row.id}/edit" class="btn-action btn-action-edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                        <button type="button" class="btn-action btn-action-delete" onclick="deleteLink(${row.id})" title="Delete"><i class="bi bi-trash-fill"></i></button>
                    </div>`;
                }
            }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });
});

let linkToDeleteId = null;

function deleteLink(id) {
    linkToDeleteId = id;
    $('#deleteLinkModal').modal('show');
    $('#confirmDeleteLink').off('click').on('click', function() {
        $.ajax({
            url: '{{ route('admin.social-media-links.destroy', ':id') }}'.replace(':id', linkToDeleteId),
            method: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#deleteLinkModal').modal('hide');
                $('#social-links-table').DataTable().ajax.reload();
                showToast('success', "Link deleted successfully");
            },
            error: function() { showToast('error', "Error deleting link"); }
        });
    });
}
</script>
@endsection
