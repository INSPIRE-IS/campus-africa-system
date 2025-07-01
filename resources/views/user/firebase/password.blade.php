<h2>Update Firebase Password</h2>

@if (session('success'))
    <p>{{ session('success') }}</p>
@endif
@if (session('error'))
    <p style="color:red">{{ session('error') }}</p>
@endif

<form method="POST" action="{{ route('firebase.password.update') }}">
    @csrf

    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="new_password" placeholder="New Password" required />

    <button type="submit">Update Password</button>
</form>
