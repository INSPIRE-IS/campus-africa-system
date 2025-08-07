<h2>Register Student with Firebase</h2>

@if (session('success'))
    <p>{{ session('success') }}</p>
@endif
@if (session('error'))
    <p style="color:red">{{ session('error') }}</p>
@endif

<form method="POST" action="{{ route('firebase.register') }}">
    @csrf

    <input type="text" name="first_name" placeholder="First Name" required />
    <input type="text" name="last_name" placeholder="Last Name" required />
    <input type="text" name="display_name" placeholder="Display Name" />
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <input type="text" name="phone_number" placeholder="Phone Number" />
    <input type="text" name="bed_code" placeholder="Bed Code" />
    <input type="text" name="residence" placeholder="Residence" />
    <input type="text" name="role" placeholder="Role" />

    <button type="submit">Register</button>
</form>
