{{-- Shared form fields for create/edit --}}
@php
    $appSettings = app(\App\Services\SettingsService::class)->all();
    $defaultVisitTime = ($appSettings['visit_default_time_mode'] ?? 'now') === 'blank' ? '' : date('H:i');
    $defaultOfficerName = match ($appSettings['visit_officer_prefill_mode'] ?? 'current_user') {
        'custom' => $appSettings['visit_officer_default_name'] ?? '',
        'blank' => '',
        default => auth()->user()->name,
    };
    $showStandardExam = old('visit_form_show_standard_exam', $appSettings['visit_form_show_standard_exam'] ?? '1');
@endphp

@if($errors->any())
    <div class="alert alert-danger py-2">
        <ul class="mb-0 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="alert alert-info border-0 shadow-sm d-flex flex-wrap align-items-start justify-content-between gap-3" id="visitOfflineHint">
    <div>
        <div class="fw-bold mb-1"><i class="bi bi-cloud-arrow-down me-2"></i>Visit bisa disimpan saat offline</div>
        <div class="small mb-1">Kalau koneksi putus, form ini akan menyimpan kunjungan ke perangkat ini dulu lalu otomatis kirim ke server saat online lagi.</div>
        <div class="small text-muted">Batasan: pencarian siswa/pegawai saat offline hanya bisa memakai data yang pernah tercache sebelumnya di perangkat ini.</div>
    </div>
    <div class="small text-muted" id="visitOfflinePendingInfo">Mengecek antrean lokal...</div>
</div>

<div class="row g-3">
    <div class="col-md-4 col-6">
        <label class="form-label small fw-semibold">Tanggal Kunjungan <span class="text-danger">*</span></label>
        <input type="date" name="visit_date" class="form-control @error('visit_date') is-invalid @enderror"
               value="{{ old('visit_date', isset($visit) ? $visit->visit_date->format('Y-m-d') : date('Y-m-d')) }}" required>
        @error('visit_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 col-6">
        <label class="form-label small fw-semibold">Waktu <span class="text-danger">*</span></label>
        <input type="time" name="visit_time" class="form-control @error('visit_time') is-invalid @enderror"
               value="{{ old('visit_time', isset($visit) ? $visit->visit_time : $defaultVisitTime) }}" required>
        @error('visit_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
        <select name="patient_category" class="form-select @error('patient_category') is-invalid @enderror" required id="categorySelect">
            <option value="">Pilih</option>
            @foreach(['SMA' => 'Siswa (SMA)', 'GURU' => 'Guru', 'KARYAWAN' => 'Karyawan', 'UMUM' => 'Umum'] as $val => $label)
                <option value="{{ $val }}" {{ old('patient_category', $visit->patient_category ?? '') == $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('patient_category') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Patient Selection Wrapper --}}
    <div class="col-12" id="patientSelectionWrapper">
        <div id="wrapper_SMA" class="category-wrapper {{ old('patient_category', $visit->patient_category ?? '') == 'SMA' ? '' : 'd-none' }}">
            <label class="form-label small fw-semibold">Cari Siswa (NIS / Nama) <span class="text-danger">*</span></label>
            <select name="student_id" id="student_id" class="form-select select2-ajax" data-url="{{ route('admin.master.students.search') }}">
                @if(isset($visit) && $visit->student)
                    <option value="{{ $visit->student_id }}" selected>{{ $visit->student->nis }} - {{ $visit->student->name }}</option>
                @endif
            </select>
        </div>

        <div id="wrapper_staff" class="category-wrapper {{ in_array(old('patient_category', $visit->patient_category ?? ''), ['GURU', 'KARYAWAN']) ? '' : 'd-none' }}">
            <label class="form-label small fw-semibold">Cari Pegawai (NIP / Nama) <span class="text-danger">*</span></label>
            <select name="employee_id" id="employee_id" class="form-select select2-ajax" data-url="{{ route('admin.master.employees.search') }}">
                @if(isset($visit) && $visit->employee)
                    <option value="{{ $visit->employee_id }}" selected>{{ $visit->employee->nip }} - {{ $visit->employee->name }}</option>
                @endif
            </select>
        </div>

        <div id="wrapper_UMUM" class="category-wrapper {{ old('patient_category', $visit->patient_category ?? '') == 'UMUM' ? '' : 'd-none' }}">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nama Pasien (Lengkap) <span class="text-danger">*</span></label>
                    <input type="text" name="external_patient_name" id="external_patient_name" class="form-control" 
                           value="{{ old('external_patient_name', $visit->external_patient_name ?? '') }}" placeholder="Masukkan nama pasien">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Info Tambahan (Ortu, Outsource, dll) <span class="text-danger">*</span></label>
                    <input type="text" name="additional_info" id="additional_info" class="form-control" 
                           value="{{ old('additional_info', $visit->additional_info ?? '') }}" placeholder="Misal: Orang tua siswa X">
                </div>
            </div>
        </div>
        
        <input type="hidden" name="patient_name" id="hidden_patient_name" value="{{ old('patient_name', $visit->patient_name ?? '') }}">
    </div>

    <div class="col-md-6 col-6">
        <label class="form-label small fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
        <select name="gender" id="genderSelect" class="form-select @error('gender') is-invalid @enderror" required>
            <option value="">Pilih</option>
            <option value="L" {{ old('gender', $visit->gender ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
            <option value="P" {{ old('gender', $visit->gender ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
        </select>
        @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 col-6" id="classWrapper">
        <label class="form-label small fw-semibold">Kelas / Bagian</label>
        <input type="text" name="class_or_department" id="classInput" class="form-control @error('class_or_department') is-invalid @enderror"
               value="{{ old('class_or_department', $visit->class_or_department ?? '') }}" placeholder="Akan terisi otomatis">
        @error('class_or_department') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12 {{ $showStandardExam ? '' : 'd-none' }}" id="standardExamWrapper">
        <div class="card border-0 shadow-sm bg-light bg-opacity-50">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Pemeriksaan Standar</h6>
                        <p class="text-muted small mb-0">Isi hasil pengukuran dasar dan tanda-tanda vital pengunjung bila tersedia.</p>
                    </div>
                    <span class="badge text-bg-light border">Opsional</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <label class="form-label small fw-semibold">Tinggi Badan</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="300" name="height_cm" class="form-control @error('height_cm') is-invalid @enderror"
                                   value="{{ old('height_cm', $visit->height_cm ?? '') }}" placeholder="Contoh: 165">
                            <span class="input-group-text">cm</span>
                        </div>
                        @error('height_cm') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 col-6">
                        <label class="form-label small fw-semibold">Berat Badan</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="500" name="weight_kg" class="form-control @error('weight_kg') is-invalid @enderror"
                                   value="{{ old('weight_kg', $visit->weight_kg ?? '') }}" placeholder="Contoh: 52.5">
                            <span class="input-group-text">kg</span>
                        </div>
                        @error('weight_kg') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 col-6">
                        <label class="form-label small fw-semibold">Tekanan Darah</label>
                        <input type="text" name="blood_pressure" class="form-control @error('blood_pressure') is-invalid @enderror"
                               value="{{ old('blood_pressure', $visit->blood_pressure ?? '') }}" placeholder="Contoh: 110/70">
                        @error('blood_pressure') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 col-6">
                        <label class="form-label small fw-semibold">Temperature</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="30" max="45" name="temperature_c" class="form-control @error('temperature_c') is-invalid @enderror"
                                   value="{{ old('temperature_c', $visit->temperature_c ?? '') }}" placeholder="Contoh: 36.7">
                            <span class="input-group-text">&deg;C</span>
                        </div>
                        @error('temperature_c') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 col-6">
                        <label class="form-label small fw-semibold">Heart Rate</label>
                        <div class="input-group">
                            <input type="number" min="0" max="300" name="heart_rate" class="form-control @error('heart_rate') is-invalid @enderror"
                                   value="{{ old('heart_rate', $visit->heart_rate ?? '') }}" placeholder="Contoh: 88">
                            <span class="input-group-text">bpm</span>
                        </div>
                        @error('heart_rate') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 col-6">
                        <label class="form-label small fw-semibold">RR</label>
                        <div class="input-group">
                            <input type="number" min="0" max="120" name="respiratory_rate" class="form-control @error('respiratory_rate') is-invalid @enderror"
                                   value="{{ old('respiratory_rate', $visit->respiratory_rate ?? '') }}" placeholder="Contoh: 20">
                            <span class="input-group-text">x/menit</span>
                        </div>
                        @error('respiratory_rate') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $oldDiseaseNames = collect(old('disease_names', isset($visit) ? $visit->diseases->pluck('name')->all() : []))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->values();
        $oldMedicationNames = collect(old('medication_names', isset($visit) ? $visit->medications->pluck('name')->all() : []))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->values();
        $diseaseSuggestions = \App\Models\Disease::query()->orderBy('name')->pluck('name')->all();
        $medicationSuggestions = \App\Models\Medication::query()->orderBy('name')->pluck('name')->all();
    @endphp

    <div class="col-md-8">
        <label class="form-label small fw-semibold">Diagnosa / Penyakit <span class="text-danger">*</span></label>
        <div class="tag-suggestion-field" data-name="disease_names" data-required="true" data-suggestions='@json($diseaseSuggestions)'>
            <div class="form-control tag-input-shell @error('disease_names') is-invalid @enderror">
                <div class="tag-input-selected"></div>
                <input type="text" class="tag-input-control" placeholder="Ketik lalu Enter. Saran akan muncul dari master data.">
            </div>
            <div class="tag-suggestion-dropdown d-none"></div>
            <div class="tag-hidden-inputs">
                @foreach($oldDiseaseNames as $name)
                    <input type="hidden" name="disease_names[]" value="{{ $name }}">
                @endforeach
            </div>
        </div>
        <div class="form-text">Boleh pilih dari saran atau ketik penyakit baru. Tekan Enter untuk menambahkan.</div>
        @error('disease_names') <div class="invalid-feedback text-danger small d-block">{{ $message }}</div> @enderror
        @error('disease_names.*') <div class="invalid-feedback text-danger small d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 d-flex align-items-end mb-1">
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="is_acc_pulang" id="is_acc_pulang" value="1" {{ old('is_acc_pulang', $visit->is_acc_pulang ?? false) ? 'checked' : '' }}>
            <label class="form-check-label fw-bold small" for="is_acc_pulang">Acc Pulang</label>
        </div>
    </div>

    <div class="col-md-8">
        <label class="form-label small fw-semibold">Obat</label>
        <div class="tag-suggestion-field" data-name="medication_names" data-suggestions='@json($medicationSuggestions)'>
            <div class="form-control tag-input-shell @error('medication_names') is-invalid @enderror">
                <div class="tag-input-selected"></div>
                <input type="text" class="tag-input-control" placeholder="Ketik lalu Enter. Bisa isi obat baru jika belum ada.">
            </div>
            <div class="tag-suggestion-dropdown d-none"></div>
            <div class="tag-hidden-inputs">
                @foreach($oldMedicationNames as $name)
                    <input type="hidden" name="medication_names[]" value="{{ $name }}">
                @endforeach
            </div>
        </div>
        <div class="form-text">Boleh kosong. Obat baru akan otomatis ditambahkan ke master.</div>
        @error('medication_names') <div class="invalid-feedback text-danger small d-block">{{ $message }}</div> @enderror
        @error('medication_names.*') <div class="invalid-feedback text-danger small d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-12 {{ old('is_acc_pulang', $visit->is_acc_pulang ?? false) ? '' : 'd-none' }}" id="reasonWrapper">
        <label class="form-label small fw-semibold">Alasan Acc Pulang <span class="text-danger">*</span></label>
        <input type="text" name="acc_pulang_reason" class="form-control @error('acc_pulang_reason') is-invalid @enderror" 
               value="{{ old('acc_pulang_reason', $visit->acc_pulang_reason ?? '') }}" placeholder="Misal: Demam tinggi, butuh istirahat">
        @error('acc_pulang_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label small fw-semibold">Keluhan <span class="text-danger">*</span></label>
        <textarea name="complaint" rows="3" class="form-control @error('complaint') is-invalid @enderror"
                  placeholder="Tuliskan keluhan pasien..." required>{{ old('complaint', $visit->complaint ?? '') }}</textarea>
        @error('complaint') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label small fw-semibold">Terapi / Tindakan</label>
        <textarea name="therapy" rows="2" class="form-control @error('therapy') is-invalid @enderror"
                  placeholder="Tuliskan terapi/tindakan yang diberikan...">{{ old('therapy', $visit->therapy ?? '') }}</textarea>
        @error('therapy') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label small fw-semibold">Nama Petugas <span class="text-danger">*</span></label>
        <input type="text" name="officer_name" class="form-control @error('officer_name') is-invalid @enderror"
               value="{{ old('officer_name', $visit->officer_name ?? $defaultOfficerName) }}" required>
        @error('officer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label small fw-semibold">Catatan</label>
        <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
               value="{{ old('notes', $visit->notes ?? '') }}" placeholder="Catatan tambahan (opsional)">
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 AJAX
        $('.select2-ajax').each(function() {
            var url = $(this).data('url');
            var isMultiple = $(this).prop('multiple');
            var cacheType = this.id === 'student_id' ? 'student' : 'employee';
            $(this).select2({
                theme: 'bootstrap-5',
                ajax: {
                    delay: 250,
                    transport: function (params, success, failure) {
                        var term = params.data && params.data.q ? params.data.q : '';

                        if (window.LabsHealthOffline?.isOffline()) {
                            window.LabsHealthOffline.searchPatientCache(cacheType, term)
                                .then(success)
                                .catch(failure);
                            return;
                        }

                        return $.ajax({
                            url: url,
                            dataType: 'json',
                            data: params.data
                        }).then(function (data) {
                            window.LabsHealthOffline?.cachePatientResults(cacheType, data);
                            success(data);
                        }).catch(failure);
                    },
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                },
                placeholder: 'Cari data...',
                minimumInputLength: 1,
                closeOnSelect: !isMultiple
            });
        });

        // Toggle Category Wrappers
        function updateFormUI() {
            var val = $('#categorySelect').val();
            $('.category-wrapper').addClass('d-none');
            $('#classWrapper').removeClass('d-none');
            
            // Reset readonly
            $('#genderSelect').prop('disabled', false).removeClass('bg-light');
            $('#classInput').prop('readonly', false).removeClass('bg-light');

            if (val === 'SMA') {
                $('#wrapper_SMA').removeClass('d-none');
                $('#genderSelect').prop('disabled', true).addClass('bg-light');
                $('#classInput').prop('readonly', true).addClass('bg-light');
            } else if (val === 'GURU' || val === 'KARYAWAN') {
                $('#wrapper_staff').removeClass('d-none');
                $('#classWrapper').addClass('d-none');
            } else if (val === 'UMUM') {
                $('#wrapper_UMUM').removeClass('d-none');
            }
        }

        $('#categorySelect').on('change', updateFormUI);
        updateFormUI(); // Run on load

        // Set Hidden Name & Auto-fill from Student Search
        $('#student_id').on('select2:select', function (e) {
            var data = e.params.data;
            $('#hidden_patient_name').val(data.text.split(' - ')[1].split(' (')[0]);
            if (data.class) $('#classInput').val(data.class);
            if (data.gender) $('#genderSelect').val(data.gender);
        });

        $('#employee_id').on('select2:select', function (e) {
            var data = e.params.data;
            $('#hidden_patient_name').val(data.text.split(' - ')[1].split(' (')[0]);
            if (data.gender) $('#genderSelect').val(data.gender);
        });

        $('#external_patient_name').on('input', function() {
            $('#hidden_patient_name').val($(this).val());
        });

        // Acc Pulang logical toggle
        $('#is_acc_pulang').on('change', function() {
            if ($(this).is(':checked')) {
                $('#reasonWrapper').removeClass('d-none');
            } else {
                $('#reasonWrapper').addClass('d-none');
            }
        });

        function flushPendingTagInput($form) {
            $('#genderSelect').prop('disabled', false);

            $form.find('.tag-suggestion-field').each(function () {
                const $field = $(this);
                const $input = $field.find('.tag-input-control');
                const inputName = $field.data('name');
                const value = ($input.val() || '').trim().replace(/\s+/g, ' ');

                if (!value) {
                    return;
                }

                const exists = $field.find('.tag-hidden-inputs input').filter(function () {
                    return (($(this).val() || '').trim().replace(/\s+/g, ' ')).toLowerCase() === value.toLowerCase();
                }).length > 0;

                if (!exists) {
                    $('<input>', {
                        type: 'hidden',
                        name: inputName + '[]',
                        value: value
                    }).appendTo($field.find('.tag-hidden-inputs'));
                }
            });
        }

        function formDataToPayload(form) {
            const formData = new FormData(form);
            const payload = {};

            formData.forEach(function (value, key) {
                if (key.endsWith('[]')) {
                    const normalizedKey = key.slice(0, -2);
                    if (!Array.isArray(payload[normalizedKey])) {
                        payload[normalizedKey] = [];
                    }
                    payload[normalizedKey].push(value);
                    return;
                }

                payload[key] = value;
            });

            payload.is_acc_pulang = payload.is_acc_pulang === '1' ? '1' : '0';
            return payload;
        }

        function validateOfflineVisitPayload(payload) {
            const errors = [];
            const category = (payload.patient_category || '').trim();

            if (category === 'SMA' && !payload.student_id) {
                errors.push('Pilih siswa terlebih dahulu sebelum menyimpan offline.');
            }

            if ((category === 'GURU' || category === 'KARYAWAN') && !payload.employee_id) {
                errors.push('Pilih pegawai terlebih dahulu sebelum menyimpan offline.');
            }

            if (category === 'UMUM' && !String(payload.external_patient_name || '').trim()) {
                errors.push('Nama pasien umum wajib diisi.');
            }

            if (!Array.isArray(payload.disease_names) || payload.disease_names.length === 0) {
                errors.push('Diagnosa/Penyakit minimal harus diisi 1.');
            }

            if (payload.is_acc_pulang === '1' && !String(payload.acc_pulang_reason || '').trim()) {
                errors.push('Alasan Acc Pulang wajib diisi jika statusnya aktif.');
            }

            return errors;
        }

        async function updateOfflinePendingInfo() {
            const pendingInfo = document.getElementById('visitOfflinePendingInfo');
            if (!pendingInfo || !window.LabsHealthOffline) return;

            const count = await window.LabsHealthOffline.countPendingVisits();
            pendingInfo.textContent = count > 0
                ? count + ' kunjungan lokal menunggu sinkronisasi.'
                : 'Belum ada antrean lokal.';
        }

        // Handle disabled gender select before form submit
        $('form').on('submit', function(event) {
            const form = this;
            const $form = $(form);
            flushPendingTagInput($form);

            if (!form.classList.contains('js-offline-visit-form')) {
                return;
            }

            if (!window.LabsHealthOffline?.isOffline()) {
                return;
            }

            event.preventDefault();

            if (!form.reportValidity()) {
                return;
            }

            const payload = formDataToPayload(form);
            const validationErrors = validateOfflineVisitPayload(payload);

            if (validationErrors.length > 0) {
                if (window.showAsyncAlert) {
                    window.showAsyncAlert('danger', validationErrors[0]);
                }
                return;
            }

            window.LabsHealthOffline.queueVisit(payload).then(async function () {
                if (window.showAsyncAlert) {
                    window.showAsyncAlert('success', form.dataset.offlineSuccessMessage || 'Kunjungan disimpan lokal.');
                }

                form.reset();
                $('#student_id, #employee_id').val(null).trigger('change');
                $('.tag-hidden-inputs').empty();
                $('.tag-input-selected').empty();
                $('.tag-input-control').val('');
                $('#hidden_patient_name').val('');
                $('#reasonWrapper').addClass('d-none');
                updateFormUI();
                await updateOfflinePendingInfo();
                window.LabsHealthOffline.renderStatusBanner();
            }).catch(function () {
                if (window.showAsyncAlert) {
                    window.showAsyncAlert('danger', 'Kunjungan gagal disimpan lokal di perangkat ini.');
                }
            });
        });

        function normalizeTagValue(value) {
            return (value || '').trim().replace(/\s+/g, ' ');
        }

        function initializeTagSuggestionField($field) {
            const suggestions = ($field.data('suggestions') || []).map(normalizeTagValue).filter(Boolean);
            const inputName = $field.data('name');
            const required = Boolean($field.data('required'));
            const $selected = $field.find('.tag-input-selected');
            const $input = $field.find('.tag-input-control');
            const $dropdown = $field.find('.tag-suggestion-dropdown');
            const $hidden = $field.find('.tag-hidden-inputs');

            function getValues() {
                return $hidden.find('input').map(function () {
                    return normalizeTagValue($(this).val());
                }).get().filter(Boolean);
            }

            function syncTags() {
                const values = getValues();
                $selected.empty();

                values.forEach(function (value) {
                    const $tag = $('<span class="badge rounded-pill text-bg-light border d-inline-flex align-items-center gap-2 px-3 py-2 me-2 mb-2"></span>');
                    $tag.append($('<span></span>').text(value));
                    const $remove = $('<button type="button" class="btn btn-sm p-0 border-0 bg-transparent text-danger lh-1">&times;</button>');
                    $remove.on('click', function () {
                        $hidden.find('input').filter(function () {
                            return normalizeTagValue($(this).val()).toLowerCase() === value.toLowerCase();
                        }).first().remove();
                        syncTags();
                    });
                    $tag.append($remove);
                    $selected.append($tag);
                });

                if (required) {
                    $field.toggleClass('tag-field-empty', values.length === 0);
                }
            }

            function addValue(rawValue) {
                const value = normalizeTagValue(rawValue);
                if (!value) {
                    return;
                }

                const exists = getValues().some(function (item) {
                    return item.toLowerCase() === value.toLowerCase();
                });

                if (exists) {
                    $input.val('');
                    renderSuggestions();
                    return;
                }

                $('<input>', {
                    type: 'hidden',
                    name: inputName + '[]',
                    value: value
                }).appendTo($hidden);

                $input.val('');
                renderSuggestions();
                syncTags();
            }

            function renderSuggestions() {
                const keyword = normalizeTagValue($input.val()).toLowerCase();
                const selectedValues = getValues().map(function (item) {
                    return item.toLowerCase();
                });

                const filtered = suggestions.filter(function (item) {
                    const lowered = item.toLowerCase();
                    if (selectedValues.includes(lowered)) {
                        return false;
                    }

                    return keyword === '' || lowered.includes(keyword);
                }).slice(0, 8);

                $dropdown.empty();

                if (!filtered.length || keyword === '') {
                    $dropdown.addClass('d-none');
                    return;
                }

                filtered.forEach(function (item) {
                    const $button = $('<button type="button" class="dropdown-item small py-2"></button>').text(item);
                    $button.on('click', function () {
                        addValue(item);
                        $dropdown.addClass('d-none');
                        $input.trigger('focus');
                    });
                    $dropdown.append($button);
                });

                $dropdown.removeClass('d-none');
            }

            $input.on('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ',') {
                    event.preventDefault();
                    addValue($input.val());
                    return;
                }

                if (event.key === 'Backspace' && normalizeTagValue($input.val()) === '') {
                    const values = getValues();
                    if (values.length > 0) {
                        const lastValue = values[values.length - 1];
                        $hidden.find('input').filter(function () {
                            return normalizeTagValue($(this).val()).toLowerCase() === lastValue.toLowerCase();
                        }).last().remove();
                        syncTags();
                    }
                }
            });

            $input.on('input focus', renderSuggestions);
            $input.on('blur', function () {
                setTimeout(function () {
                    $dropdown.addClass('d-none');
                }, 150);
            });

            syncTags();
        }

        $('.tag-suggestion-field').each(function () {
            initializeTagSuggestionField($(this));
        });

        updateOfflinePendingInfo();

        document.addEventListener('labshealth:offline-queue-changed', updateOfflinePendingInfo);
        document.addEventListener('labshealth:offline-sync-finished', updateOfflinePendingInfo);
    });
</script>
@endpush

@push('styles')
<style>
    .tag-input-shell {
        min-height: 48px;
        display: flex;
        align-items: flex-start;
        gap: 4px;
        flex-wrap: wrap;
        position: relative;
    }

    .tag-input-selected {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
    }

    .tag-input-control {
        border: 0;
        outline: none;
        flex: 1 1 180px;
        min-width: 180px;
        padding: 6px 0;
        background: transparent;
    }

    .tag-suggestion-field {
        position: relative;
    }

    .tag-suggestion-dropdown {
        position: absolute;
        inset-inline: 0;
        top: calc(100% + 4px);
        z-index: 20;
        background: #fff;
        border: 1px solid var(--bs-border-color);
        border-radius: .75rem;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .08);
        overflow: hidden;
    }
</style>
@endpush
