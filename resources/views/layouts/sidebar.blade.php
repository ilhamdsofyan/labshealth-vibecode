<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('assets/img/Logo Labschool Bintaro.png') }}" alt="LabsHealth UKS" class="brand-logo-long">
        <img src="{{ asset('assets/img/Logo.png') }}" alt="LabsHealth UKS" class="brand-logo-square">
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
