<!-- resources/views/auth/register.blade.php -->

<div class="container">
    <h2>Register</h2>

    <!-- Displaying validation errors -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register.post') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <!-- User Information -->
        <input type="text" name="first_name" placeholder="First Name" value="{{ old('first_name') }}" required>
        @error('first_name')
            <div class="text-danger">{{ $message }}</div>
        @enderror

        <input type="text" name="last_name" placeholder="Last Name" value="{{ old('last_name') }}" required>
        @error('last_name')
            <div class="text-danger">{{ $message }}</div>
        @enderror

        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
        @error('email')
            <div class="text-danger">{{ $message }}</div>
        @enderror

        <input type="text" name="phone_number" placeholder="Phone Number" value="{{ old('phone_number') }}" required>
        @error('phone_number')
            <div class="text-danger">{{ $message }}</div>
        @enderror

        <input type="text" name="status" placeholder="Status" value="{{ old('status') }}" required>
        @error('status')
            <div class="text-danger">{{ $message }}</div>
        @enderror

        <input type="password" name="password" placeholder="Password" required>
        @error('password')
            <div class="text-danger">{{ $message }}</div>
        @enderror

        <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        @error('password_confirmation')
            <div class="text-danger">{{ $message }}</div>
        @enderror

        <input type="file" name="picture">
        @error('picture')
            <div class="text-danger">{{ $message }}</div>
        @enderror

        <!-- Hidden input to hold Firebase device token -->
        <input type="hidden" name="device_token" id="device_token" value="{{ old('device_token') }}">

        <button type="submit">Register</button>
    </form>
</div>


<script type="module">
   import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js';
   import { getMessaging, getToken } from 'https://www.gstatic.com/firebasejs/9.6.1/firebase-messaging.js';

   const firebaseConfig = {
       apiKey: "AIzaSyB7gZBD1Vw-lW0vPkC22vAuN8oqCcIZJHA",
       authDomain: "lumenapi-11b39.firebaseapp.com",
       projectId: "lumenapi-11b39",
       storageBucket: "lumenapi-11b39.firebasestorage.app",
       messagingSenderId: "220455378166",
       appId: "1:220455378166:web:ba8a176de522a48f60e0d7",
       measurementId: "G-SX11TL34RF",
   };

   // Initialize Firebase
   const app = initializeApp(firebaseConfig);
   const messaging = getMessaging(app);

   // Register the service worker when the document is ready
   document.addEventListener('DOMContentLoaded', () => {
       if ('serviceWorker' in navigator) {
           navigator.serviceWorker
               .register('/firebase-messaging-sw.js') // Path to the service worker
               .then(function (registration) {
                   console.log('Service Worker registered with scope:', registration.scope);
               })
               .catch(function (err) {
                   console.log('Service Worker registration failed:', err);
               });
       }

       // Get device token
       getDeviceToken();
   });

   // Function to get device token
   async function getDeviceToken() {
       try {
           const token = await getToken(messaging, {
               vapidKey: 'BHctJ1-cs9u8_VVSuhsBGwFXLUpaz6apaBXutBuKrbTICYqI3ZJzo8zZv1_gfZtQ6W3sERouJj7T1pbrTlfAM5g',
           });
           // Set token in hidden input field
           document.getElementById('device_token').value = token;
           console.log('Device token:', token);
       } catch (error) {
           console.error('Error getting device token:', error);
       }
   }
</script>
