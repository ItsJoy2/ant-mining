<!DOCTYPE html>
<html lang="en">
  <head>
    @php
        use App\Models\GeneralSetting;
        $generalSettings = GeneralSetting::first();
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $generalSettings->app_name ?? 'Edulife ' }}</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('assets/user/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/user/vendors/css/vendor.bundle.base.css') }}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('assets/user/css/style.css') }}">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
  </head>
  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="row w-100 m-0">
          <div class="content-wrapper full-page-wrapper d-flex align-items-center auth login-bg">
            <div class="card col-lg-4 mx-auto">
                <div class="card-body px-5 py-5">
                                            {{-- Display Validation Errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Display Flash Messages --}}
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('info'))
                            <div class="alert alert-info">
                                {{ session('info') }}
                            </div>
                        @endif
                <h3 class="card-title text-left mb-3">Register</h3>
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="form-group">
                        <label>Wallet Address</label>
                        <input type="text" class="form-control text-white p_input" name="wallet_address" value="{{ old('wallet_address') }}">
                        {{-- <input type="text" class="form-control text-white p_input" name="wallet_address" value="{{ old('wallet_address') }}" pattern="^0x[a-fA-F0-9]{40}$" title="Wallet Address must be a valid EVM address (start with 0x and 42 characters long)" required> --}}

                        {{-- @error('wallet_address') <small class="text-danger">{{ $message }}</small> @enderror --}}
                    </div>

                    <div class="form-group">
                        <label>Referral Wallet (optional)</label>
                        <input type="text" class="form-control text-white p_input" name="refer_wallet" value="{{ old('refer_wallet') }}">
                        @error('refer_wallet') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control text-white p_input" name="password">
                        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" class="form-control text-white p_input" name="password_confirmation">
                    </div> --}}

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-block enter-btn">Register</button>
                    </div>

                    <p class="sign-up text-center">
                        Already have an Account? <a href="{{ route('login') }}">Login</a>
                    </p>
                </form>

              </div>
            </div>
          </div>
          <!-- content-wrapper ends -->
        </div>
        <!-- row ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{ asset('assets/user/vendors/js/vendor.bundle.base.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{ asset('assets/user/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/user/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/user/js/misc.js') }}"></script>
    <script src="{{ asset('assets/user/js/settings.js') }}"></script>
    <script src="{{ asset('assets/user/js/todolist.js') }}"></script>

    <!-- ... আপনার আগের HTML ... -->

    <script>
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const referCode = getQueryParam('ref');
        if (referCode) {
        const referInput = document.querySelector('input[name="referCode"]');
        if (referInput) {
            referInput.value = referCode;
        }
        }
    });
    </script>

    <!-- endinject -->
  </body>
</html>
