@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection
@section('content')
    <main class="container-fluid py-5">
        <div class="card shadow-sm">
            <div class="card-header text-white" style="background-color: #7cb342;">Edit Contact</div>
            <div class="card-body">
                <form action="{{ route('contacts.update', $contact->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('contacts.form', ['contact' => $contact])
                    <button type="submit" class="btn text-white" style="background-color: #7cb342; border-color: #7cb342;">Update</button>
                    <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </main>
@endsection