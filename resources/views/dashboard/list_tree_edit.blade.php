@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <main>
        <div class="container-fluid">
            <div class="row m-1">
                <div class="col-12">
                    <h4 class="main-title mb-3">Edit Tree</h4>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('list.trees.update', $tree->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
                                <label>Tree Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $tree->name }}"
                                    required>
                            </div>

                            <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
                                <label>Scientific Name</label>
                                <input type="text" name="scientific_name" class="form-control"
                                    value="{{ $tree->scientific->scientific_name ?? '' }}" required>
                            </div>

                            <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
                                <label>Family Name</label>
                                <input type="text" name="family_name" class="form-control"
                                    value="{{ $tree->family->family_name ?? '' }}" required>
                            </div>

                            <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
                                <label>Height Ratio</label>
                                <input type="text" name="height_ratio" class="form-control"
                                    value="{{ $tree->scientific->height_ratio ?? '' }}">
                            </div>

                            <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
                                <label>Age Ratio</label>
                                <input type="text" name="age_ratio" class="form-control"
                                    value="{{ $tree->scientific->age_ratio ?? '' }}">
                            </div>

                            <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
                                <label>Canopy Ratio</label>
                                <input type="text" name="canopy_ratio" class="form-control"
                                    value="{{ $tree->scientific->canopy_ratio ?? '' }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('tree.name.list') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection
