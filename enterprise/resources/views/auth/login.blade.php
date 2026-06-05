<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP System - Modern Login</title>
    <!-- Modern Typography: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: hsl(224, 76%, 48%);
            --primary-hover: hsl(224, 76%, 40%);
            --text-main: hsl(224, 71%, 12%);
            --text-muted: hsl(220, 9%, 45%);
            --border-color: hsl(210, 14%, 89%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            /* Stunning dark-mode mesh gradient */
            background-color: hsl(224, 71%, 4%);
            background-image: 
                radial-gradient(at 0% 0%, hsl(224, 76%, 18%) 0px, transparent 50%),
                radial-gradient(at 100% 0%, hsl(199, 89%, 15%) 0px, transparent 50%),
                radial-gradient(at 50% 100%, hsl(222, 47%, 8%) 0px, transparent 100%);
            background-size: cover;
            padding: 24px 0;
            overflow-x: hidden;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            padding: 24px;
        }

        .card {
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            /* Glassmorphism styling */
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, hsl(224, 76%, 48%) 0%, hsl(199, 89%, 48%) 100%);
            border: none;
            padding: 36px 24px;
            text-align: center;
            color: white;
            position: relative;
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: rgba(255, 255, 255, 0.92);
            clip-path: ellipse(60% 100% at 50% 100%);
        }

        .card-header h2 {
            margin: 0;
            font-weight: 800;
            font-size: 1.75rem;
            letter-spacing: -0.03em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .card-header p {
            margin: 8px 0 0 0;
            font-size: 0.9rem;
            opacity: 0.95;
            font-weight: 500;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.875rem;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(47, 84, 235, 0.12);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, hsl(224, 76%, 48%) 0%, hsl(224, 76%, 40%) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(47, 84, 235, 0.25);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(47, 84, 235, 0.35);
        }

        .demo-credentials {
            background-color: rgba(0, 0, 0, 0.02);
            padding: 20px;
            border-radius: 16px;
            margin-top: 24px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .demo-credentials h6 {
            margin-top: 0;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--primary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .credential-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 8px 12px;
            margin-bottom: 8px;
            font-size: 0.775rem;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .credential-item:hover {
            background: var(--primary-light);
            border-color: rgba(47, 84, 235, 0.2);
            transform: scale(1.01);
        }

        .credential-item:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h2><i class="bi bi-rocket-takeoff-fill"></i> Enterprise ERP</h2>
                <p>Modern Business Suite Layout</p>
            </div>
            
            <div class="card-body" style="padding: 32px 28px;">
                @if($errors->any())
                <div class="alert alert-danger p-3 mb-4 rounded-3 border-0" role="alert" style="background-color: rgba(239, 68, 68, 0.1); color: hsl(346, 84%, 49%);">
                    <ul class="mb-0 ps-3" style="font-size: 0.775rem; font-weight: 500;">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" 
                               id="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" 
                               id="password" name="password" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-login">Sign In to Workspace</button>
                </form>

                <div class="demo-credentials">
                    <h6><i class="bi bi-key-fill"></i> Quick Fill Demo Accounts</h6>
                    
                    <div class="credential-item" onclick="quickFill('admin@example.com')">
                        <div>
                            <strong>Administrator (All Access)</strong><br>
                            <span class="text-muted">admin@example.com</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </div>

                    <div class="credential-item" onclick="quickFill('purchasing@example.com')">
                        <div>
                            <strong>Purchasing Manager</strong><br>
                            <span class="text-muted">purchasing@example.com</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </div>

                    <div class="credential-item" onclick="quickFill('sales_manager@example.com')">
                        <div>
                            <strong>Sales Manager</strong><br>
                            <span class="text-muted">sales_manager@example.com</span>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function quickFill(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
        }
    </script>
</body>
</html>
