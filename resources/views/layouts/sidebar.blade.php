<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-heart-pulse"></i>
        </div>
        <h5>LabsHealth UKS</h5>
    </div>

    <nav class="sidebar-nav">
        @isset($sidebarMenus)
            @foreach($sidebarMenus as $menu)
                @include('layouts._sidebar_item', ['menu' => $menu])
            @endforeach
        @endisset
    </nav>

    <div class="sidebar-footer">
        <a href="{{ route('dashboard') }}" class="nav-link">
            <i class="bi bi-gear"></i>
            <span>Settings</span>
        </a>
        <form action="{{ route('logout') }}" method="POST" class="mt-1">
            @csrf
            <button type="submit" class="nav-link logout-link border-0 bg-transparent w-100 text-start">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
