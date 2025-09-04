<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #e0eafc, #cfdef3);
        }

        .profile-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-picture {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border: 4px solid #0d6efd;
            box-shadow: 0 2px 10px rgba(13, 110, 253, 0.3);
        }

        .form-control, .form-check-input {
            border-radius: 0.5rem;
        }

        .btn-primary {
            border-radius: 0.5rem;
            font-weight: 500;
        }

        textarea {
            resize: none;
        }

        .form-label {
            font-weight: 600;
        }
    </style>
</head>
<body>

@include('partials.navbar')

<div class="container py-5">
    @if(session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="profile-card p-4">
        <h3 class="text-primary mb-4 d-flex align-items-center">
            <i class="bi bi-person-circle me-2 fs-3"></i> {{ $userData ? 'Update Profile' : 'Create Profile' }}
        </h3>

        <div class="row">
            <!-- Sidebar: Profile Picture & User Info -->
            <div class="col-lg-3 text-center mb-4">
                <div>
                    @if($userData && $userData->image)
                        <img src="{{ asset('storage/' . $userData->image) }}" alt="User Image" class="rounded-circle profile-picture mb-3">
                    @else
                        <img src="https://via.placeholder.com/150" alt="Default Avatar" class="rounded-circle profile-picture mb-3">
                    @endif
                    <p class="mb-1"><strong>Name:</strong> {{ Auth::user()->name }}</p>
                    <p class="mb-0"><strong>Email:</strong> {{ Auth::user()->email }}</p>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="col-lg-9">
                <!-- inside <form> element -->
<form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="number" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                   placeholder="Enter your phone" pattern="[0-9]{10}" maxlength="10" required
                   value="{{ old('phone', $userData->phone ?? '') }}">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="bio" class="form-label">Bio</label>
            <input type="text" name="bio" id="bio" class="form-control @error('bio') is-invalid @enderror"
                   placeholder="Tell something about yourself" required
                   value="{{ old('bio', $userData->bio ?? '') }}">
            @error('bio')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror"
                  rows="2" placeholder="Enter your address" required>{{ old('address', $userData->address ?? '') }}</textarea>
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label d-block">Gender</label>
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input @error('gender') is-invalid @enderror" type="radio" name="gender" id="male" value="Male"
                           {{ old('gender', $userData->gender ?? '') === 'Male' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="male">Male</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input @error('gender') is-invalid @enderror" type="radio" name="gender" id="female" value="Female"
                           {{ old('gender', $userData->gender ?? '') === 'Female' ? 'checked' : '' }}>
                    <label class="form-check-label" for="female">Female</label>
                </div>
            </div>
            @error('gender')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-8">
            <label for="image" class="form-label">Update Profile Picture</label>
            <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror"
                   accept="image/*">
            @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary px-4 py-2">
            <i class="bi bi-save2 me-1"></i> {{ $userData ? 'Update Info' : 'Submit Info' }}
        </button>
    </div>
</form>

            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
});

$(document).ready(function () {

    // On blur of each input field
    $('#phone, #bio, #address, #image').on('blur', function () {
        validateField($(this));
    });

    // For gender (radio button), trigger blur manually
    $('input[name="gender"]').on('change', function () {
        validateField($('input[name="gender"]'));
    });

    function validateField(input) {
        let formData = new FormData();
        let name = input.attr('name');
        let value = input.val();

        // Append only the changed field (for performance)
        if (input.attr('type') === 'file') {
            formData.append(name, input[0].files[0]);
        } else {
            formData.append(name, value);
        }

        // Handle radio separately
        if (name === 'gender') {
            let selected = $('input[name="gender"]:checked').val();
            formData.append('gender', selected);
        }

        $.ajax({
            url: "{{ route('user.validate') }}",
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function () {
                clearError(input);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    if (errors[name]) {
                        showError(input, errors[name][0]);
                    } else {
                        clearError(input);
                    }
                }
            }
        });
    }

    function showError(input, message) {
        clearError(input);
        input.addClass('is-invalid');
        input.after('<div class="invalid-feedback d-block">' + message + '</div>');
    }

    function clearError(input) {
        input.removeClass('is-invalid');
        input.next('.invalid-feedback').remove();
    }
});
</script>

</body>
</html>
