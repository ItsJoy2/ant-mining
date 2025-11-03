@extends('user.layouts.app')

@section('userContent')

<div class="page-header">
    <h3 class="page-title">My Global Income Eligibility</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('user.dashboard') }}" class="btn btn-outline-danger">
                    <i class="mdi mdi-arrow-left-circle pt-2"></i> Return Back
                </a>
            </li>
        </ol>
    </nav>
</div>

<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Criteria</th>
                            <th>Required</th>
                            <th>Your Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>

                        {{-- Direct Referral Investment --}}
                        <tr>
                            <td><strong>Direct Referral Investment</strong></td>
                            <td>${{ number_format($settings->min_direct_ref_invest, 2) }}</td>
                            <td>${{ number_format($directInvest, 2) }}</td>
                            <td>
                                @if($directInvest >= $settings->min_direct_ref_invest)
                                    <span class="text-success fw-bold"> Achieved</span>
                                @else
                                    <span class="text-danger fw-bold">
                                        Need ${{ number_format($settings->min_direct_ref_invest - $directInvest, 2) }} more
                                    </span>
                                @endif
                            </td>
                        </tr>

                        {{-- Team Investment --}}
                        <tr>
                            <td><strong>Team Investment (Up to 5 Levels)</strong></td>
                            <td>${{ number_format($settings->min_team_invest, 2) }}</td>
                            <td>${{ number_format($teamInvest, 2) }}</td>
                            <td>
                                @if($teamInvest >= $settings->min_team_invest)
                                    <span class="text-success fw-bold"> Achieved</span>
                                @else
                                    <span class="text-danger fw-bold">
                                        Need ${{ number_format($settings->min_team_invest - $teamInvest, 2) }} more
                                    </span>
                                @endif
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            {{-- Eligibility Message --}}
            <div class="mt-4">
                @if($directInvest >= $settings->min_direct_ref_invest && $teamInvest >= $settings->min_team_invest)
                    <div class="alert alert-success">
                        Congratulations! You are eligible for Global Income.
                    </div>
                @else
                    <div class="alert alert-danger">
                        ⚠ You are <strong>not yet eligible</strong> for Global Income.
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

@endsection

@push('styles')
    <style>
        .content-wrapper{
            height: 96vh;
        }
    </style>
@endpush
