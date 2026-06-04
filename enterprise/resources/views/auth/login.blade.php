<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px 10px 0 0 !important;
            padding: 30px;
            text-align: center;
            color: white;
        }
        .card-header h2 {
            margin: 0;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
            font-size: 14px;
            border: 1px solid #e0e0e0;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 5px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
        }
        .demo-credentials {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .demo-credentials h6 {
            margin-top: 0;
            color: #667eea;
        }
        .demo-credentials p {
            font-size: 12px;
            margin: 5px 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h2>ERP System</h2>
                <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">NetSuite Basics</p>
            </div>
            <div class="card-body" style="padding: 30px;">
                @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach($errors->all() as $error)
                    <small>{{ $error }}</small><br>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-login">Login</button>
                </form>

                <div class="demo-credentials">
                    <h6>Demo Credentials</h6>
                    <p><strong>Admin (All Roles):</strong><br>
                    Email: admin@example.com<br>
                    Password: password</p>
                    <hr style="margin: 10px 0;">
                    <p><strong>Purchasing Manager:</strong><br>
                    Email: purchasing@example.com<br>
                    Password: password</p>
                    <hr style="margin: 10px 0;">
                    <p><strong>Other Users Available:</strong><br>
                    inventory@example.com<br>
                    ap@example.com<br>
                    sales@example.com<br>
                    sales_manager@example.com</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
