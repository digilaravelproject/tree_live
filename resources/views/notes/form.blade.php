<div class="mb-3">
    <label class="form-label" style="color: #7cb342;">Title</label>
    <input type="text" name="title" value="{{ old('title', $note->title ?? '') }}" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label" style="color: #7cb342;">Content</label>
    <textarea name="content" rows="6" class="form-control">{{ old('content', $note->content ?? '') }}</textarea>
</div>