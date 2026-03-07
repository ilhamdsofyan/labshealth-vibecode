@extends('layouts.app')

@section('title', 'Kelola Menu')

@push('styles')
<style>
    .dd { position: relative; display: block; margin: 0; padding: 0; list-style: none; font-size: 13px; line-height: 20px; }
    .dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
    .dd-list .dd-list { padding-left: 30px; }
    .dd-collapsed .dd-list { display: none; }
    .dd-item, .dd-empty, .dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; font-size: 13px; line-height: 20px; }
    .dd-handle { 
        display: block; height: 45px; margin: 5px 0; padding: 10px 15px; color: #333; text-decoration: none; font-weight: bold; 
        border: 1px solid #ddd; background: #fff; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        cursor: move;
    }
    .dd-handle:hover { color: var(--primary); background: #f9f9f9; }
    .dd-item > button { 
        display: block; position: relative; cursor: pointer; float: left; width: 25px; height: 35px; 
        margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; 
        border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center; font-weight: bold; 
    }
    .dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-indent: 0; }
    .dd-item > button[data-action="collapse"]:before { content: '-'; }
    .dd-placeholder, .dd-empty { margin: 5px 0; padding: 0; min-height: 45px; background: #f2fbff; border: 1px dashed #b6bcbf; box-sizing: border-box; border-radius: 8px; }
    .dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
    .dd-dragel > .dd-item .dd-handle { margin-top: 0; }
    .dd-dragel .dd-handle { box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1); }

    .menu-actions { position: absolute; right: 10px; top: 8px; z-index: 10; }
    .menu-info { font-weight: normal; font-size: 11px; color: #777; margin-left: 10px; }
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Kelola Menu</h4>
        <p class="text-muted mb-0 small">Drag and drop untuk mengatur urutan dan hirarki menu</p>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.menus.reorder') }}" method="POST" id="reorderForm">
            @csrf
            <input type="hidden" name="data" id="nestable-output">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save me-1"></i>Simpan Perubahan
            </button>
        </form>
        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah Menu
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="dd" id="nestable">
            <ol class="dd-list">
                @foreach($menus as $menu)
                    <li class="dd-item" data-id="{{ $menu->id }}">
                        <div class="dd-handle d-flex align-items-center">
                            <i class="bi {{ $menu->icon ?? 'bi-circle' }} me-3 text-primary"></i>
                            <span>{{ $menu->name }}</span>
                            <span class="menu-info d-none d-md-inline">
                                {{ $menu->route_name ? '('.$menu->route_name.')' : '' }} 
                                @if($menu->permission_name) • <i class="bi bi-shield-lock me-1"></i>{{ $menu->permission_name }} @endif
                            </span>

                            <div class="menu-actions">
                                @forelse($menu->roles as $role)
                                    <span class="badge bg-light text-dark border me-1 d-none d-lg-inline-block" style="font-size: 10px;">{{ $role->name }}</span>
                                @empty
                                    <span class="badge bg-light text-muted border me-1 d-none d-lg-inline-block" style="font-size: 10px;">Semua</span>
                                @endforelse
                                <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-xs btn-outline-warning py-0 px-1 ms-2"><i class="bi bi-pencil small"></i></a>
                                <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus menu ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-1"><i class="bi bi-trash small"></i></button>
                                </form>
                            </div>
                        </div>
                        @if($menu->activeChildren && $menu->activeChildren->isNotEmpty())
                            <ol class="dd-list">
                                @foreach($menu->activeChildren as $child)
                                    <li class="dd-item" data-id="{{ $child->id }}">
                                        <div class="dd-handle d-flex align-items-center">
                                            <i class="bi {{ $child->icon ?? 'bi-circle' }} me-3 text-secondary"></i>
                                            <span>{{ $child->name }}</span>
                                            <span class="menu-info">
                                                {{ $child->route_name ? '('.$child->route_name.')' : '' }}
                                            </span>
                                            <div class="menu-actions">
                                                <a href="{{ route('admin.menus.edit', $child) }}" class="btn btn-xs btn-outline-warning py-0 px-1"><i class="bi bi-pencil small"></i></a>
                                                <form action="{{ route('admin.menus.destroy', $child) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus menu ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-1"><i class="bi bi-trash small"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                        {{-- Deep nesting support --}}
                                        @if(method_exists($child, 'activeChildren') && $child->activeChildren && $child->activeChildren->isNotEmpty())
                                            <ol class="dd-list">
                                                @foreach($child->activeChildren as $grandchild)
                                                    <li class="dd-item" data-id="{{ $grandchild->id }}">
                                                        {{-- Recursive call would be better but let's do 3 levels for now as per UKS needs --}}
                                                        <div class="dd-handle">
                                                            <i class="bi {{ $grandchild->icon ?? 'bi-circle' }} me-2"></i> {{ $grandchild->name }}
                                                            <div class="menu-actions">
                                                                <a href="{{ route('admin.menus.edit', $grandchild) }}" class="btn btn-xs"><i class="bi bi-pencil small"></i></a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ol>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Nestable/2012-10-15/jquery.nestable.min.js"></script>
<script>
    $(document).ready(function() {
        var updateOutput = function(e) {
            var list = e.length ? e : $(e.target),
                output = list.data('output');
            if (window.JSON) {
                output.val(window.JSON.stringify(list.nestable('serialize')));
            } else {
                output.val('JSON browser support required for this demo.');
            }
        };

        $('#nestable').nestable({
            group: 1,
            maxDepth: 3
        }).on('change', updateOutput);

        updateOutput($('#nestable').data('output', $('#nestable-output')));

        setTimeout(() => {
            $('.dd a').on('mousedown', function (event) { event.preventDefault(); return false; });
        }, 100)
    });
</script>
@endpush
