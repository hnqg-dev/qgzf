<?php /*a:2:{s:56:"/wwwroot/website/qgzf/public/template/default/index.html";i:1761140337;s:55:"/wwwroot/website/qgzf/public/template/default/menu.html";i:1761141118;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>全哥账房</title>
    <link rel="stylesheet" href="/template/default/css/bootstrap.min.css">
    <link rel="stylesheet" href="/template/default/css/qxqlan.css">
    <link rel="stylesheet" href="/template/default/font/qxqicon.css">
    <link rel="stylesheet" href="/template/default/font/qxqfont.css">
    <link rel="stylesheet" href="/template/default/font/bootstrap-icons.min.css">

</head>
<body style="background-color: #f2f2ff;">
<?php 
use think\facade\Session;
if (!Session::has('user')) {
    header('Location: /index/index/login');
    exit;
}
?>
    <nav class="navbar navbar-expand d-lg-none bg-light mobile-nav">
        <div class="container-fluid justify-content-around">

            <a class="nav-link text-center px-2 py-1 default-link" href="<?php echo url('index/index/index'); ?>" data-controller="index/index">
                <i class="d-block qxqicon qxq-shouye fs-5 default-link"></i>
                <span class="d-block small">首页</span>
            </a>
            <a class="nav-link text-center px-2 py-1 default-link" href="<?php echo url('index/zhangben/index'); ?>" data-controller="index/zhangben">
                <i class="d-block qxqicon qxq-zhangben fs-5 default-link"></i>
                <span class="d-block small">账本</span>
            </a>
            <a class="nav-link text-center px-2 py-1 default-link" href="<?php echo url('index/jiedai/index'); ?>" data-controller="index/jiedai">
                <i class="d-block qxqicon qxq-yinhangkaduoka fs-5 default-link"></i>
                <span class="d-block small">资产</span>
            </a>
            <a class="nav-link text-center px-2 py-1 default-link" href="<?php echo url('index/wode/index'); ?>" data-controller="index/wode">
                <i class="d-block bi bi-person fs-5 default-link"></i>
                <span class="d-block small">我的</span>
            </a>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const path = window.location.pathname;
                const navLinks = document.querySelectorAll('.mobile-nav .nav-link');
                let hasActive = false;
                navLinks.forEach(link => {
                    const controller = link.getAttribute('data-controller');
                    if (path.includes(controller)) {
                        link.classList.remove('default-link');
                        link.classList.add('active', 'active-link');
                        
                        const icon = link.querySelector('i');
                        if (icon) {
                            icon.classList.remove('default-link');
                            icon.classList.add('active-link');
                        }
                        hasActive = true;
                    }
                });
                if (!hasActive) {
                    const homeLink = document.querySelector('.mobile-nav .nav-link[data-controller="index/index"]');
                    if (homeLink) {
                        homeLink.classList.remove('default-link');
                        homeLink.classList.add('active', 'active-link');
                        
                        const icon = homeLink.querySelector('i');
                        if (icon) {
                            icon.classList.remove('default-link');
                            icon.classList.add('active-link');
                        }
                    }
                }
            });
            </script>
        </div>
    </nav>


<div class="container-fluid p-0">
    <div class="index-con">
        <div class="ind-container">
            <div class="ind-logo">
                <img src="/template/default/img/logo.png" class="img-fluid">
            </div>
            <h1 class="ind-title cnf-yfxyb">全哥账房</h1>
            <div class="ind-card-container">
                <div class="ind-card">
                    <div class="ind-card-icon">
                        <i class="qxqicon qxq-zhangben"></i>
                    </div>
                    <div class="ind-card-content">
                        <div class="ind-card-label">已有账本 <span class="ind-card-value"><?php echo htmlentities((string) $ledgerCount); ?></span> 本</div>
                    </div>
                </div>
                <div class="ind-card">
                    <div class="ind-card-icon">
                        <i class="qxqicon qxq-kabao"></i>
                    </div>
                    <div class="ind-card-content">
                        <div class="ind-card-label">资产账户 <span class="ind-card-value"><?php echo htmlentities((string) $accountCount); ?></span> 个</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/template/default/js/jquery.min.js"></script>
<script src="/template/default/js/bootstrap.bundle.min.js"></script>
<script src="/template/default/js/bootstrap.min.js"></script>
</body>
</html>
