<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel 11 Custom Reset Password Functions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <style type="text/css">
        body {
            background: #181818; /* Dark background for the body */
            color: #ffffff; /* White text color for contrast */
        }

        .card {
            background-color: aliceblue; /* Dark background for the card */
            color: black; /* White text inside the card */
        }

        .form-control {
      
        border: 1px solid black; /* Subtle border for input fields */
        }

        .form-control:focus {
        border-color: #333; /* Blue border when input is focused */
        
        }

        .btn-primary {
        background-color: black; /* Primary button color */
        border-color: blueviolet;
        color: azure;
        }
        .btn-primary:hover {
        background-color: darkgray; /* Darker shade for hover effect */
        border-color: #004085; /* Darker border on hover */
        }

        

        .form-label {
            color: #bbb; /* Light gray text for labels */
        }

        .bg-light {
            background-color: #181818 !important; /* Dark background for the section */
        }
    </style>
</head>

<body>

    <section class="bg-light py-3 py-md-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
                    <div class="card border border-light-subtle rounded-3 shadow-sm mt-5">
                        <div class="card-body p-3 p-md-4 p-xl-5">

                            <h2 class="fs-6 fw-normal text-center text-secondary mb-4">Reset Password</h2>
                            <form method="POST" action="{{ route('reset.password.post') }}">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">

                                @if (Session::has('message'))
                                    <div class="alert alert-success" role="alert">
                                        {{ Session::get('message') }}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <div class="row gy-2 overflow-hidden">

                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                name="password" id="password" placeholder="name@example.com" >
                                            <label for="password" class="form-label">{{ __('Password') }}</label>
                                            @if ($errors->has('password'))
                                                <span class="text-danger">{{ $errors->first('password') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="password"
                                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                                name="password_confirmation" id="password_confirmation"
                                                placeholder="name@example.com" >
                                            <label for="password_confirmation"
                                                class="form-label">{{ __('Confirm Password') }}</label>
                                            @if ($errors->has('password_confirmation'))
                                                <span
                                                    class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-grid my-3">
                                            <button class="btn btn-primary btn-lg"
                                                type="submit">{{ __('Reset Password') }}</button>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</body>

</html>
