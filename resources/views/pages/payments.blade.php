
{{-- @extends('layouts.app') --}}
@extends('layouts1.app', ['class' => 'g-sidenav-show bg-gray-100'])


@section('content')
    @include('layouts1.navbars.auth.topnav', ['title' => 'User Bookings'])
    <div class="container-fluid py-4">
    <div class="row mt-4 mx-4">
        <div class="row">
            
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Today's Money</p>
                                    <h5 class="font-weight-bolder">
                                        $53,000
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">+55%</span>
                                        since yesterday
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Today's Users</p>
                                    <h5 class="font-weight-bolder">
                                        2,300
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">+3%</span>
                                        since last week
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                    <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">New Clients</p>
                                    <h5 class="font-weight-bolder">
                                        +3,462
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-danger text-sm font-weight-bolder">-2%</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Sales</p>
                                    <h5 class="font-weight-bolder">
                                        $103,430
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">+5%</span> than last month
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mt-4">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>All Payments</h6>
                </div>
                <form method="GET" action="{{ url('payments') }}">
                    @csrf
                    <div class="form-group row text-md-center">
                        <label for="type" class="col-md-4 col-form-label text-md-right">{{ __('Filter by Type') }}</label>
        
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-10">
                                    <select id="type" class="form-control" name="type">
                                        <option value="">-- Select Type --</option>
                                        <option value="chef" {{ old('type') == 'chef' ? 'selected' : '' }}>Chef</option>
                                        <option value="user" {{ old('type') == 'user' ? 'selected' : '' }}>User</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row text-md-center">
                        <label for="sort" class="col-md-4 col-form-label text-md-right">{{ __('Sort Order') }}</label>
        
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-10">
                                    <select id="sort" class="form-control" name="sort">
                                        <option value="desc" {{ old('sort') == 'desc' ? 'selected' : '' }}>Descending</option>
                                        <option value="asc" {{ old('sort') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                        </div>
                    </div>
                </form>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date & Time</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Payment Type</th>
                                    {{-- <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $payment)
                                    <tr>
                                        <td>
                                            <h6 class="text-center mb-0 text-sm">{{ $payment->user->first_name . ' ' . $payment->user->last_name }}</h6>
                                        </td>  
                                        <td>
                                            <p class="text-center text-sm font-weight-bold mb-0">{{ $payment->created_at->format('d-m-Y H:i') }}</p>
                                        </td>

                                        @if($payment->booking_id)
                                            <td>
                                                <p class="text-center text-sm font-weight-bold mb-0">Booking Payment</p>
                                            </td>
                                            @else
                                                <td>
                                                    <p class="text-center text-sm font-weight-bold mb-0">Chef Registration Payment</p>
                                                </td>
                                        @endif

                                        {{-- <td>
                                            <p class="text-center text-sm font-weight-bold mb-0">{{ $payment->created_at->format('d-m-Y H:i') }}</p>
                                        </td> --}}
                                        
                                        {{-- <td class="align-middle text-end">
                                            <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                                <p class="text-sm font-weight-bold mb-0 btn btn-info me-2">Archive</p>
                                                <p class="text-sm font-weight-bold mb-0 btn btn-warning">Delete</p>
                                            </div>
                                        </td> --}}
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        {!! $payments->links() !!}
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    </div>
@endsection
