<div class="row">
    <!-- Name -->
    <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
        <label class="form-label" style="color: #7cb342;">Name</label>
        <input type="text" name="name" value="{{ old('name', $contact->name ?? '') }}" class="form-control" required>
    </div>
    <!-- Email -->
    <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
        <label class="form-label" style="color: #7cb342;">Email</label>
        <input type="email" name="email" value="{{ old('email', $contact->email ?? '') }}" class="form-control">
    </div>
    <!-- Phone -->
    <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
        <label class="form-label" style="color: #7cb342;">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $contact->phone ?? '') }}" class="form-control">
    </div>
    <hr class="mt-4">
    <!-- Instagram -->
    <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
        <label class="form-label" style="color: #7cb342;">Instagram</label>
        <input type="url" name="instagram" value="{{ old('instagram', $contact->instagram ?? '') }}"
            class="form-control">
    </div>
    <!-- Facebook -->
    <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
        <label class="form-label" style="color: #7cb342;">Facebook</label>
        <input type="url" name="facebook" value="{{ old('facebook', $contact->facebook ?? '') }}"
            class="form-control">
    </div>
    <!-- WhatsApp -->
    <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
        <label class="form-label" style="color: #7cb342;">WhatsApp Number</label>
        <input type="text" name="whatsapp" value="{{ old('whatsapp', $contact->whatsapp ?? '') }}"
            class="form-control" placeholder="e.g. 919876543210">
    </div>
    <!-- YouTube -->
    <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
        <label class="form-label" style="color: #7cb342;">YouTube</label>
        <input type="url" name="youtube" value="{{ old('youtube', $contact->youtube ?? '') }}" class="form-control">
    </div>
    <!-- LinkedIn -->
    <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
        <label class="form-label" style="color: #7cb342;">LinkedIn</label>
        <input type="url" name="linkedin" value="{{ old('linkedin', $contact->linkedin ?? '') }}"
            class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label" style="color: #7cb342;">Details / Description</label>
        <textarea name="details" id="details" class="form-control" rows="6">{{ old('details', $contact->details ?? '') }}</textarea>
    </div>
</div>
@section('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#details'))
            .catch(error => console.error(error));
    </script>
@endsection