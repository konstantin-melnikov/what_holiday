@extends('layout')

@section('title', 'What is holiday?')

@section('content')
    <div class="container py-5">
        <h1 class="text-center">What is holiday?</h1>
        <form class="mx-auto" style="max-width: 34rem;" method="POST" action="{{ route('home') }}">
            @csrf
            <div class="input-group mb-3">
                <span class="input-group-text">Select date</span>
                <input
                    class="form-control"
                    type="date"
                    name="day"
                    value="{{ $day }}"
                    required
                    pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"
                >
                <button class="btn btn-primary" type="submit">Get holiday</button>
            </div>
        </form>
        @if (!empty($message))
        <div class="alert alert-info" role="alert">
            {{ $message }}
        </div>
        @endif
        </table>
    </div>
@endsection