@php
    $items = $items ?? [];
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                    @if(!empty($subtitle))
                        <div class="small text-muted">{{ $subtitle }}</div>
                    @endif
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                @if(count($items))
                    <div class="list-group list-group-flush">
                        @foreach($items as $item)
                            <div class="list-group-item py-3">
                                <div class="d-flex flex-wrap justify-content-between gap-3">
                                    <div class="me-auto">
                                        <div class="fw-semibold">{{ $item['title'] ?? '-' }}</div>
                                        @if(!empty($item['meta']))
                                            <div class="small text-muted">{{ $item['meta'] }}</div>
                                        @endif
                                        @if(!empty($item['subtitle']))
                                            <div class="small mt-1">{{ $item['subtitle'] }}</div>
                                        @endif
                                    </div>
                                    <div class="text-md-end small">
                                        @if(!empty($item['count']))
                                            <div class="fw-semibold">{{ $item['count'] }}x</div>
                                        @endif
                                        @if(!empty($item['date']) || !empty($item['time']))
                                            <div class="text-muted">
                                                {{ $item['date'] ?? '' }}{{ !empty($item['date']) && !empty($item['time']) ? ' ' : '' }}{{ $item['time'] ?? '' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if(!empty($item['link']) || !empty($item['secondary_link']))
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @if(!empty($item['link']))
                                            <a href="{{ $item['link'] }}" class="btn btn-sm btn-outline-primary">{{ $item['link_label'] ?? 'Buka' }}</a>
                                        @endif
                                        @if(!empty($item['secondary_link']))
                                            <a href="{{ $item['secondary_link'] }}" class="btn btn-sm btn-outline-secondary">{{ $item['secondary_link_label'] ?? 'Lihat' }}</a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 text-center text-muted small">Belum ada detail data untuk periode ini.</div>
                @endif
            </div>
        </div>
    </div>
</div>
