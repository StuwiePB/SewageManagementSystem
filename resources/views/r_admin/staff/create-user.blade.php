@extends('r_admin.layout')

@section('title', 'Add user')

@push('styles')
<style>
    .staff-user-form-wrap { max-width: 34rem; margin: 0 auto; }
    .staff-user-form-wrap .back-row { margin-bottom: 1.25rem; }
    .staff-user-form-wrap .form-title { font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin: 0 0 0.35rem; }
    .staff-user-form-wrap .form-subtitle { color: var(--text-secondary); font-size: 0.9375rem; margin: 0 0 1.5rem; line-height: 1.45; }
    .staff-user-form-wrap .card { padding: 1.75rem; }
    .staff-user-form-wrap label .req { color: var(--accent-red); }
    .password-field-row { display: flex; gap: 0.5rem; align-items: stretch; }
    .password-field-row input { flex: 1; }
    .password-field-row .btn-generate { flex-shrink: 0; padding: 0.5rem 0.9rem; white-space: nowrap; font-size: 0.8125rem; }
    .field-hint { font-size: 0.8125rem; color: var(--text-secondary); margin: 0.35rem 0 0; line-height: 1.4; }
    .domain-list { font-size: 0.8125rem; color: var(--text-secondary); margin: 0.35rem 0 0; }
    .domain-list code { color: var(--accent-blue); font-size: 0.78rem; }
    .client-email-error { font-size: 0.8125rem; color: #f87171; margin: 0.35rem 0 0; display: none; }
    .client-email-error.visible { display: block; }
    .form-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.5rem; }
</style>
@endpush

@section('content')
@php
    $adminSuffix = \App\Support\AccountEmail::ADMIN_SUFFIX;
    $operationSuffix = \App\Support\AccountEmail::OPERATION_SUFFIX;
@endphp
<div class="staff-user-form-wrap">
    <div class="back-row">
        <a href="{{ route('admin.staff.index') }}" class="back-link"><i class="fas fa-arrow-left" style="margin-right:0.35rem;"></i>Back to staff directory</a>
    </div>
    <h1 class="form-title">Add user</h1>
    <p class="form-subtitle">Create an <strong>Admin</strong> (console access) or <strong>Operation</strong> (operations dashboard) account. Email domain must match the role you select.</p>

    <div class="card">
        <form method="POST" action="{{ route('admin.staff.users.store') }}" id="staff-user-form" novalidate>
            @csrf

            <div class="form-group">
                <label for="name">Name <span class="req">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required autocomplete="name" autofocus>
                @error('name') <div class="text-danger" style="color:#f87171;font-size:0.8125rem;margin-top:0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="username">Username <span class="req">*</span></label>
                <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}" required autocomplete="username" pattern="[a-zA-Z0-9._-]+" maxlength="50" placeholder="e.g. ahmad.ops">
                <p class="field-hint">Unique login identifier (letters, numbers, . _ -).</p>
                @error('username') <div class="text-danger" style="color:#f87171;font-size:0.8125rem;margin-top:0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email <span class="req">*</span></label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autocomplete="email">
                <p class="domain-list" id="email-domain-hint">Allowed for <strong id="email-role-label">Admin</strong>: <code id="email-suffix-display">{{ $adminSuffix }}</code></p>
                <p class="domain-list">Reference — Admin: <code>{{ $adminSuffix }}</code> · Operation: <code>{{ $operationSuffix }}</code></p>
                <p class="client-email-error" id="email-client-error" role="alert"></p>
                @error('email') <div class="text-danger" style="color:#f87171;font-size:0.8125rem;margin-top:0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="password">Password <span class="req">*</span></label>
                <div class="password-field-row">
                    <input type="password" name="password" id="password" class="form-control" required autocomplete="new-password" minlength="8">
                    <button type="button" class="btn btn-secondary btn-generate" id="btn-generate-password">Generate</button>
                </div>
                <p class="field-hint">Enter manually or click Generate for a strong random password.</p>
                @error('password') <div class="text-danger" style="color:#f87171;font-size:0.8125rem;margin-top:0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm password <span class="req">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required autocomplete="new-password" minlength="8">
            </div>

            <div class="form-group">
                <label for="role">Role <span class="req">*</span></label>
                <select name="role" id="role" class="form-control" required>
                    <option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected(old('role', $defaultRole) === \App\Models\User::ROLE_ADMIN)>Admin</option>
                    <option value="{{ \App\Models\User::ROLE_OPERATOR }}" @selected(old('role', $defaultRole) === \App\Models\User::ROLE_OPERATOR)>Operator</option>
                </select>
            </div>

            <div class="form-group" id="crew-wrap" style="{{ old('role', $defaultRole) === \App\Models\User::ROLE_OPERATOR ? '' : 'display:none;' }}">
                <label for="crew_id">Crew <span class="req" style="color:var(--text-secondary);font-weight:400;">(optional — for crew leaders)</span></label>
                <select name="crew_id" id="crew_id" class="form-control">
                    <option value="">— None —</option>
                    @foreach($crews as $c)
                        <option value="{{ $c->id }}" {{ (string) old('crew_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <p class="field-hint">Assign a crew to make this user a crew leader (workers attendance).</p>
                @error('crew_id') <div class="text-danger" style="color:#f87171;font-size:0.8125rem;margin-top:0.25rem;">{{ $message }}</div> @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create user</button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var adminSuffix = @json($adminSuffix);
    var operationSuffix = @json($operationSuffix);
    var roleAdmin = @json(\App\Models\User::ROLE_ADMIN);
    var roleOperator = @json(\App\Models\User::ROLE_OPERATOR);

    var roleSelect = document.getElementById('role');
    var emailInput = document.getElementById('email');
    var emailRoleLabel = document.getElementById('email-role-label');
    var emailSuffixDisplay = document.getElementById('email-suffix-display');
    var emailClientError = document.getElementById('email-client-error');
    var crewWrap = document.getElementById('crew-wrap');
    var crewSelect = document.getElementById('crew_id');
    var form = document.getElementById('staff-user-form');
    var pass = document.getElementById('password');
    var passConf = document.getElementById('password_confirmation');

    function updateEmailUi() {
        if (!roleSelect || !emailRoleLabel || !emailSuffixDisplay) return;
        var isOp = roleSelect.value === roleOperator;
        emailRoleLabel.textContent = isOp ? 'Operator' : 'Admin';
        emailSuffixDisplay.textContent = isOp ? operationSuffix : adminSuffix;
        emailInput.placeholder = 'name' + (isOp ? operationSuffix : adminSuffix);
        crewWrap.style.display = isOp ? '' : 'none';
        if (!isOp && crewSelect) crewSelect.value = '';
        validateEmailClient();
    }

    function validateEmailClient() {
        if (!emailInput || !emailClientError || !roleSelect) return;
        var v = (emailInput.value || '').trim().toLowerCase();
        emailClientError.classList.remove('visible');
        emailClientError.textContent = '';
        if (!v) return true;
        var need = roleSelect.value === roleOperator ? operationSuffix.toLowerCase() : adminSuffix.toLowerCase();
        if (!v.endsWith(need)) {
            emailClientError.textContent = 'Email must use ' + need + ' for the selected role.';
            emailClientError.classList.add('visible');
            return false;
        }
        return true;
    }

    function generatePassword() {
        var chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$%&*';
        var out = '';
        var cryptoObj = window.crypto || window.msCrypto;
        if (cryptoObj && cryptoObj.getRandomValues) {
            var arr = new Uint32Array(16);
            cryptoObj.getRandomValues(arr);
            for (var i = 0; i < 16; i++) out += chars[arr[i] % chars.length];
        } else {
            for (var j = 0; j < 16; j++) out += chars[Math.floor(Math.random() * chars.length)];
        }
        pass.value = out;
        passConf.value = out;
        pass.type = 'text';
        passConf.type = 'text';
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', updateEmailUi);
        emailInput.addEventListener('input', validateEmailClient);
        emailInput.addEventListener('blur', validateEmailClient);
        updateEmailUi();
    }

    document.getElementById('btn-generate-password').addEventListener('click', generatePassword);

    if (form) {
        form.addEventListener('submit', function (e) {
            if (!validateEmailClient()) {
                e.preventDefault();
                emailInput.focus();
            }
        });
    }
})();
</script>
@endpush
