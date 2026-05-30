<form action="{{ route('admin.profile.password') }}" method="POST" class="admin-profile-form">
    @csrf
    @method('PUT')

    <div class="admin-form-group">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" required>
        @error('current_password')
            <span class="admin-field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="admin-form-group">
        <label for="password">New Password</label>
        <input type="password" id="password" name="password" required>
        @error('password')
            <span class="admin-field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="admin-form-group">
        <label for="password_confirmation">Confirm New Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
    </div>

    <button type="submit" class="admin-btn admin-btn-outline">Update Password</button>
</form>
