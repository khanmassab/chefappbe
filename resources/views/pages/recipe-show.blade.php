@extends('layouts1.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Title: ') }}</h4>
                     <h2>{{ $recipe->recipe_name }}</h2>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h4>{{ __('Image: ') }}</h4>
                         <img src="{{ $recipe->image }}" alt="{{ $recipe->recipe_name }}" class="img-fluid">
                    </div>
                    <div class="text-center">


                        <h4>{{ __('Recipe: ') }}</h4>
                        <p>{{ $recipe->recipe_requirements }}</p>
                    </div>
                    <div class="text-center">
                        <h4>{{ __('Recipe Video: ') }}</h4>
                        <div class="embed-responsive embed-responsive-16by9">
                            <iframe class="embed-responsive-item" src="{{ $recipe->recipe_video }}" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
