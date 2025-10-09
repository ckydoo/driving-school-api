{{-- resources/views/admin/invoices/create.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-invoice-dollar"></i> Create New Invoice
            </h1>
            <p class="text-muted mb-0">Generate an invoice for a student's lessons</p>
        </div>
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Invoices
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Invoice Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.invoices.store') }}" method="POST" id="invoiceForm">
                        @csrf

                        <!-- Student Selection -->
                        <div class="form-group">
                            <label for="student" class="font-weight-bold">
                                Student <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('student') is-invalid @enderror" 
                                    id="student" 
                                    name="student" 
                                    required
                                    onchange="loadStudentInfo()">
                                <option value="">Select a student...</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" 
                                            data-email="{{ $student->email }}"
                                            data-phone="{{ $student->phone ?? 'N/A' }}"
                                            {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} ({{ $student->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Select the student for this invoice
                            </small>
                        </div>

                        <!-- Student Info Display -->
                        <div id="studentInfo" class="alert alert-info" style="display: none;">
                            <strong>Student Information:</strong><br>
                            <span id="studentEmail"></span><br>
                            <span id="studentPhone"></span>
                        </div>

                        <!-- Course Selection -->
                        <div class="form-group">
                            <label for="course_id" class="font-weight-bold">
                                Course <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('course_id') is-invalid @enderror" 
                                    id="course_id" 
                                    name="course_id" 
                                    required
                                    onchange="loadCourseInfo()">
                                <option value="">Select a course...</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}"
                                            data-price="{{ $course->price }}"
                                            data-description="{{ $course->description }}"
                                            {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }} - ${{ number_format($course->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Course Info Display -->
                        <div id="courseInfo" class="alert alert-secondary" style="display: none;">
                            <strong>Course Details:</strong><br>
                            <span id="courseDescription"></span><br>
                            <strong>Price per lesson:</strong> $<span id="coursePrice">0.00</span>
                        </div>

                        <div class="row">
                            <!-- Number of Lessons -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number_of_lessons" class="font-weight-bold">
                                        Number of Lessons <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('number_of_lessons') is-invalid @enderror" 
                                           id="number_of_lessons" 
                                           name="number_of_lessons" 
                                           value="{{ old('number_of_lessons', 1) }}" 
                                           min="1" 
                                           required
                                           onchange="calculateTotal()">
                                    @error('number_of_lessons')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Price per Lesson -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price_per_lesson" class="font-weight-bold">
                                        Price per Lesson <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" 
                                               class="form-control @error('price_per_lesson') is-invalid @enderror" 
                                               id="price_per_lesson" 
                                               name="price_per_lesson" 
                                               value="{{ old('price_per_lesson') }}" 
                                               step="0.01" 
                                               min="0" 
                                               required
                                               onchange="calculateTotal()">
                                    </div>
                                    @error('price_per_lesson')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Date -->
                        <div class="form-group">
                            <label for="invoice_date" class="font-weight-bold">
                                Invoice Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control @error('invoice_date') is-invalid @enderror" 
                                   id="invoice_date" 
                                   name="invoice_date" 
                                   value="{{ old('invoice_date', date('Y-m-d')) }}" 
                                   required>
                            @error('invoice_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Due Date -->
                        <div class="form-group">
                            <label for="due_date" class="font-weight-bold">
                                Due Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control @error('due_date') is-invalid @enderror" 
                                   id="due_date" 
                                   name="due_date" 
                                   value="{{ old('due_date', date('Y-m-d', strtotime('+7 days'))) }}" 
                                   required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Default is 7 days from invoice date
                            </small>
                        </div>

                        <!-- Notes -->
                        <div class="form-group">
                            <label for="notes" class="font-weight-bold">Notes (Optional)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3"
                                      placeholder="Add any additional notes or comments...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status" class="font-weight-bold">Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status">
                                <option value="unpaid" {{ old('status', 'unpaid') == 'unpaid' ? 'selected' : '' }}>
                                    Unpaid
                                </option>
                                <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>
                                    Paid
                                </option>
                                <option value="partially_paid" {{ old('status') == 'partially_paid' ? 'selected' : '' }}>
                                    Partially Paid
                                </option>
                                <option value="overdue" {{ old('status') == 'overdue' ? 'selected' : '' }}>
                                    Overdue
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Invoice
                            </button>
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Invoice Preview -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Invoice Preview</h6>
                </div>
                <div class="card-body">
                    <div class="invoice-preview">
                        <div class="mb-3">
                            <small class="text-muted">INVOICE TO:</small>
                            <div id="preview-student" class="font-weight-bold">
                                Select a student
                            </div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">COURSE:</small>
                            <div id="preview-course" class="font-weight-bold">
                                Select a course
                            </div>
                        </div>

                        <hr>

                        <table class="table table-sm table-borderless">
                            <tr>
                                <td>Lessons:</td>
                                <td class="text-right">
                                    <span id="preview-lessons">0</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Price per lesson:</td>
                                <td class="text-right">
                                    $<span id="preview-price">0.00</span>
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>Total Amount:</strong></td>
                                <td class="text-right">
                                    <strong class="text-primary">
                                        $<span id="preview-total">0.00</span>
                                    </strong>
                                </td>
                            </tr>
                        </table>

                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                Invoice will be generated with these details
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-lightbulb"></i> Quick Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Select the student first
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Choose the course to auto-fill price
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Set number of lessons
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success"></i>
                            Review the preview before creating
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadStudentInfo() {
    const select = document.getElementById('student_id');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        document.getElementById('studentEmail').textContent = 'Email: ' + option.dataset.email;
        document.getElementById('studentPhone').textContent = 'Phone: ' + option.dataset.phone;
        document.getElementById('studentInfo').style.display = 'block';
        document.getElementById('preview-student').textContent = option.text;
    } else {
        document.getElementById('studentInfo').style.display = 'none';
        document.getElementById('preview-student').textContent = 'Select a student';
    }
}

function loadCourseInfo() {
    const select = document.getElementById('course_id');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        const price = parseFloat(option.dataset.price);
        document.getElementById('courseDescription').textContent = option.dataset.description;
        document.getElementById('coursePrice').textContent = price.toFixed(2);
        document.getElementById('price_per_lesson').value = price.toFixed(2);
        document.getElementById('courseInfo').style.display = 'block';
        document.getElementById('preview-course').textContent = option.text.split(' - ')[0];
        calculateTotal();
    } else {
        document.getElementById('courseInfo').style.display = 'none';
        document.getElementById('preview-course').textContent = 'Select a course';
        document.getElementById('price_per_lesson').value = '';
    }
}

function calculateTotal() {
    const lessons = parseInt(document.getElementById('number_of_lessons').value) || 0;
    const price = parseFloat(document.getElementById('price_per_lesson').value) || 0;
    const total = lessons * price;
    
    document.getElementById('preview-lessons').textContent = lessons;
    document.getElementById('preview-price').textContent = price.toFixed(2);
    document.getElementById('preview-total').textContent = total.toFixed(2);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('student_id').value) {
        loadStudentInfo();
    }
    if (document.getElementById('course_id').value) {
        loadCourseInfo();
    }
});
</script>
@endpush