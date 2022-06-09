@extends('layouts.admin')

@section('content')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>


<div class="container">
    <br/>
    <h1>System Users</h1>
    <br/>
    <a href="/new/admin"><button type="button" class="inactiveBtn">Add New System User</button></a>
    <br/><br/>

    <table id="admin_table" class="display">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email Address</th>
                <th>Role</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($systemUsers as $user)
                <tr>
                    <td>{{$user->userFirstName}}</td>
                    <td>{{$user->userLastname}}</td>
                    <td>{{$user->email}}</td>
                    <td>{{$user->user_role_name}}</td>
                    <td><a href="/user/edit/{{$user->id}}"><button type="button" class="inactiveBtn">Edit</button></a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br/><br/><br/><br/>

    <h1>Students</h1>
    <br/>
    @if(Auth::user()->role_create_app == 1)
        <a href="/new/student"><button type="button" class="inactiveBtn">Add New Student</button></a>
    @endif
    <br/><br/>

    <table id="student_table" class="display">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email Address</th>
                <th>ID Number</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $user)
                <tr>
                    <td>{{$user->userFirstName}}</td>
                    <td>{{$user->userLastname}}</td>
                    <td>{{$user->email}}</td>
                    <td>{{$user->userId}}</td>
                    <td>
                        @if(Auth::user()->role_edit_app == 1)
                            <a href="/student/edit/{{$user->id}}"><button type="button" class="inactiveBtn">Edit</button></a> &nbsp;&nbsp;
                        @endif

                        @if(Auth::user()->email == "mirichea@whitehart.co.za")
                        <a href="/loginAsStudent/{{$user->id}}"><button type="button" class="inactiveBtn">Login As</button></a>
                        @endif
                        <!-- <a href="/student/upload/{{$user->id}}"><button type="button" class="inactiveBtn">Upload Documents</button></a> -->
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table> 

</div>


<script>
    $(document).ready( function () {
        $('#admin_table').DataTable();
    } );

    $(document).ready( function () {
        $('#student_table').DataTable();
    } );

    
</script>

@endsection