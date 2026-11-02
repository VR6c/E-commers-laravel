{{-- Shared account navigation. Usage: @include('themes.xylo.customer._nav', ['active' => 'orders']) --}}
@php $active = $active ?? ''; @endphp
<nav class="xsf-account-nav" aria-label="{{ 'Account' }}">
    <a href="{{ route('customer.profile.edit') }}" class="xsf-account-nav__link {{ $active === 'profile' ? 'is-active' : '' }}" @if($active === 'profile') aria-current="page" @endif>
        <i class="fa-solid fa-user-gear" aria-hidden="true"></i> <span>{{ 'My Profile' }}</span>
    </a>
    <a href="{{ route('customer.orders.index') }}" class="xsf-account-nav__link {{ $active === 'orders' ? 'is-active' : '' }}" @if($active === 'orders') aria-current="page" @endif>
        <i class="fa-solid fa-box-archive" aria-hidden="true"></i> <span>{{ 'My Orders' }}</span>
    </a>
    <a href="{{ route('customer.recipes.index') }}" class="xsf-account-nav__link {{ $active === 'recipes' ? 'is-active' : '' }}" @if($active === 'recipes') aria-current="page" @endif>
        <i class="fa-solid fa-utensils" aria-hidden="true"></i> <span>{{ 'My Recipes' }}</span>
    </a>
    <a href="{{ route('customer.wishlist.index') }}" class="xsf-account-nav__link {{ $active === 'wishlist' ? 'is-active' : '' }}">
        <i class="fa-regular fa-heart" aria-hidden="true"></i> <span>{{ 'Wishlist' }}</span>
    </a>
    <a href="{{ route('xylo.home') }}" class="xsf-account-nav__link">
        <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> <span>{{ 'Continue Shopping' }}</span>
    </a>
    <a href="{{ route('customer.logout') }}" class="xsf-account-nav__link xsf-account-nav__link--danger"
        onclick="event.preventDefault(); document.getElementById('account-logout-form').submit();">
        <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> <span>{{ 'Logout' }}</span>
    </a>
    <form id="account-logout-form" action="{{ route('customer.logout') }}" method="POST" class="d-none">@csrf</form>
</nav>
