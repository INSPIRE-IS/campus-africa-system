@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/new-lookup" method="post" autocomplete="off">
    @csrf
        <br/>        <br/>

        <div class="form-field">
            <label>Title</label>
            <select name="group_id" id='group_id'>
                <option value="0" disabled selected>Select one</option>
                <option value="1">Institution of Study</option>
                <!-- <option value="2">Insitution Campus</option> -->
                <option value="3" >Faculty</option>
                <option value="4" >Year</option>
                <option value="5" >Nationality</option>
                <option value="6" >Year of Study</option>
            </select>
        </div>

        <div class="form-field">
            <label>Value</label>
            <input type="text" name="value" id="value"  >
        </div>

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

@endsection