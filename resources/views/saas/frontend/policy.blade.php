@extends('saas.frontend.layouts.app')

@section('content')
    <section id="feature" class="generate-content-area section-t-space">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="h2">{{ $pageTitle }}</h1>
                </div>
                <div class="col-md-12">
                    <hr>
                    {!! nl2br($description) !!}
                </div>
            </div>
        </div>
    </section>
@endsection
