
{{-- @extends('layouts.app') --}}
@extends('layouts1.app', ['class' => 'g-sidenav-show bg-gray-100'])


@section('content')
@include('layouts1.navbars.auth.topnav', ['title' => 'User Management'])
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
                    <h6>Chefs</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Create Date</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Verified</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($chefs as $chef)
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1" id="chefProfile" data-toggle="modal" data-target="#myModal">
                                            <div>
                                                <img src="{{ $chef->profile_picture ? $chef->profile_picture : 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_640.png' }}" class="avatar me-3" alt="image">
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $chef->first_name . ' ' . $chef->last_name }}</h6>
                                            </div>
                                        </div>
                                    </td>                                                                  
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">{{ $chef->is_chef == 0 ? 'User' : 'Chef' }}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <p class="text-sm font-weight-bold mb-0">{{ $chef->created_at->format('d-m-Y') }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <form id="update-status-form" action="{{ url('/chef/verify', $chef->id) }}" method="POST">
                                            @csrf
                                            <select id="status" class="form-control" name="status">
                                                <option value="">-- Select Status --</option>
                                                <option dislabled value="" {{ $chef->verified_by_admin == 0 ? 'selected' : '' }}>Pending</option>
                                                <option value="approved" {{ $chef->verified_by_admin == 1 ? 'selected' : '' }}>Verified</option>
                                            </select>
                                            <button type="submit" class="btn btn-warning mt-2" onclick="event.preventDefault();
                                            if (confirm('Are you sure you want to update the status?')) {
                                                var form = document.getElementById('update-status-form');
                                                if (document.getElementById('status').value === 'approved') {
                                                    form.action = '{{ route('/chef/verify', ['id' => $chef->id]) }}';
                                                } 
                                                form.submit();
                                            }">Update Status</button>
                                        </form>
                                        
                                        {{-- <!-- Full screen modal --> --}}
                                        {{-- <div class="modal-dialog modal-fullscreen-sm-down">
                                            
                                        </div> --}}
                                        {{-- <p class="text-sm font-weight-bold mb-0">{{ $chef->chefInfo->verified_by_admin }}</p> --}}
                                    </td>
                                    <td class="align-middle text-end">
                                        <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                            @if($chef->is_archived)
                                                <a href="{{ route('archive.user', ['user' => $chef->id]) }}" class="text-sm font-weight-bold mb-0 btn btn-info me-2">Unarchive</a>
                                            @else
                                                <a href="{{ route('archive.user', ['user' => $chef->id]) }}" class="text-sm font-weight-bold mb-0 btn btn-info me-2">Archive</a>
                                            @endif
                                            {{-- <button class="text-sm font-weight-bold mb-0 btn btn-warning">Delete</button> --}}
                                        </div>                                        
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        {!! $chefs->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


