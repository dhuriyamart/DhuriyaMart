@extends('layouts.app')

@section('title', ($page->meta_title ?: $page->title) . ' - DhuriyaMart')
@section('meta_description', $page->meta_description)

@section('content')
<div class="container py-4 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="bg-white rounded-4 p-4 p-md-5 shadow-sm border">
                <h2 class="fw-bold text-dark mb-4 border-bottom pb-3">{{ $page->title }}</h2>
                <div class="lh-lg text-secondary">
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

