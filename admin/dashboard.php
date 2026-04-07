<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

ini_set('session.name', SESSION_NAME . '_admin');
session_start();

// Auth guard
if (empty($_SESSION['admin_token']) || !DB::validateAdminSession($_SESSION['admin_token'])) {
    header('Location: index.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    DB::deleteAdminSession($_SESSION['admin_token']);
    session_destroy();
    header('Location: index.php');
    exit;
}

$users  = DB::allUsers();
$total  = count($users);
$active = array_filter($users, fn($u) => !empty($u['access_token']));
$today  = array_filter($users, fn($u) => date('Y-m-d', $u['last_login'] ?? 0) === date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — PW Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#060709;--surface:#0d0f14;--card:#111318;--border:rgba(255,255,255,.06);
    --accent:#dc2626;--accent2:#f97316;--text:#e8eaf0;--muted:#6b7280;
    --success:#22c55e;--warn:#f59e0b;
  }
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  html,body{min-height:100%;font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text)}
  body::before{content:'';position:fixed;inset:0;
    background:radial-gradient(ellipse 50% 30% at 80% 0%,rgba(220,38,38,.08) 0%,transparent 60%);
    pointer-events:none;z-index:0}

  /* ── Sidebar ── */
  .layout{display:flex;min-height:100vh}
  aside{
    width:220px;flex-shrink:0;background:var(--card);
    border-right:1px solid var(--border);
    padding:24px 0;display:flex;flex-direction:column;
    position:fixed;top:0;left:0;height:100vh;z-index:50;
  }
  .side-logo{
    display:flex;align-items:center;gap:10px;padding:0 20px 24px;
    border-bottom:1px solid var(--border);margin-bottom:16px;
  }
  .logo-box{
    width:36px;height:36px;background:var(--accent);border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    font-family:'Syne',sans-serif;font-weight:800;font-size:16px;color:#fff;
    box-shadow:0 0 16px rgba(220,38,38,.4);
  }
  .logo-text{font-family:'Syne',sans-serif;font-weight:700;font-size:16px}
  .logo-text span{color:var(--accent)}
  .side-label{font-size:10px;color:var(--muted);letter-spacing:.8px;text-transform:uppercase;padding:0 20px 8px}
  .nav-item{
    display:flex;align-items:center;gap:10px;padding:10px 20px;
    color:var(--muted);font-size:14px;font-weight:500;cursor:pointer;
    border-left:2px solid transparent;transition:all .2s;text-decoration:none;
  }
  .nav-item:hover{color:var(--text);background:rgba(255,255,255,.03)}
  .nav-item.active{color:var(--accent);border-left-color:var(--accent);background:rgba(220,38,38,.06)}
  .side-spacer{flex:1}
  .side-user{padding:16px 20px;border-top:1px solid var(--border);font-size:13px;color:var(--muted)}
  .side-user strong{display:block;color:var(--text);margin-bottom:2px}

  /* ── Main ── */
  main{margin-left:220px;flex:1;padding:32px;position:relative;z-index:1}

  .page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px}
  .page-head h1{font-family:'Syne',sans-serif;font-size:26px;font-weight:800}
  .page-head p{color:var(--muted);font-size:14px;margin-top:3px}

  .logout-btn{
    padding:8px 18px;background:transparent;border:1px solid rgba(220,38,38,.4);
    border-radius:50px;color:var(--accent);font-size:13px;cursor:pointer;
    font-family:'DM Sans',sans-serif;transition:all .2s;text-decoration:none;
  }
  .logout-btn:hover{background:rgba(220,38,38,.1)}

  /* ── Stats ── */
  .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:16px;margin-bottom:28px}
  .stat{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:20px}
  .stat-icon{font-size:24px;margin-bottom:10px}
  .stat-val{font-family:'Syne',sans-serif;font-size:28px;font-weight:800}
  .stat-lbl{color:var(--muted);font-size:12px;margin-top:2px}

  /* ── Table ── */
  .table-wrap{background:var(--card);border:1px solid var(--border);border-radius:20px;overflow:hidden}
  .table-head{
    padding:20px 24px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;
  }
  .table-title{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;display:flex;align-items:center;gap:10px}
  .badge{background:var(--accent);color:#fff;font-size:11px;padding:2px 9px;border-radius:50px}

  /* search */
  .search{
    padding:8px 14px;background:var(--surface);border:1px solid var(--border);
    border-radius:50px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;
    outline:none;width:220px;transition:border-color .2s;
  }
  .search:focus{border-color:rgba(220,38,38,.4)}

  table{width:100%;border-collapse:collapse}
  th{padding:12px 20px;text-align:left;font-size:11px;font-weight:500;color:var(--muted);letter-spacing:.5px;text-transform:uppercase;border-bottom:1px solid var(--border);white-space:nowrap}
  td{padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.03);font-size:13.5px;vertical-align:middle}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:rgba(255,255,255,.02)}

  /* token cell */
  .token-cell{display:flex;align-items:center;gap:8px}
  .token-val{
    font-family:'DM Mono',monospace;font-size:11.5px;color:#9ca3af;
    background:var(--surface);padding:4px 10px;border-radius:6px;
    max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
    cursor:pointer;border:1px solid var(--border);transition:border-color .2s;
  }
  .token-val:hover{border-color:var(--accent2);color:var(--text)}
  .copy-btn{
    width:28px;height:28px;border:1px solid var(--border);background:var(--surface);
    border-radius:7px;cursor:pointer;display:flex;align-items:center;justify-content:center;
    font-size:13px;transition:all .2s;flex-shrink:0;
  }
  .copy-btn:hover{border-color:var(--accent2);background:rgba(249,115,22,.1)}

  .mobile-val{font-family:'DM Mono',monospace;color:#a1a1aa}
  .name-val{font-weight:500}
  .date-val{color:var(--muted);font-size:12px}

  .status-badge{
    display:inline-block;padding:3px 9px;border-radius:50px;font-size:11px;font-weight:500;
  }
  .status-active{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:#86efac}
  .status-no-token{background:rgba(107,114,128,.1);border:1px solid rgba(107,114,128,.2);color:var(--muted)}

  /* empty */
  .empty-row td{text-align:center;padding:60px 20px;color:var(--muted)}
  .empty-row .empty-ico{font-size:40px;display:block;margin-bottom:8px}

  /* toast */
  #toast{
    position:fixed;bottom:24px;right:24px;
    background:#111318;border:1px solid rgba(34,197,94,.4);color:#86efac;
    padding:12px 20px;border-radius:12px;font-size:14px;z-index:9999;
    transform:translateY(20px);opacity:0;transition:all .3s;pointer-events:none;
  }
  #toast.show{transform:none;opacity:1}
</style>
</head>
<body>
<div class="layout">
  <!-- Sidebar -->
  <aside>
    <div class="side-logo">
      <div class="logo-box">P</div>
      <div class="logo-text">PW<span>Admin</span></div>
    </div>
    <div class="side-label">Main</div>
    <a href="dashboard.php" class="nav-item active">👥 All Users</a>
    <div class="side-spacer"></div>
    <div class="side-user">
      <strong>admin</strong>
      Admin Account
    </div>
  </aside>

  <!-- Main -->
  <main>
    <div class="page-head">
      <div>
        <h1>User Management</h1>
        <p>All registered users, tokens, and session data</p>
      </div>
      <a href="?logout=1" class="logout-btn" onclick="return confirm('Logout from admin?')">🔒 Logout</a>
    </div>

    <!-- Stats -->
    <div class="stats">
      <div class="stat">
        <div class="stat-icon">👥</div>
        <div class="stat-val"><?= $total ?></div>
        <div class="stat-lbl">Total Users</div>
      </div>
      <div class="stat">
        <div class="stat-icon">🔑</div>
        <div class="stat-val"><?= count($active) ?></div>
        <div class="stat-lbl">With Active Token</div>
      </div>
      <div class="stat">
        <div class="stat-icon">📅</div>
        <div class="stat-val"><?= count($today) ?></div>
        <div class="stat-lbl">Logins Today</div>
      </div>
      <div class="stat">
        <div class="stat-icon">🕐</div>
        <div class="stat-val"><?= date('H:i') ?></div>
        <div class="stat-lbl">Current Time (IST)</div>
      </div>
    </div>

    <!-- Table -->
    <div class="table-wrap">
      <div class="table-head">
        <div class="table-title">
          Users <span class="badge"><?= $total ?></span>
        </div>
        <input class="search" type="text" id="searchInput" placeholder="🔍  Search users…" oninput="filterTable()">
      </div>

      <table id="userTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Mobile</th>
            <th>Email</th>
            <th>Access Token</th>
            <th>Refresh Token</th>
            <th>Org ID</th>
            <th>Status</th>
            <th>Last Login</th>
            <th>Registered</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
          <tr class="empty-row"><td colspan="10"><span class="empty-ico">📭</span>No users yet</td></tr>
          <?php endif; ?>
          <?php foreach ($users as $i => $u): ?>
          <tr>
            <td style="color:var(--muted)"><?= $u['id'] ?></td>
            <td class="name-val"><?= htmlspecialchars($u['name'] ?: '—') ?></td>
            <td class="mobile-val"><?= htmlspecialchars($u['mobile'] ?: '—') ?></td>
            <td style="font-size:13px;color:#9ca3af"><?= htmlspecialchars($u['email'] ?: '—') ?></td>

            <!-- Access Token -->
            <td>
              <?php if ($u['access_token']): ?>
              <div class="token-cell">
                <span class="token-val" title="<?= htmlspecialchars($u['access_token']) ?>"><?= htmlspecialchars(substr($u['access_token'],0,28)).'…' ?></span>
                <button class="copy-btn" onclick="copyToken('<?= addslashes(htmlspecialchars($u['access_token'])) ?>')" title="Copy full token">📋</button>
              </div>
              <?php else: echo '<span style="color:var(--muted)">—</span>'; endif; ?>
            </td>

            <!-- Refresh Token -->
            <td>
              <?php if ($u['refresh_token']): ?>
              <div class="token-cell">
                <span class="token-val" title="<?= htmlspecialchars($u['refresh_token']) ?>"><?= htmlspecialchars(substr($u['refresh_token'],0,20)).'…' ?></span>
                <button class="copy-btn" onclick="copyToken('<?= addslashes(htmlspecialchars($u['refresh_token'])) ?>')" title="Copy refresh token">📋</button>
              </div>
              <?php else: echo '<span style="color:var(--muted)">—</span>'; endif; ?>
            </td>

            <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($u['org_id'] ?: '—') ?></td>

            <!-- Status -->
            <td>
              <?php if ($u['access_token']): ?>
              <span class="status-badge status-active">✓ Active</span>
              <?php else: ?>
              <span class="status-badge status-no-token">No Token</span>
              <?php endif; ?>
            </td>

            <td class="date-val"><?= $u['last_login'] ? date('d M Y H:i', $u['last_login']) : '—' ?></td>
            <td class="date-val"><?= $u['created_at'] ? date('d M Y', $u['created_at']) : '—' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<div id="toast">✅ Token copied to clipboard!</div>

<script>
function copyToken(token) {
  // Decode HTML entities
  const txt = document.createElement('textarea');
  txt.innerHTML = token;
  const decoded = txt.value;

  navigator.clipboard.writeText(decoded).then(() => {
    const t = document.getElementById('toast');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
  });
}

function filterTable() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('#userTable tbody tr:not(.empty-row)').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>
</body>
</html>
