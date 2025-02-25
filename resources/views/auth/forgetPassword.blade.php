<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laravel 11 Custom Reset Password Functions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <style type="text/css">
    body {
      background-color: #333; /* Dark background for the body */
      color: #ffffff; /* White text color for contrast */
    }
    .card {
      background-color: aliceblue; /* Dark background for the card */
      color: #fff; /* White text inside the card */
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
  </style>
</head>
<body>

<section class="py-3 py-md-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
        <div class="card border border-light-subtle rounded-3 shadow-sm mt-5">
          <div class="card-body p-3 p-md-4 p-xl-5">
            
            <h2 class="fs-6 fw-normal text-center text-secondary mb-4">Reset Password</h2>
            <form method="POST" action="{{ route('forget.password.post') }}">
              @csrf

              @if (Session::has('message'))
                   <div class="alert alert-success" role="alert">
                      {{ Session::get('message') }}
                  </div>
              @endif

              @error('email')
                  <div class="alert alert-danger" role="alert">
                      <strong>{{ $message }}</strong>
                  </div>
              @enderror

              <div class="row gy-2 overflow-hidden">

                <div class="col-12">
                  <div class="form-floating mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" placeholder="name@example.com">
                    <label for="email" class="form-label">{{ __('Email Address') }}</label>
                  </div>
                </div>

                <div class="col-12">
                  <div class="d-grid my-3">
                    <button class="btn btn-primary btn-lg" type="submit">{{ __('Send Password Reset Link') }}</button>
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
