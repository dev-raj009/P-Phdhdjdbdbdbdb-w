<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/api.php';

ini_set('session.name', SESSION_NAME);
session_start();
if (empty($_SESSION['token'])) { header('Location: index.php'); exit; }

$token    = $_SESSION['token'];
$batchId  = preg_replace('/[^a-f0-9]/i','', $_GET['id'] ?? '');
$batchName= htmlspecialchars($_GET['name'] ?? 'Course');

if (!$batchId) { header('Location: dashboard.php'); exit; }

$res      = PWAPI::getBatchDetails($token, $batchId);
$subjects = $res['data']['subjects'] ?? [];
$details  = $res['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $batchName ?> — PW Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<style>
  :root{--bg:#08090d;--surface:#10121a;--card:#14161f;--border:rgba(255,255,255,.07);--accent:#f97316;--text:#e8eaf0;--muted:#6b7280;--radius:16px}
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  html,body{min-height:100%;font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text)}
  body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 50% 40% at 30% 10%,rgba(249,115,22,.1) 0%,transparent 60%);pointer-events:none;z-index:0}

  nav{position:sticky;top:0;z-index:100;background:rgba(8,9,13,.85);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between}
  .nav-brand{display:flex;align-items:center;gap:10px}
  .nav-logo{width:34px;height:34px;background:var(--accent);border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:16px;color:#fff}
  .nav-title{font-family:'Syne',sans-serif;font-weight:700;font-size:17px}
  .nav-title span{color:var(--accent)}
  .back-btn{padding:8px 18px;background:var(--surface);border:1px solid var(--border);border-radius:50px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:6px;transition:all .2s}
  .back-btn:hover{border-color:var(--accent);color:var(--accent)}

  .page{max-width:1000px;margin:0 auto;padding:40px 24px;position:relative;z-index:1}

  /* Hero band */
  .course-hero{
    background:var(--card);border:1px solid var(--border);border-radius:24px;
    padding:36px;margin-bottom:32px;
    background-image:radial-gradient(ellipse 80% 80% at 100% 50%, rgba(249,115,22,.08) 0%, transparent 60%);
  }
  .hero-tag{display:inline-block;padding:4px 12px;border-radius:50px;background:rgba(249,115,22,.15);border:1px solid rgba(249,115,22,.3);color:var(--accent);font-size:11px;font-weight:500;letter-spacing:.5px;text-transform:uppercase;margin-bottom:12px}
  .hero-title{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;letter-spacing:-.5px;margin-bottom:8px}
  .hero-meta{color:var(--muted);font-size:14px;display:flex;gap:16px;flex-wrap:wrap}
  .hero-meta span{display:flex;align-items:center;gap:5px}

  /* Subjects list */
  .section-head{font-family:'Syne',sans-serif;font-size:18px;font-weight:700;margin-bottom:18px;display:flex;align-items:center;gap:10px}
  .count-badge{background:var(--accent);color:#fff;font-size:12px;padding:2px 9px;border-radius:50px}

  .subject-list{display:flex;flex-direction:column;gap:12px}
  .subject-row{
    background:var(--card);border:1px solid var(--border);border-radius:16px;
    padding:20px 24px;display:flex;align-items:center;justify-content:space-between;
    transition:border-color .2s, transform .2s;
    animation:fadeIn .35s ease both;
  }
  .subject-row:hover{border-color:rgba(249,115,22,.3);transform:translateX(4px)}
  @keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}

  .subj-left{display:flex;align-items:center;gap:16px}
  .subj-icon{
    width:44px;height:44px;border-radius:12px;
    display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;
  }
  .subj-name{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;letter-spacing:-.2px}
  .subj-id{font-size:12px;color:var(--muted);margin-top:2px}
  .subj-right{color:var(--muted);font-size:18px}

  /* Empty */
  .empty{text-align:center;padding:64px 20px;background:var(--card);border:1px solid var(--border);border-radius:24px}
  .empty-icon{font-size:48px;margin-bottom:12px}
  .empty h3{font-family:'Syne',sans-serif;font-size:20px;margin-bottom:6px}
  .empty p{color:var(--muted);font-size:14px}

  /* icon colors */
  .ic-red{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.2)}
  .ic-blue{background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.2)}
  .ic-green{background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.2)}
  .ic-orange{background:rgba(249,115,22,.15);border:1px solid rgba(249,115,22,.2)}
  .ic-purple{background:rgba(168,85,247,.15);border:1px solid rgba(168,85,247,.2)}
</style>
</head>
<body>
<nav>
  <div class="nav-brand">
    <div class="nav-logo">P</div>
    <div class="nav-title">PW<span>Portal</span></div>
  </div>
  <a href="dashboard.php" class="back-btn">← Back to Courses</a>
</nav>

<div class="page">
  <div class="course-hero">
    <div class="hero-tag">📚 Batch Details</div>
    <div class="hero-title"><?= $batchName ?></div>
    <div class="hero-meta">
      <span>🆔 <?= htmlspecialchars($batchId) ?></span>
      <?php if (!empty($details['language'])): ?>
      <span>🌐 <?= htmlspecialchars($details['language']) ?></span>
      <?php endif; ?>
      <span>📖 <?= count($subjects) ?> Subject<?= count($subjects)!=1?'s':'' ?></span>
    </div>
  </div>

  <?php if (empty($subjects)): ?>
  <div class="empty">
    <div class="empty-icon">📭</div>
    <h3>No Subjects Found</h3>
    <p>This batch doesn't have any subjects yet.</p>
  </div>
  <?php else: ?>
  <div class="section-head">
    Subjects
    <span class="count-badge"><?= count($subjects) ?></span>
  </div>

  <?php
  $icons   = ['⚡','🔬','📐','🧬','🌍','🧮','📡','🔭','💡','🎯','🏆','🔥'];
  $classes = ['ic-red','ic-blue','ic-green','ic-orange','ic-purple'];
  ?>
  <div class="subject-list">
    <?php foreach ($subjects as $i => $subj):
      $sn = $subj['subject'] ?? 'Subject';
      $si = $subj['_id'] ?? '';
    ?>
    <div class="subject-row" style="animation-delay:<?= $i*50 ?>ms">
      <div class="subj-left">
        <div class="subj-icon <?= $classes[$i % count($classes)] ?>"><?= $icons[$i % count($icons)] ?></div>
        <div>
          <div class="subj-name"><?= htmlspecialchars($sn) ?></div>
          <div class="subj-id"><?= htmlspecialchars($si) ?></div>
        </div>
      </div>
      <div class="subj-right">→</div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
