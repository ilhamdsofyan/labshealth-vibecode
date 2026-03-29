@extends('layouts.app')

@section('title', 'Profil Saya')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<style>
    .profile-avatar-preview {
        width: 112px;
        height: 112px;
        object-fit: cover;
    }
    #profileCropImage {
        max-width: 100%;
        max-height: 60vh;
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Profil Saya</h4>
        <p class="text-muted mb-0 small">Kelola identitas akun, avatar, dan password Anda sendiri.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">Ringkasan Akun</div>
            <div class="card-body text-center">
                <div class="mb-3" id="profileAvatarSummaryWrapper">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle border shadow-sm mx-auto profile-avatar-preview" id="profileAvatarSummaryImage">
                    @else
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center profile-avatar-preview mx-auto" id="profileAvatarSummaryFallback" style="background: linear-gradient(135deg, var(--accent), var(--primary)); color: #111827; font-size: 2rem; font-weight: 800;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <img src="" alt="{{ $user->name }}" class="rounded-circle border shadow-sm mx-auto profile-avatar-preview d-none" id="profileAvatarSummaryImage">
                    @endif
                </div>

                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <div class="text-muted small mb-3">{{ $user->email }}</div>

                <div class="small text-muted">Status akun</div>
                <div class="fw-semibold mb-3">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</div>

                @if($user->avatar_url)
                    <form method="POST" action="{{ route('profile.avatar.remove') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Hapus Avatar</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card mb-4">
            <div class="card-header">Profil Akun</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Avatar</label>
                            <div class="text-center border rounded-3 p-3 bg-light-subtle mb-3">
                                <img
                                    src="{{ old('avatar_cropped_data', $user->avatar_url ?: '') }}"
                                    alt="Preview Avatar"
                                    class="rounded-circle border shadow-sm mx-auto profile-avatar-preview {{ old('avatar_cropped_data', $user->avatar_url) ? '' : 'd-none' }}"
                                    id="profileAvatarPreview"
                                >
                                <div
                                    class="rounded-circle d-inline-flex align-items-center justify-content-center profile-avatar-preview mx-auto {{ old('avatar_cropped_data', $user->avatar_url) ? 'd-none' : '' }}"
                                    id="profileAvatarPreviewFallback"
                                    style="background: linear-gradient(135deg, var(--accent), var(--primary)); color: #111827; font-size: 2rem; font-weight: 800;"
                                >
                                    {{ strtoupper(substr(old('name', $user->name), 0, 1)) }}
                                </div>
                            </div>
                            <input type="file" name="avatar" id="profileAvatarInput" class="form-control @error('avatar') is-invalid @enderror" accept=".png,.jpg,.jpeg,.webp">
                            <input type="hidden" name="avatar_cropped_data" id="profileAvatarCroppedData" value="{{ old('avatar_cropped_data', '') }}">
                            <div class="form-text">Maksimal 2MB. Format PNG, JPG, JPEG, atau WEBP. Setelah pilih gambar, Anda bisa crop dulu sebelum menyimpan.</div>
                            @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @error('avatar_cropped_data') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Simpan Profil</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Ganti Password</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    @if(filled($user->password))
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Password Saat Ini</label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password Baru</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Perbarui Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="profileCropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crop Avatar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="profileCropImage" src="" alt="Crop Avatar">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="applyProfileCrop">Gunakan Avatar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const avatarInput = document.getElementById('profileAvatarInput');
        const avatarPreview = document.getElementById('profileAvatarPreview');
        const avatarPreviewFallback = document.getElementById('profileAvatarPreviewFallback');
        const avatarSummaryImage = document.getElementById('profileAvatarSummaryImage');
        const avatarSummaryFallback = document.getElementById('profileAvatarSummaryFallback');
        const croppedDataInput = document.getElementById('profileAvatarCroppedData');
        const nameInput = document.querySelector('input[name="name"]');
        const cropModalEl = document.getElementById('profileCropModal');
        const cropImage = document.getElementById('profileCropImage');
        const applyCropBtn = document.getElementById('applyProfileCrop');

        if (!avatarInput || !cropModalEl || typeof Cropper === 'undefined') {
            return;
        }

        const cropModal = new bootstrap.Modal(cropModalEl);
        let cropper = null;

        function syncFallbackInitial() {
            const initial = (nameInput?.value || '{{ strtoupper(substr($user->name, 0, 1)) }}').trim().charAt(0).toUpperCase() || 'U';

            if (avatarPreviewFallback) {
                avatarPreviewFallback.textContent = initial;
            }

            if (avatarSummaryFallback) {
                avatarSummaryFallback.textContent = initial;
            }
        }

        function setPreview(dataUrl) {
            const hasImage = Boolean(dataUrl);

            if (avatarPreview) {
                avatarPreview.src = hasImage ? dataUrl : '';
                avatarPreview.classList.toggle('d-none', !hasImage);
            }

            if (avatarPreviewFallback) {
                avatarPreviewFallback.classList.toggle('d-none', hasImage);
            }

            if (avatarSummaryImage) {
                avatarSummaryImage.src = hasImage ? dataUrl : '{{ $user->avatar_url ?: '' }}';
                avatarSummaryImage.classList.toggle('d-none', !(hasImage || {{ $user->avatar_url ? 'true' : 'false' }}));
            }

            if (avatarSummaryFallback) {
                avatarSummaryFallback.classList.toggle('d-none', hasImage || {{ $user->avatar_url ? 'true' : 'false' }});
            }
        }

        function openCropper(file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                cropImage.src = event.target.result;
                cropModal.show();
            };
            reader.readAsDataURL(file);
        }

        cropModalEl.addEventListener('shown.bs.modal', function () {
            if (cropper) {
                cropper.destroy();
            }

            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                responsive: true,
                background: false,
            });
        });

        cropModalEl.addEventListener('hidden.bs.modal', function () {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        });

        avatarInput.addEventListener('change', function (event) {
            const file = event.target.files && event.target.files[0];
            if (!file) {
                return;
            }

            croppedDataInput.value = '';
            openCropper(file);
        });

        applyCropBtn.addEventListener('click', function () {
            if (!cropper) {
                return;
            }

            const canvas = cropper.getCroppedCanvas({
                width: 512,
                height: 512,
                imageSmoothingQuality: 'high',
            });

            const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
            croppedDataInput.value = dataUrl;
            avatarInput.value = '';
            setPreview(dataUrl);
            cropModal.hide();
        });

        nameInput?.addEventListener('input', syncFallbackInitial);

        syncFallbackInitial();
        setPreview(croppedDataInput.value || '{{ $user->avatar_url ?: '' }}');
    });
</script>
@endpush
