@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">{{ !empty($user) ? 'Update User' : 'Create User' }}</h4>
                            <a href="{{ route('user.list') }}" class="btn btn-sm" style="background-color: #7cb342; color: #ffffff;">User List</a>
                        </div>

                        <div class="card-body">
                            <form action="{{ !empty($user) ? route('update.user') : route('store.user') }}" method="POST"
                                class="row g-3 needs-validation" novalidate>
                                @csrf
                                @if (!empty($user))
                                    <input type="hidden" name="id" value="{{ old('id', $user->id) }}">
                                @endif

                                <div class="col-md-4">
                                    <label for="name" class="form-label">Full Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ old('name', $user->name ?? '') }}" required>
                                    <div class="valid-feedback">Looks good!</div>
                                    <div class="invalid-feedback">Please Enter Full Name.</div>
                                </div>

                                <div class="col-md-4">
                                    <label for="email" class="form-label">Email Address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ old('email', $user->email ?? '') }}" required>
                                    <div class="invalid-feedback">Please Enter Valid Email Address.</div>
                                </div>

                                <div class="col-md-4">
                                    <label for="phone" class="form-label">Mobile Number <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="phone" name="phone"
                                        value="{{ old('phone', $user->phone ?? '') }}" required
                                        placeholder="10 Digit Mobile Number">
                                    <div class="invalid-feedback">Please Enter Valid Mobile Number.</div>
                                </div>

                                <div class="col-md-4">
                                    <label for="role_id" class="form-label">Assign Role <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="role_id" name="role_id" required>
                                        <option disabled value="" selected>--Select Role--</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}"
                                                {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Please select a valid user role.</div>
                                </div>

                                <div class="col-md-4">
                                    <label for="Designation" class="form-label">Designation <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="Designation" name="designation" required>
                                        <option disabled value="" selected>--Select Designation--</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ old('designation', $user->designation ?? '') == $role->name ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Please select a valid user designation.</div>
                                </div>

                                <div class="col-md-4">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="" selected>--Select Gender--</option>
                                        <option value="Male"
                                            {{ old('gender', $user->gender ?? '') == 'Male' ? 'selected' : '' }}>Male
                                        </option>
                                        <option value="Female"
                                            {{ old('gender', $user->gender ?? '') == 'Female' ? 'selected' : '' }}>Female
                                        </option>
                                        <option value="Other"
                                            {{ old('gender', $user->gender ?? '') == 'Other' ? 'selected' : '' }}>Other
                                        </option>
                                    </select>
                                </div>

                                <?php /*<div class="col-md-6">
                                    <label for="district" class="form-label">District <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="district" name="district_id" required>
                                        <option disabled value="" selected>--Select District--</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district->id }}"
                                                {{ old('district_id', $user->district_id ?? '') == $district->id ? 'selected' : '' }}>
                                                {{ $district->district_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Please select a valid district.</div>
                                </div> */ ?>

                                <input type="hidden" id="district" name="district_id" value="1">

                                <input type="hidden" id="projects" name="projects" value="1">

                                <div class="col-md-6">
                                    <label for="aadhaar_number" class="form-label">Aadhaar Number</label>
                                    <input type="text" class="form-control" id="aadhaar_number" name="aadhaar_number"
                                        value="{{ old('aadhaar_number', $user->aadhaar_number ?? '') }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="ward_number" class="form-label">Ward Number</label>
                                    <input type="text" class="form-control" id="ward_number" name="ward_number"
                                        value="{{ old('ward_number', $user->ward_number ?? '') }}">
                                </div>

                                <?php /*<div class="col-md-6">
                                    <label for="projects" class="form-label">Projects</label>
                                    <input type="text" class="form-control" id="projects" name="projects"
                                        value="{{ old('projects', $user->projects ?? '') }}">
                                </div> */ ?>

                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $user->address ?? '') }}</textarea>
                                </div>

                                <div class="col-sm-6">
                                    <label for="password1" class="form-label">New Password
                                        {{ empty($user) ? '*' : '' }}</label>
                                    <div class="input-group input-group-password mb-3">
                                        <span class="input-group-text b-r-left"><i
                                                class="ph-bold ph-lock f-s-20"></i></span>
                                        <input type="password" id="password1" class="form-control" name="password"
                                            placeholder="*******" {{ empty($user) ? 'required' : '' }}>
                                        <span class="input-group-text b-r-right">
                                            <i class="ph ph-eye-slash f-s-20 eyes-icon1" id="showPassword1"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">Please create a strong password.</div>
                                </div>

                                <div class="col-sm-6">
                                    <label for="password2" class="form-label">Confirm Password
                                        {{ empty($user) ? '*' : '' }}</label>
                                    <div class="input-group input-group-password mb-3">
                                        <span class="input-group-text b-r-left"><i
                                                class="ph-bold ph-lock f-s-20"></i></span>
                                        <input type="password" id="password2" class="form-control"
                                            name="password_confirmation" placeholder="*******"
                                            {{ empty($user) ? 'required' : '' }}>
                                        <span class="input-group-text b-r-right">
                                            <i class="ph ph-eye-slash f-s-20 eyes-icon2" id="showPassword2"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">Please re-enter same password.</div>
                                </div>

                                <div class="col-12">
                                    <button class="btn" style="background-color: #7cb342; color: #ffffff;"
                                        type="submit">{{ !empty($user) ? 'Update' : 'Create' }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            ['1', '2'].forEach(i => {
                let toggleBtn = document.getElementById("showPassword" + i);
                if (toggleBtn) {
                    toggleBtn.onclick = function() {
                        let input = document.getElementById("password" + i);
                        input.type = input.type === "password" ? "text" : "password";
                        this.classList.toggle("ph-eye");
                        this.classList.toggle("ph-eye-slash");
                    };
                }
            });
        });
    </script>
@endsection