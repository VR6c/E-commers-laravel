@props([
    'user',
    'role'          => 'Administrator',
    'profileRoute',
    'dashboardRoute',
    'logoutRoute',
    'logoutFormId'  => 'panel-logout-form',
    'settingsRoute' => null,
    'search'        => false,
])

@php
    $name   = $user?->name ?? $role;
    $avatar = $user && $user->profile_image
        ? (\Illuminate\Support\Str::startsWith($user->profile_image, ['http://', 'https://'])
            ? $user->profile_image
            : asset('storage/' . $user->profile_image))
        : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=4f46e5&color=fff&size=40';
@endphp

<nav class="navbar navbar-expand navbar-light p-0 main-header sticky-top" aria-label="Top navigation">
    <div class="d-flex align-items-center gap-2 ps-3">
        <button class="topbar-icon-btn" id="sidebarToggle" type="button"
                aria-label="Toggle sidebar" aria-expanded="true" aria-controls="sidebar">
            <i class="bi bi-list"></i>
        </button>

        @if ($search)
            <button type="button" class="topbar-pill" onclick="window.openCmdk()">
                <i class="bi bi-search text-primary"></i>
                <span class="d-none d-sm-inline">Quick search or command...</span>
                <kbd class="d-none d-md-inline">⌘K</kbd>
            </button>
        @endif
    </div>

    <div class="d-flex align-items-center gap-2 ms-auto pe-3 pe-md-4">
        <a href="/" target="_blank" rel="noopener" class="topbar-pill" title="View Storefront">
            <i class="bi bi-shop text-primary"></i>
            <span class="d-none d-md-inline">Storefront</span>
            <i class="bi bi-arrow-up-right text-muted topbar-pill__ext"></i>
        </a>

        <span class="topbar-divider" aria-hidden="true"></span>

        <div class="dropdown">
            <button class="topbar-profile" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false" aria-label="Account menu" type="button">
                <img src="{{ $avatar }}" alt="{{ $name }}" width="34" height="34" class="topbar-profile__avatar">
                <span class="topbar-profile__name d-none d-md-inline">{{ $name }}</span>
                <i class="bi bi-chevron-down d-none d-md-inline text-muted topbar-pill__ext"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end topbar-menu">
                <li class="topbar-menu__head">
                    <p class="topbar-menu__name">{{ $name }}</p>
                    <p class="topbar-menu__email">{{ $user?->email }}</p>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">{{ $role }}</span>
                </li>
                <li><a class="dropdown-item" href="{{ $profileRoute }}"><i class="bi bi-person-circle"></i> My Profile</a></li>
                <li><a class="dropdown-item" href="{{ $dashboardRoute }}"><i class="bi bi-grid"></i> Dashboard</a></li>
                @if ($settingsRoute)
                    <li><a class="dropdown-item" href="{{ $settingsRoute }}"><i class="bi bi-gear"></i> Site Settings</a></li>
                @endif
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form id="{{ $logoutFormId }}" action="{{ $logoutRoute }}" method="POST" class="d-none">@csrf</form>
                    <a class="dropdown-item text-danger" href="#"
                       onclick="event.preventDefault(); document.getElementById('{{ $logoutFormId }}').submit();">
                        <i class="bi bi-box-arrow-right"></i> {{ __('Logout') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
