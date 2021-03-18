@extends('layout')

@section('title', 'What holiday?')

@section('content')
    <div class="container py-5">
        <h1 class="text-center">What holiday?</h1>
        <form class="mx-auto" style="max-width: 34rem;" method="POST" action="{{ route('home') }}">
            @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ol>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                </ol>
            </div>
            @endif
            @csrf
            <div class="input-group mb-3">
                <span class="input-group-text">Select date</span>
                <input
                    class="form-control"
                    type="date"
                    name="date"
                    value="{{ old('date') ?? $date }}"
                    required
                    pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"
                >
                <button class="btn btn-primary" type="submit">Get holiday</button>
            </div>
        </form>
        @forelse ($holidays as $holiday)
            <div class="alert alert-success" role="alert">
                <h3>{{ $holiday['date_raw'] }} - {{ $holiday['title'] }}</h3>
                <p>{{ $holiday['description'] }}</p>
            </div>
        @empty
            <div class="alert alert-info" role="alert">
                Holidays not found
            </div>
        @endforelse
    </div>
@endsection