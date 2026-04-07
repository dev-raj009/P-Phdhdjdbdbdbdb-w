<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/api.php';

ini_set('session.name', SESSION_NAME);
session_start();
if (empty($_SESSION['token'])) {
    header('Location: index.php');
    exit;
}
$token = $_SESSION['token'];

// Fetch batches
$batchRes = PWAPI::getBatches($token);
$batches  = $batchRes['data'] ?? [];

// Fetch profile
$profileRes = PWAPI::getProfile($token);
$profile    = $profileRes['data'] ?? [];
$userName   = trim(($profile['firstName'] ?? '') . ' ' . ($profile['lastName'] ?? ''));
if (!$userName) $userName = 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Courses — PW Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#08090d;--surface:#10121a;--card:#14161f;
    --border:rgba(255,255,255,.07);--accent:#f97316;--text:#e8eaf0;--muted:#6b7280;
    --radius:16px;
  }
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  html,body{min-height:100%;font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text)}

  body::before{content:'';position:fixed;inset:0;
    background:radial-gradient(ellipse 60% 40% at 80% 10%,rgba(249,115,22,.1) 0%,transparent 60%);
    pointer-events:none;z-index:0}
  body::after{content:'';position:fixed;inset:0;
    background-image:linear-gradient(rgba(255,255,255,.02) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px);
    background-size:48px 48px;pointer-events:none;z-index:0;opacity:.5}

  /* ── Navbar ── */
  nav{
    position:sticky;top:0;z-index:100;
    background:rgba(8,9,13,.85);
    backdrop-filter:blur(20px);
    border-bottom:1px solid var(--border);
    padding:0 32px;height:64px;
    display:flex;align-items:center;justify-content:space-between;
  }
  .nav-brand{display:flex;align-items:center;gap:10px}
  .nav-logo{width:34px;height:34px;background:var(--accent);border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    font-family:'Syne',sans-serif;font-weight:800;font-size:16px;color:#fff;
    box-shadow:0 0 16px rgba(249,115,22,.35)}
  .nav-title{font-family:'Syne',sans-serif;font-weight:700;font-size:17px}
  .nav-title span{color:var(--accent)}
  .nav-right{display:flex;align-items:center;gap:16px}
  .user-chip{
    display:flex;align-items:center;gap:8px;
    background:var(--surface);border:1px solid var(--border);
    border-radius:50px;padding:6px 14px 6px 8px;font-size:13.5px;
  }
  .user-avatar{
    width:28px;height:28px;border-radius:50%;background:var(--accent);
    display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;
  }
  .btn-logout{
    padding:8px 16px;background:transparent;border:1px solid var(--border);
    border-radius:50px;color:var(--muted);font-family:'DM Sans',sans-serif;font-size:13px;
    cursor:pointer;transition:all .2s;
  }
  .btn-logout:hover{border-color:#ef4444;color:#ef4444}

  /* ── Page ── */
  .page{max-width:1200px;margin:0 auto;padding:40px 24px;position:relative;z-index:1}

  /* ── Hero ── */
  .hero{margin-bottom:40px}
  .hero h1{font-family:'Syne',sans-serif;font-size:32px;font-weight:800;letter-spacing:-.5px}
  .hero h1 span{color:var(--accent)}
  .hero p{color:var(--muted);font-size:15px;margin-top:6px}

  /* ── Empty state ── */
  .empty{
    text-align:center;padding:80px 20px;
    background:var(--card);border:1px solid var(--border);border-radius:24px;
  }
  .empty-icon{font-size:56px;margin-bottom:16px}
  .empty h2{font-family:'Syne',sans-serif;font-size:22px;font-weight:700;margin-bottom:8px}
  .empty p{color:var(--muted);max-width:380px;margin:0 auto}

  /* ── Grid ── */
  .courses-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:20px;
  }

  /* ── Course card ── */
  .course-card{
    background:var(--card);border:1px solid var(--border);border-radius:20px;
    overflow:hidden;transition:transform .2s, box-shadow .2s, border-color .2s;
    cursor:pointer;text-decoration:none;color:inherit;
    animation:cardIn .4s cubic-bezier(.22,1,.36,1) both;
  }
  .course-card:hover{
    transform:translateY(-4px);
    box-shadow:0 20px 60px rgba(0,0,0,.5);
    border-color:rgba(249,115,22,.3);
  }
  @keyframes cardIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}

  .course-thumb{
    height:140px;display:flex;align-items:center;justify-content:center;
    font-size:48px;position:relative;overflow:hidden;
  }
  .course-thumb-bg{
    position:absolute;inset:0;opacity:.15;
    background:linear-gradient(135deg,var(--accent),transparent);
  }
  .course-body{padding:20px}
  .course-badge{
    display:inline-block;padding:3px 10px;border-radius:50px;
    background:rgba(249,115,22,.15);border:1px solid rgba(249,115,22,.3);
    color:var(--accent);font-size:11px;font-weight:500;letter-spacing:.5px;
    text-transform:uppercase;margin-bottom:10px;
  }
  .course-name{
    font-family:'Syne',sans-serif;font-size:16px;font-weight:700;line-height:1.3;
    margin-bottom:8px;letter-spacing:-.2px;
  }
  .course-id{
    font-size:12px;color:var(--muted);font-family:'DM Mono',monospace;
    background:var(--surface);border-radius:6px;padding:3px 8px;display:inline-block;
  }
  .course-footer{
    border-top:1px solid var(--border);padding:14px 20px;
    display:flex;align-items:center;justify-content:space-between;
  }
  .course-tag{font-size:12px;color:var(--muted)}
  .course-arrow{font-size:18px;color:var(--accent);opacity:0;transition:opacity .2s, transform .2s}
  .course-card:hover .course-arrow{opacity:1;transform:translateX(4px)}

  /* Stats bar */
  .stats-bar{
    display:flex;gap:16px;margin-bottom:32px;flex-wrap:wrap;
  }
  .stat{
    background:var(--card);border:1px solid var(--border);border-radius:14px;
    padding:16px 22px;display:flex;align-items:center;gap:12px;
  }
  .stat-icon{font-size:22px}
  .stat-val{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;line-height:1}
  .stat-lbl{font-size:12px;color:var(--muted);margin-top:2px}

  /* emojis for subjects */
  .e1{background:linear-gradient(135deg,#f97316,#dc2626)}
  .e2{background:linear-gradient(135deg,#3b82f6,#8b5cf6)}
  .e3{background:linear-gradient(135deg,#10b981,#0891b2)}
  .e4{background:linear-gradient(135deg,#f59e0b,#d97706)}
  .e5{background:linear-gradient(135deg,#ec4899,#8b5cf6)}
</style>
</head>
<body>

<nav>
  <div class="nav-brand">
    <div class="nav-logo">P</div>
    <div class="nav-title">PW<span>Portal</span></div>
  </div>
  <div class="nav-right">
    <div class="user-chip">
      <div class="user-avatar"><?= strtoupper(substr($userName,0,1)) ?></div>
      <?= htmlspecialchars($userName) ?>
    </div>
    <button class="btn-logout" onclick="logout()">Logout</button>
  </div>
</nav>

<div class="page">
  <!-- Hero -->
  <div class="hero">
    <h1>Hello, <span><?= htmlspecialchars(explode(' ',$userName)[0]) ?></span> 👋</h1>
    <p>Here are all your enrolled Physics Wallah courses</p>
  </div>

  <!-- Stats -->
  <div class="stats-bar">
    <div class="stat">
      <div class="stat-icon">📚</div>
      <div>
        <div class="stat-val"><?= count($batches) ?></div>
        <div class="stat-lbl">Enrolled Courses</div>
      </div>
    </div>
    <div class="stat">
      <div class="stat-icon">✅</div>
      <div>
        <div class="stat-val">Active</div>
        <div class="stat-lbl">Account Status</div>
      </div>
    </div>
    <div class="stat">
      <div class="stat-icon">📅</div>
      <div>
        <div class="stat-val"><?= date('d M Y') ?></div>
        <div class="stat-lbl">Today</div>
      </div>
    </div>
  </div>

  <?php if (empty($batches)): ?>
  <div class="empty">
    <div class="empty-icon">📭</div>
    <h2>No Courses Found</h2>
    <p>You don't have any enrolled courses yet. Purchase a batch from PW to see it here.</p>
  </div>
  <?php else: ?>
  <div class="courses-grid">
    <?php
    $emojis = ['⚡','🔬','📐','🧬','🌍','🧮','📡','🔭','💡','🎯'];
    $gradients = ['e1','e2','e3','e4','e5'];
    foreach ($batches as $i => $batch):
      $name = $batch['name'] ?? 'Course';
      $id   = $batch['_id'] ?? '';
      $lang = $batch['language'] ?? 'English';
      $emoji = $emojis[$i % count($emojis)];
      $grad  = $gradients[$i % count($gradients)];
    ?>
    <div class="course-card" onclick="window.location.href='course.php?id=<?= urlencode($id) ?>&name=<?= urlencode($name) ?>'">
      <div class="course-thumb <?= $grad ?>">
        <div class="course-thumb-bg"></div>
        <span style="position:relative;z-index:1"><?= $emoji ?></span>
      </div>
      <div class="course-body">
        <div class="course-badge">📚 Batch</div>
        <div class="course-name"><?= htmlspecialchars($name) ?></div>
        <div class="course-id"><?= htmlspecialchars($id) ?></div>
      </div>
      <div class="course-footer">
        <span class="course-tag">🌐 <?= htmlspecialchars($lang) ?></span>
        <span class="course-arrow">→</span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
async function logout(){
  if(!confirm('Are you sure you want to logout?')) return;
  const r = await fetch('auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})});
  const d = await r.json();
  if(d.redirect) location.href=d.redirect;
}
// stagger card animations
document.querySelectorAll('.course-card').forEach((c,i)=>c.style.animationDelay=(i*60)+'ms');
</script>
</body>
</html>
