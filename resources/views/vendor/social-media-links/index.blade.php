@extends('vendor.layouts.master')

@section('title', 'Social Media Links')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
@endsection

@section('content')

<x-admin.page-header
    :title="'Social Media Links'"
    icon="bi bi-share-fill"
    :subtitle="'Manage your external store and social media profiles'"
    :breadcrumbs="['Social Media' => '#']"
    :create-route="route('vendor.social-media-links.create')"
    :create-label="'Add Link'" />

<x-admin.data-card label="Social Media Links Table">
    <div class="table-responsive">
        <table id="social-links-table" class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Platform</th>
                    <th>Name</th>
                    <th>URL</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal
    id="deleteLinkModal"
    confirm-id="confirmDeleteLink"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this social media link?'"
    :confirm-label="'Delete Link'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    $('#social-links-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('vendor.social-media-links.data') }}",
            type: 'POST',
            data: function (d) { d._token = "{{ csrf_token() }}"; }
        },
        columns: [
            {
                data: 'id', name: 'id',
                render: d => `<span class="fw-bold text-body">#${d}</span>`
            },
            {
                data: 'type', name: 'type',
                render: function (data) {
                    const icons = {
                        facebook:  'bi-facebook text-primary',
                        instagram: 'bi-instagram text-danger',
                        tiktok:    'bi-tiktok text-dark',
                        youtube:   'bi-youtube text-danger',
                        x:         'bi-twitter-x text-dark',
                    };
                    const icon = icons[data] ?? 'bi-link-45deg text-secondary';
                    return `<span class="d-flex align-items-center gap-2 fw-medium">
                        <i class="bi ${icon} fs-5"></i>
                        ${data.charAt(0).toUpperCase() + data.slice(1)}
                    </span>`;
                }
            },
            {
                data: 'platform', name: 'platform',
                render: d => `<span class="fw-semibold text-dark">${d}</span>`
            },
            { data: 'name', name: 'name' },
            {
                data: 'link', name: 'link',
                render: d => `<a href="${d}" target="_blank" rel="noopener"
                                  class="text-primary text-decoration-none text-truncate d-block"
                                  style="max-width:220px;" title="${d}">${d}</a>`
            },
            {
                data: 'action', name: 'action',
                orderable: false, searchable: false,
                className: 'text-end',
                render: (data, type, row) => data ||
                    `<div class="dt-actions">
                        <a href="/vendor/social-media-links/${row.id}/edit"
                           class="btn-action btn-action-edit" title="Edit">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <button type="button"
                                class="btn-action btn-action-delete"
                                onclick="deleteLink(${row.id})" title="Delete">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>`
            }
        ],
        pageLength: 10,
        language: {
            search: '',
            searchPlaceholder: 'Search social links…',
            lengthMenu: 'Show _MENU_',
            zeroRecords: 'No matching social links found.',
            info: 'Showing _START_–_END_ of _TOTAL_ entries',
            infoEmpty: 'No social links available',
            infoFiltered: '(filtered from _MAX_ total)',
        }
    });
});

let linkToDeleteId = null;

function deleteLink(id) {
    linkToDeleteId = id;
    $('#deleteLinkModal').modal('show');
    $('#confirmDeleteLink').off('click').on('click', function () {
        if (linkToDeleteId !== null) {
            $.ajax({
                url: '{{ route('vendor.social-media-links.destroy', ':id') }}'.replace(':id', linkToDeleteId),
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function (response) {
                    $('#deleteLinkModal').modal('hide');
                    $('#social-links-table').DataTable().ajax.reload();
                    showToast('success', response.message ?? 'Link deleted successfully.');
                },
                error: function () {
                    $('#deleteLinkModal').modal('hide');
                    showToast('error', 'Error deleting link.');
                }
            });
        }
    });
}
</script>
@endsection
