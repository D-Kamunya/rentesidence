@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Import Properties</h2>
    <form action="{{ route('owner.property.excel.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="file">Excel File:</label>
            <input type="file" name="excel_file" class="form-control" required>
        </div>
        <button class="btn btn-primary">Import</button>
    </form>
</div>
@endsection
