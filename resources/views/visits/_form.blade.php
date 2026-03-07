{{-- Shared form fields for create/edit --}}
@if($errors->any())
    <div class="alert alert-danger py-2">
        <ul class="mb-0 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

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
               value="{{ old('visit_time', isset($visit) ? $visit->visit_time : date('H:i')) }}" required>
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

    <div class="col-md-8">
        <label class="form-label small fw-semibold">Diagnosa / Penyakit <span class="text-danger">*</span></label>
        <select name="disease_id" id="disease_id" class="form-select select2-ajax" data-url="{{ route('admin.master.diseases.search') }}" required>
            @if(isset($visit) && $visit->disease)
                <option value="{{ $visit->disease_id }}" selected>{{ $visit->disease->name }}</option>
            @endif
        </select>
        @error('disease_id') <div class="invalid-feedback text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 d-flex align-items-end mb-1">
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="is_acc_pulang" id="is_acc_pulang" value="1" {{ old('is_acc_pulang', $visit->is_acc_pulang ?? false) ? 'checked' : '' }}>
            <label class="form-check-label fw-bold small" for="is_acc_pulang">Acc Pulang</label>
        </div>
    </div>

    <div class="col-md-8">
        <label class="form-label small fw-semibold">Obat</label>
        <select name="medication_id" id="medication_id" class="form-select select2-ajax" data-url="{{ route('admin.master.medications.search') }}">
            @if(isset($visit) && $visit->medication)
                <option value="{{ $visit->medication_id }}" selected>{{ $visit->medication->name }}</option>
            @endif
        </select>
        @error('medication_id') <div class="invalid-feedback text-danger small">{{ $message }}</div> @enderror
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
               value="{{ old('officer_name', $visit->officer_name ?? auth()->user()->name) }}" required>
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
            $(this).select2({
                theme: 'bootstrap-5',
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                },
                placeholder: 'Cari data...',
                minimumInputLength: 1
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

        // Handle disabled gender select before form submit
        $('form').on('submit', function() {
            $('#genderSelect').prop('disabled', false);
        });
    });
</script>
@endpush
