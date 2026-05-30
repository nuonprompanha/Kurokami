@csrf

<div class="row g-3">
    <div class="col-md-6">
        <div class="admin-form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
            @error('name')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="admin-form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
            @error('email')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="admin-form-group">
            <label for="role">Department</label>
            <select id="role" name="role" required>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(old('role', $user->role ?? '') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('role')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="admin-form-group">
            <label for="password">Password {{ isset($user) ? '(leave blank to keep current)' : '' }}</label>
            <input type="password" id="password" name="password" {{ isset($user) ? '' : 'required' }}>
            @error('password')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    @if (isset($user))
        <div class="col-md-6">
            <div class="admin-form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation">
            </div>
        </div>
    @else
        <div class="col-md-6">
            <div class="admin-form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>
        </div>
    @endif

    <div class="col-12">
        <div class="admin-form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="3" placeholder="Optional bio...">{{ old('bio', $user->bio ?? '') }}</textarea>
            @error('bio')
                <span class="admin-field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="admin-form-actions">
    <button type="submit" class="admin-btn admin-btn-primary">
        {{ isset($user) ? 'Update User' : 'Create User' }}
    </button>
    <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn-outline">Cancel</a>
</div>
