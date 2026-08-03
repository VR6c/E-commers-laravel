{{-- ── COMMAND MENU (cmdk / ⌘K Palette) ────────────────────────────────── --}}
<div id="cmdk-backdrop" class="cmdk-backdrop" style="display: none;" aria-hidden="true">
    <div class="cmdk-modal" role="dialog" aria-modal="true" aria-label="Command Menu">
        {{-- Search Header --}}
        <div class="cmdk-header">
            <i class="bi bi-search cmdk-search-icon"></i>
            <input type="text" 
                   id="cmdk-input" 
                   class="cmdk-input" 
                   placeholder="Type a command or search products..." 
                   autocomplete="off"
                   spellcheck="false">
            <kbd class="cmdk-kbd">ESC</kbd>
        </div>

        {{-- Scrollable List Area --}}
        <div class="cmdk-body" id="cmdk-body">
            {{-- Quick Navigation Group --}}
            <div class="cmdk-group" data-group="navigation">
                <div class="cmdk-group-title">Navigation</div>
                
                <a href="{{ route('admin.dashboard') }}" class="cmdk-item" data-search="dashboard home overview">
                    <i class="bi bi-grid-1x2 cmdk-item-icon"></i>
                    <span class="cmdk-item-label">Dashboard</span>
                    <span class="cmdk-item-shortcut">G D</span>
                </a>

                <a href="{{ route('admin.products.index') }}" class="cmdk-item" data-search="products catalog inventory">
                    <i class="bi bi-box-seam cmdk-item-icon"></i>
                    <span class="cmdk-item-label">All Products</span>
                    <span class="cmdk-item-shortcut">G P</span>
                </a>

                <a href="{{ route('admin.products.create') }}" class="cmdk-item" data-search="add product new create">
                    <i class="bi bi-plus-circle cmdk-item-icon text-success"></i>
                    <span class="cmdk-item-label">Add New Product</span>
                    <span class="cmdk-item-shortcut">N P</span>
                </a>

                <a href="{{ route('admin.categories.index') }}" class="cmdk-item" data-search="categories taxonomy">
                    <i class="bi bi-folder2-open cmdk-item-icon"></i>
                    <span class="cmdk-item-label">Categories</span>
                    <span class="cmdk-item-shortcut">G C</span>
                </a>

                <a href="{{ route('admin.brands.index') }}" class="cmdk-item" data-search="brands manufacturers">
                    <i class="bi bi-tag cmdk-item-icon"></i>
                    <span class="cmdk-item-label">Brands</span>
                    <span class="cmdk-item-shortcut">G B</span>
                </a>

                <a href="{{ route('admin.orders.index') }}" class="cmdk-item" data-search="orders sales transactions">
                    <i class="bi bi-cart-check cmdk-item-icon"></i>
                    <span class="cmdk-item-label">Orders</span>
                    <span class="cmdk-item-shortcut">G O</span>
                </a>

                <a href="{{ route('admin.customers.index') }}" class="cmdk-item" data-search="customers users accounts">
                    <i class="bi bi-people cmdk-item-icon"></i>
                    <span class="cmdk-item-label">Customers</span>
                    <span class="cmdk-item-shortcut">G U</span>
                </a>

                <a href="{{ route('admin.banners.index') }}" class="cmdk-item" data-search="banners sliders promotions">
                    <i class="bi bi-images cmdk-item-icon"></i>
                    <span class="cmdk-item-label">Banners</span>
                </a>

                <a href="{{ route('admin.site-settings.index') }}" class="cmdk-item" data-search="settings configuration site">
                    <i class="bi bi-gear cmdk-item-icon"></i>
                    <span class="cmdk-item-label">Site Settings</span>
                </a>
            </div>

            {{-- Storefront Group --}}
            <div class="cmdk-group" data-group="storefront">
                <div class="cmdk-group-title">Storefront</div>
                
                <a href="/" target="_blank" class="cmdk-item" data-search="store shop website homepage">
                    <i class="bi bi-shop cmdk-item-icon text-primary"></i>
                    <span class="cmdk-item-label">Visit Storefront</span>
                    <i class="bi bi-box-arrow-up-right ms-auto text-muted" style="font-size:0.75rem;"></i>
                </a>
            </div>

            {{-- Account Group --}}
            <div class="cmdk-group" data-group="account">
                <div class="cmdk-group-title">Account</div>
                
                <a href="{{ route('admin.profile.edit') }}" class="cmdk-item" data-search="profile account user details">
                    <i class="bi bi-person-circle cmdk-item-icon"></i>
                    <span class="cmdk-item-label">Edit My Profile</span>
                </a>
            </div>

            {{-- Empty Search Results State --}}
            <div id="cmdk-empty" class="cmdk-empty" style="display: none;">
                <i class="bi bi-search-heart" style="font-size: 1.8rem; color: #9ca3af;"></i>
                <p class="mb-0 mt-2 text-muted" style="font-size: 0.88rem;">No matching commands or actions found.</p>
            </div>
        </div>

        {{-- Command Menu Footer --}}
        <div class="cmdk-footer">
            <div class="d-flex align-items-center gap-3">
                <span><kbd class="cmdk-kbd-sm">↑</kbd> <kbd class="cmdk-kbd-sm">↓</kbd> Navigate</span>
                <span><kbd class="cmdk-kbd-sm">↵</kbd> Select</span>
                <span><kbd class="cmdk-kbd-sm">ESC</kbd> Close</span>
            </div>
            <div class="ms-auto fw-semibold text-secondary" style="font-size:0.72rem;">
                Command Palette (⌘K)
            </div>
        </div>
    </div>
</div>

{{-- ── COMMAND MENU STYLES ───────────────────────────────────────────── --}}
<style>
.cmdk-backdrop {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 10000;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 10vh;
}

.cmdk-modal {
    width: 100%;
    max-width: 620px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 75vh;
}

.cmdk-header {
    display: flex;
    align-items: center;
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
    gap: 12px;
}

.cmdk-search-icon {
    font-size: 1.15rem;
    color: #64748b;
    flex-shrink: 0;
}

.cmdk-input {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    width: 100%;
    font-size: 0.95rem;
    font-weight: 500;
    color: #1e293b;
    background: transparent;
}

.cmdk-input::placeholder {
    color: #94a3b8;
}

.cmdk-kbd {
    font-size: 0.68rem;
    font-family: inherit;
    font-weight: 600;
    padding: 3px 7px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.cmdk-kbd-sm {
    font-size: 0.65rem;
    font-family: inherit;
    padding: 1px 5px;
    border-radius: 4px;
    background: #e2e8f0;
    color: #475569;
}

.cmdk-body {
    padding: 8px 10px;
    overflow-y: auto;
    flex: 1;
}

.cmdk-group {
    margin-bottom: 10px;
}

.cmdk-group-title {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    padding: 6px 10px 4px 10px;
}

.cmdk-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    text-decoration: none !important;
    color: #334155;
    font-size: 0.88rem;
    font-weight: 500;
    transition: background 0.12s ease, color 0.12s ease;
    cursor: pointer;
}

.cmdk-item:hover,
.cmdk-item.active {
    background: #f1f5f9;
    color: #0f172a;
}

.cmdk-item.active {
    background: #e0e7ff;
    color: #4338ca;
}

.cmdk-item-icon {
    font-size: 1.05rem;
    width: 24px;
    text-align: center;
    color: #64748b;
    flex-shrink: 0;
}

.cmdk-item.active .cmdk-item-icon {
    color: #4338ca;
}

.cmdk-item-shortcut {
    margin-left: auto;
    font-size: 0.7rem;
    font-weight: 600;
    color: #94a3b8;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 2px 6px;
    border-radius: 6px;
}

.cmdk-empty {
    padding: 36px 16px;
    text-align: center;
}

.cmdk-footer {
    display: flex;
    align-items: center;
    padding: 10px 18px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    font-size: 0.75rem;
    color: #64748b;
}

@keyframes cmdkFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes cmdkScaleIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
</style>


