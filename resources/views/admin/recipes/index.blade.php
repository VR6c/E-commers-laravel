@extends('admin.layouts.admin')

@section('title', 'Manage Recipes - Admin')

@section('content')

<x-admin.page-header
    :title="'Manage Recipes'"
    icon="bi bi-book"
    :subtitle="'Create gourmet recipes, link products, and manage downloadable cards'"
    :breadcrumbs="['Recipes' => '#']"
    :create-route="route('admin.recipes.create')"
    :create-label="'Add Recipe'" />

<x-admin.data-card>
    <div class="table-responsive">
        <table id="recipes-table" class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Time</th>
                    <th>Difficulty</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.data-card>

@push('modals')
<x-admin.delete-modal id="deleteRecipeModal" confirm-id="confirmDeleteRecipe"
    :title="'Confirm Delete Recipe'"
    :message="'Are you sure you want to delete this recipe? This will remove all product linkages.'"
    :confirm-label="'Delete'"
    :cancel-label="'Cancel'" />
@endpush

@endsection

@section('js')
<script>
    $(document).ready(function() {
        var table = $('#recipes-table').DataTable({
            processing: true,
            serverSide: true,
            dom: '<"dt-toolbar"<"dt-toolbar__left"l><"dt-toolbar__right"f>>' +
                 'rt' +
                 '<"dt-footer"<"dt-footer__info"i><"dt-footer__paging"p>>',
            ajax: {
                url: "{{ route('admin.recipes.data') }}",
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'id', name: 'id', width: '50px' },
                { data: 'image', name: 'image', orderable: false, searchable: false, width: '60px' },
                { data: 'title', name: 'title' },
                { data: 'time', name: 'prep_time', width: '90px' },
                { data: 'difficulty', name: 'difficulty', width: '90px' },
                { data: 'products_count', name: 'products_count', orderable: false, searchable: false },
                { data: 'status', name: 'is_active', orderable: false, searchable: false, width: '70px' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end', width: '130px' }
            ]
        });

        // Status Toggle Handler
        $(document).on('change', '.status-toggle', function() {
            var recipeId = $(this).data('id');
            $.ajax({
                url: "{{ route('admin.recipes.updateStatus') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: recipeId
                },
                success: function(response) {
                    if (window.toastr) {
                        toastr.success(response.message);
                    }
                },
                error: function() {
                    if (window.toastr) {
                        toastr.error('Failed to update status.');
                    }
                    table.ajax.reload(null, false);
                }
            });
        });

        // Delete confirmation modal handler
        var deleteUrl = null;
        $(document).on('click', '.btn-delete', function() {
            deleteUrl = $(this).data('url');
            $('#deleteRecipeModal').modal('show');
        });

        $('#confirmDeleteRecipe').on('click', function() {
            if (!deleteUrl) return;

            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#deleteRecipeModal').modal('hide');
                    if (window.toastr) {
                        toastr.success(response.message);
                    }
                    table.ajax.reload(null, false);
                },
                error: function() {
                    $('#deleteRecipeModal').modal('hide');
                    if (window.toastr) {
                        toastr.error('Failed to delete recipe.');
                    }
                }
            });
        });
    });
</script>
@endsection
