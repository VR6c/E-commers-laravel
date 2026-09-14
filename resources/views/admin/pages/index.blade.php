@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header
    :title="'Pages'"
    icon="bi bi-file-earmark-text-fill"
    :subtitle="'Create and manage CMS content pages, terms, and policy documents'"
    :breadcrumbs="['Pages' => '#']"
    :create-route="route('admin.pages.create')"
    :create-label="'Add Page'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="pagesTable" class="table align-middle">
            <thead>
                <tr>
                    <th>{{ 'Title' }}</th>
                    <th>{{ 'Slug' }}</th>
                    <th>{{ 'Status' }}</th>
                    <th class="text-end">{{ 'Actions' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

<x-admin.delete-modal id="deletePageModal" confirm-id="confirmDeletePage"
    :title="'Confirm Delete'"
    :message="'Are you sure you want to delete this page?'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@php $datatableLang = null; @endphp

<script>
let pageToDeleteId = null;

$(document).ready(function() {
    const table = $('#pagesTable').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>rt<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
        ajax: {
            url: "{{ route('admin.pages.data') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}" }
        },
        columns: [
            { data: 'translated_title', name: 'translated_title' },
            { data: 'slug', name: 'slug' },
            { data: 'status', name: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });

    window.deletePage = function(id) {
        pageToDeleteId = id;
        $('#deletePageModal').modal('show');
    };

    $('#confirmDeletePage').off('click').on('click', function() {
        if (!pageToDeleteId) return;
        $.ajax({
            url: '{{ route("admin.pages.destroy", ":id") }}'.replace(':id', pageToDeleteId),
            method: 'POST',
            data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
            success: function(response) {
                table.ajax.reload();
                $('#deletePageModal').modal('hide');
                showToast('success', response.message || "{{ 'Page deleted.' }}");
            },
            error: function() {
                showToast('error', "{{ 'Error deleting page.' }}");
                $('#deletePageModal').modal('hide');
            }
        });
    });
});
</script>
@endsection
