@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <style>
        /* Table Styling */
        .app-data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .app-data-table th,
        .app-data-table td {
            padding: 10px 12px;
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }

        .app-data-table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        /* Inputs inside table */
        .app-data-table input.form-control {
            height: 32px;
            padding: 4px 8px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }

        /* Buttons inside table */
        .app-data-table button.btn {
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 4px;
        }

        /* Optional: make table scrollable horizontally */
        .app-datatable-default {
            overflow-x: auto;
        }

        /* Optional: hover effect */
        .app-data-table tbody tr:hover {
            background-color: #f1f3f5;
        }
    </style>
    <main>
        <div class="container-fluid">
            <div class="row m-1">
                <div class="col-12">
                    <h4 class="main-title mb-3">User Ratings</h4>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card ">
                        <div class="card-body p-0">
                            <div class="app-datatable-default overflow-auto">
                                <table class="display app-data-table default-data-table">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Rating</th>
                                            <th>Comment</th>
                                            <th>Last Update</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ratings as $rating)
                                            <tr>
                                                <td>{{ $rating->user->name ?? 'N/A' }}</td>
                                                <td>
                                                    <form method="POST"
                                                        action="{{ route('user-ratings.update', $rating->id) }}">
                                                        @csrf
                                                        <input type="number" name="rating" value="{{ $rating->rating }}"
                                                            min="1" max="5" class="form-control"
                                                            style="width:80px;">
                                                </td>
                                                <td>
                                                    <input type="text" name="comment" value="{{ $rating->comment }}"
                                                        class="form-control">
                                                </td>
                                                <td>{{ $rating->updated_at->format('d M, Y H:i') }}</td>
                                                <td>
                                                    <button type="submit" class="btn btn-sm" style="background-color: #7cb342; color: #ffffff;">Update</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection