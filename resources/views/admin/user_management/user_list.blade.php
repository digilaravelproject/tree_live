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
                            <h4 class="mb-0">User List</h4>
                            <a href="{{ route('create.user') }}" class="btn btn-sm" style="background-color: #7cb342; color: #ffffff;">
                                <i class="ph ph-plus"></i> Add New User
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="userTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Designation</th>
                                            <th>Email</th>
                                            <th>Mobile Number</th>
                                            <th>Role</th>
                                            <?php /*<th>District</th> */?>
                                            <th>Ward Number</th>
                                            <th>Gender</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($users as $key => $user)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->designation ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ph ph-envelope me-1"></i> {{ $user->email }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center" style="color: #7cb342;">
                                                        <i class="ph ph-phone me-1"></i> {{ $user->phone }}
                                                    </div>
                                                </td>
                                                <td>
                                                    @foreach ($user->roles as $role)
                                                        <span class="badge" style="background-color: #7cb342;">{{ $role->name }}</span>
                                                    @endforeach
                                                </td>
                                                <?php /* <td>{{ $user->district->district_name ?? 'N/A' }}</td> */?>
                                                <td>{{ $user->ward_number ?? '-' }}</td>
                                                <td>{{ $user->gender ?? '-' }}</td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input status-toggle" type="checkbox"
                                                            data-id="{{ $user->id }}"
                                                            {{ $user->status == 1 ? 'checked' : '' }}>

                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('user.edit', $user->id) }}"
                                                            class="btn btn-sm" style="background-color: #9ccc65; color: #ffffff;">
                                                            <i class="ph ph-pencil"></i>
                                                        </a>

                                                        <a href="{{ route('user.delete', $user->id) }}"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this user?');">
                                                            <i class="ph ph-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center text-muted">No Users Found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.status-toggle').change(function() {
                var status = $(this).prop('checked') ? 1 : 0;
                var userId = $(this).data('id');
                var label = $(this).siblings('label');

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "{{ url('admin/update-user-status') }}/" + userId,
                    data: {
                        'status': status,
                        '_token': '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        if (data.success) {
                            label.text(status ? 'Active' : 'Inactive');
                            alert(data.message);
                        }
                    },
                    error: function(e) {
                        console.log(e);
                        alert('Something went wrong');
                    }
                });
            });
        });
    </script>

@endsection