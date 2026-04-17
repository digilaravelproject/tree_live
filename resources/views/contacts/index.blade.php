@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection
@section('content')
    <main class="container-fluid py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="main-title mb-0" style="color: #7cb342;">Contact List</h4>
            <a href="{{ route('contacts.create') }}" class="btn btn-primary" style="background-color: #7cb342; border-color: #7cb342;">+ Add Contact</a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-bordered align-middle mb-0">
                    <thead style="background-color: #7cb342;">
                        <tr>
                            <th style="color: white;">Name</th>
                            <th style="color: white;">Email</th>
                            <th style="color: white;">Phone</th>
                            <th style="color: white;">Social Links</th>
                            <th style="color: white;">Details</th>
                            <th class="text-center" style="color: white;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contacts as $contact)
                            <tr>
                                <td>{{ $contact->name }}</td>
                                <td>{{ $contact->email ?? '-' }}</td>
                                <td>{{ $contact->phone ?? '-' }}</td>
                                <td>
                                    @if ($contact->instagram)
                                        <a href="{{ $contact->instagram }}" target="_blank">Insta</a> |
                                    @endif
                                    @if ($contact->facebook)
                                        <a href="{{ $contact->facebook }}" target="_blank">FB</a> |
                                    @endif
                                    @if ($contact->whatsapp)
                                        <a href="https://wa.me/{{ $contact->whatsapp }}" target="_blank">WA</a> |
                                    @endif
                                    @if ($contact->youtube)
                                        <a href="{{ $contact->youtube }}" target="_blank">YT</a> |
                                    @endif
                                    @if ($contact->linkedin)
                                        <a href="{{ $contact->linkedin }}" target="_blank">LI</a>
                                    @endif
                                </td>
                                <td>{!! Str::limit(strip_tags($contact->details), 100) !!}</td>
                                <td class="text-center">
                                    <a href="{{ route('contacts.edit', $contact->id) }}"
                                        class="btn btn-sm btn-info me-1" style="background-color: #7cb342; border-color: #7cb342; color: white;">Edit</a>
                                    <form action="{{ route('contacts.destroy', $contact->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this contact?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3">No contacts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection