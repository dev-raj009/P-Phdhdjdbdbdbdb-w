<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

ini_set('session.name', SESSION_NAME . '_admin');
session_start();

// Already logged in?
if (!empty($_SESSION['admin_token']) && DB::validateAdminSession($_SESSION['admin_token'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if ($user === ADMIN_USERNAME && password_verify($pass, ADMIN_PASSWORD_HASH)) {
        $tok = DB::createAdminSession();
        $_SESSION['admin_token'] = $tok;
        header('Location: dashboard.php');
        exit;
    } else {
        // Intentional delay to slow brute-force
        sleep(1);
        $error = 'Invalid credentials. Access denied.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — PW Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<style>
  :root{--bg:#060709;--surface:#0d0f14;--card:#111318;--border:rgba(255,255,255,.06);--accent:#dc2626;--text:#e8eaf0;--muted:#6b7280}
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  html,body{height:100%;font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text)}

  body::before{content:'';position:fixed;inset:0;
    background:radial-gradient(ellipse 50% 40% at 50% 0%,rgba(220,38,38,.1) 0%,transparent 60%);
    pointer-events:none}
  body::after{content:'';position:fixed;inset:0;
    background-image:linear-gradient(rgba(255,255,255,.02) 1px,transparent 1px),
                     linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px);
    background-size:40px 40px;pointer-events:none;opacity:.6}

  .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;z-index:1}

  .shield{
    width:56px;height:56px;background:linear-gradient(135deg,rgba(220,38,38,.2),rgba(220,38,38,.05));
    border:1px solid rgba(220,38,38,.4);border-radius:16px;
    display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 24px;
  }

  .card{
    width:100%;max-width:420px;
    background:var(--card);border:1px solid var(--border);border-radius:24px;
    padding:44px 38px;
    box-shadow:0 32px 80px rgba(0,0,0,.7), 0 0 0 1px rgba(255,255,255,.03) inset;
    animation:up .5s cubic-bezier(.22,1,.36,1) both;
  }
  @keyframes up{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}

  h1{font-family:'Syne',sans-serif;font-size:24px;font-weight:800;text-align:center;margin-bottom:4px}
  .sub{color:var(--muted);font-size:13px;text-align:center;margin-bottom:32px}

  label{display:block;font-size:12px;font-weight:500;color:var(--muted);margin-bottom:6px;letter-spacing:.4px;text-transform:uppercase}
  input{
    width:100%;padding:13px 16px;background:var(--surface);border:1px solid var(--border);
    border-radius:11px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:15px;
    outline:none;margin-bottom:16px;transition:border-color .2s,box-shadow .2s;
  }
  input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(220,38,38,.15)}
  input::placeholder{color:#374151}

  .btn{
    width:100%;padding:14px;background:var(--accent);border:none;border-radius:12px;
    color:#fff;font-family:'Syne',sans-serif;font-size:15px;font-weight:700;
    cursor:pointer;letter-spacing:.3px;
    box-shadow:0 8px 24px rgba(220,38,38,.3);transition:all .2s;
  }
  .btn:hover{background:#b91c1c;transform:translateY(-1px);box-shadow:0 12px 32px rgba(220,38,38,.4)}

  .error{
    background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);
    color:#fca5a5;border-radius:10px;padding:12px 14px;font-size:14px;
    margin-bottom:18px;display:flex;align-items:center;gap:8px;
  }

  .lock-note{
    text-align:center;margin-top:24px;font-size:12px;color:var(--muted);
    display:flex;align-items:center;justify-content:center;gap:6px;
  }

  /* Fake lock animation on the shield */
  .shield{animation:pulse 3s ease-in-out infinite}
  @keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(220,38,38,.3)}50%{box-shadow:0 0 0 10px rgba(220,38,38,0)}}
</style>
</head>
<body>
<div class="page">
  <div class="card">
    <div class="shield">🛡️</div>
    <h1>Admin Panel</h1>
    <p class="sub">Restricted access — authorised personnel only</p>

    <?php if ($error): ?>
    <div class="error">⛔ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <label>Username</label>
      <input type="text" name="username" placeholder="admin" autocomplete="off" required>
      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••••" autocomplete="new-password" required>
      <button type="submit" class="btn">🔐 Access Admin Panel</button>
    </form>

    <div class="lock-note">🔒 All activity is logged and monitored</div>
  </div>
</div>
</body>
</html>
