<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dsBilling ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card p-5 text-center">
                    <div class="mb-4">
                        <i class="bi bi-wifi" style="font-size: 4rem; color: #667eea;"></i>
                    </div>
                    <h1 class="mb-3">dsBilling ERP</h1>
                    <p class="text-muted mb-4">Sistem ERP untuk Manajemen Keuangan, Pendapatan, dan Operasional ISP</p>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-outline-primary btn-lg w-100 mb-3">
                        <i class="bi bi-person me-2"></i>
                        Login dengan Form
                    </a>
                    <a href="<?php echo e(route('login-as-admin')); ?>" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Login sebagai Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</body>
</html>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\welcome.blade.php ENDPATH**/ ?>