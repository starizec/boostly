@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div id="app"></div>
                <div data-friend='@json($friend)' data-user='@json($currentUser)' style="display: none;"></div>
                <script>
                    console.log('Friend data from PHP:', @json($friend));
                    console.log('Current user data from PHP:', @json($currentUser));
                </script>
            </div>
        </div>
    </div>
</div>
@endsection