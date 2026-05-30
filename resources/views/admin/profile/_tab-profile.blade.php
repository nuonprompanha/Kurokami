<form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="admin-profile-form">
    @csrf
    @method('PUT')

    <div class="admin-form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
        @error('name')
            <span class="admin-field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="admin-form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
        @error('email')
            <span class="admin-field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="admin-form-group">
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio" rows="4" placeholder="Short bio...">{{ old('bio', $user->bio) }}</textarea>
        @error('bio')
            <span class="admin-field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="admin-form-group">
        <label for="avatar">Avatar</label>
        <input type="file" id="avatar" name="avatar" accept="image/*">
        @error('avatar')
            <span class="admin-field-error">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit" class="admin-btn admin-btn-primary">Save Changes</button>
</form>
