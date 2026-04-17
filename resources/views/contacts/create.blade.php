@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection
@section('content')
    <main class="container-fluid py-5">
        <div class="card shadow-sm">
            <div class="card-header text-white" style="background-color: #7cb342;">Add Contact</div>
            <div class="card-body">
                <form action="{{ route('contacts.store') }}" method="POST">
                    @csrf
                    @include('contacts.form')
                    <button type="submit" class="btn btn-primary" style="background-color: #7cb342; border-color: #7cb342;">Save</button>
                    <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </main>
@endsection