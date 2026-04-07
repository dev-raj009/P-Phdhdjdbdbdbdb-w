<?php
require_once __DIR__ . '/config.php';
ini_set('session.name', SESSION_NAME);
session_start();
if (!empty($_SESSION['token'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PW Portal — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:       #08090d;
    --surface:  #10121a;
    --card:     #14161f;
    --border:   rgba(255,255,255,.07);
    --accent:   #f97316;
    --accent2:  #fb923c;
    --text:     #e8eaf0;
    --muted:    #6b7280;
    --success:  #22c55e;
    --danger:   #ef4444;
    --radius:   16px;
  }
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  html,body{height:100%;font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text)}

  /* ── Animated background ── */
  body::before{
    content:'';position:fixed;inset:0;
    background:
      radial-gradient(ellipse 60% 40% at 70% 20%, rgba(249,115,22,.12) 0%, transparent 60%),
      radial-gradient(ellipse 50% 50% at 20% 80%, rgba(249,115,22,.07) 0%, transparent 60%);
    pointer-events:none;z-index:0;
  }

  /* ── Grid noise overlay ── */
  body::after{
    content:'';position:fixed;inset:0;
    background-image:
      linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
    background-size:48px 48px;
    pointer-events:none;z-index:0;opacity:.5;
  }

  /* ── Layout ── */
  .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;z-index:1}

  .brand{
    position:fixed;top:24px;left:32px;display:flex;align-items:center;gap:10px;z-index:10;
  }
  .brand-logo{
    width:36px;height:36px;background:var(--accent);border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    font-family:'Syne',sans-serif;font-weight:800;font-size:18px;color:#fff;
    box-shadow:0 0 20px rgba(249,115,22,.4);
  }
  .brand-name{font-family:'Syne',sans-serif;font-weight:700;font-size:18px;letter-spacing:-.3px}
  .brand-name span{color:var(--accent)}

  /* ── Card ── */
  .card{
    width:100%;max-width:460px;
    background:var(--card);
    border:1px solid var(--border);
    border-radius:24px;
    padding:44px 40px;
    box-shadow:0 32px 80px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.04) inset;
    backdrop-filter:blur(20px);
    animation:slideUp .5s cubic-bezier(.22,1,.36,1) both;
  }
  @keyframes slideUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}

  .card-icon{
    width:56px;height:56px;border-radius:16px;
    background:linear-gradient(135deg,rgba(249,115,22,.25),rgba(249,115,22,.05));
    border:1px solid rgba(249,115,22,.3);
    display:flex;align-items:center;justify-content:center;
    font-size:24px;margin-bottom:24px;
  }
  h1{font-family:'Syne',sans-serif;font-size:26px;font-weight:800;letter-spacing:-.5px;margin-bottom:6px}
  .subtitle{color:var(--muted);font-size:14px;margin-bottom:32px}

  /* ── Tabs ── */
  .tabs{display:flex;gap:6px;background:var(--surface);border-radius:12px;padding:5px;margin-bottom:28px}
  .tab{
    flex:1;padding:9px 0;border:none;background:transparent;color:var(--muted);
    font-family:'DM Sans',sans-serif;font-size:13.5px;font-weight:500;cursor:pointer;
    border-radius:9px;transition:all .2s;
  }
  .tab.active{background:var(--card);color:var(--text);box-shadow:0 2px 8px rgba(0,0,0,.4)}

  /* ── Inputs ── */
  .form-group{margin-bottom:18px}
  label{display:block;font-size:13px;font-weight:500;color:var(--muted);margin-bottom:7px;letter-spacing:.3px;text-transform:uppercase}
  .input-wrap{position:relative}
  input{
    width:100%;padding:13px 16px;
    background:var(--surface);border:1px solid var(--border);
    border-radius:12px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:15px;
    outline:none;transition:border-color .2s, box-shadow .2s;
  }
  input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(249,115,22,.15)}
  input::placeholder{color:#4b5563}

  /* OTP row */
  .otp-row{display:flex;gap:10px}
  .otp-row input{flex:1}
  .otp-row .btn-send{
    white-space:nowrap;padding:13px 18px;font-size:13.5px;
    background:var(--surface);border:1px solid var(--border);
    border-radius:12px;color:var(--text);cursor:pointer;font-family:'DM Sans',sans-serif;
    transition:all .2s;flex-shrink:0;
  }
  .otp-row .btn-send:hover{border-color:var(--accent);color:var(--accent)}
  .otp-row .btn-send:disabled{opacity:.5;cursor:not-allowed}

  /* ── Submit ── */
  .btn-primary{
    width:100%;padding:14px;margin-top:6px;
    background:var(--accent);border:none;border-radius:12px;
    color:#fff;font-family:'Syne',sans-serif;font-size:15px;font-weight:700;
    cursor:pointer;letter-spacing:.3px;
    box-shadow:0 8px 24px rgba(249,115,22,.35);
    transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;
  }
  .btn-primary:hover{background:#ea6c0a;transform:translateY(-1px);box-shadow:0 12px 32px rgba(249,115,22,.45)}
  .btn-primary:active{transform:none}
  .btn-primary:disabled{opacity:.6;cursor:not-allowed;transform:none}

  /* ── Alert ── */
  .alert{
    padding:12px 16px;border-radius:10px;font-size:14px;margin-bottom:16px;
    display:none;align-items:center;gap:8px;
  }
  .alert.show{display:flex}
  .alert-error{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
  .alert-success{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:#86efac}

  /* ── Divider ── */
  .divider{display:flex;align-items:center;gap:12px;color:var(--muted);font-size:12px;margin:20px 0}
  .divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border)}

  /* OTP sent hint */
  .otp-hint{font-size:12px;color:var(--muted);margin-top:8px;display:none}
  .otp-hint.show{display:block}

  /* spinner */
  .spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:none}
  .btn-primary.loading .spinner{display:inline-block}
  .btn-primary.loading .btn-text{opacity:.7}
  @keyframes spin{to{transform:rotate(360deg)}}

  /* footer */
  .card-footer{text-align:center;margin-top:24px;font-size:13px;color:var(--muted)}

  /* countdown */
  #countdown{display:none;font-size:13px;color:var(--muted);text-align:right;margin-top:6px}
</style>
</head>
<body>
<div class="brand">
  <div class="brand-logo">P</div>
  <div class="brand-name">PW<span>Portal</span></div>
</div>

<div class="page">
  <div class="card">
    <div class="card-icon">🎓</div>
    <h1>Welcome Back</h1>
    <p class="subtitle">Access your Physics Wallah courses securely</p>

    <div class="tabs">
      <button class="tab active" onclick="switchTab('token')">🔑 Token Login</button>
      <button class="tab" onclick="switchTab('otp')">📱 OTP Login</button>
    </div>

    <div id="alert" class="alert"></div>

    <!-- ── TOKEN TAB ── -->
    <div id="tab-token">
      <div class="form-group">
        <label>Access Token</label>
        <input type="password" id="token" placeholder="Paste your PW access token here…" autocomplete="off">
      </div>
      <button class="btn-primary" onclick="tokenLogin()">
        <span class="btn-text">Login with Token</span>
        <div class="spinner"></div>
      </button>
    </div>

    <!-- ── OTP TAB ── -->
    <div id="tab-otp" style="display:none">
      <div class="form-group">
        <label>Mobile Number</label>
        <div class="otp-row">
          <input type="tel" id="mobile" placeholder="10-digit mobile number" maxlength="10">
          <button class="btn-send" id="btnSendOtp" onclick="sendOTP()">Send OTP</button>
        </div>
      </div>
      <p class="otp-hint" id="otpHint">✅ OTP sent! Check your registered mobile.</p>
      <div id="countdown"></div>

      <div class="form-group" id="otpGroup" style="display:none">
        <label>Enter OTP</label>
        <input type="text" id="otp" placeholder="6-digit OTP" maxlength="6" inputmode="numeric">
      </div>
      <button class="btn-primary" id="btnVerifyOtp" onclick="verifyOTP()" style="display:none">
        <span class="btn-text">Verify & Login</span>
        <div class="spinner"></div>
      </button>
    </div>

    <div class="card-footer">🔒 Secured by end-to-end encrypted session</div>
  </div>
</div>

<script>
function switchTab(t){
  document.querySelectorAll('.tab').forEach((b,i)=>b.classList.toggle('active', (i===0&&t==='token')||(i===1&&t==='otp')));
  document.getElementById('tab-token').style.display = t==='token'?'block':'none';
  document.getElementById('tab-otp').style.display   = t==='otp'  ?'block':'none';
  hideAlert();
}

function showAlert(msg, type='error'){
  const el = document.getElementById('alert');
  el.className = 'alert show alert-'+(type==='success'?'success':'error');
  el.innerHTML = (type==='error'?'⚠️ ':'✅ ') + msg;
}
function hideAlert(){ document.getElementById('alert').className='alert'; }

function setLoading(btn, loading){
  if(loading){ btn.classList.add('loading'); btn.disabled=true; }
  else { btn.classList.remove('loading'); btn.disabled=false; }
}

async function tokenLogin(){
  const token = document.getElementById('token').value.trim();
  if(!token){ showAlert('Please enter your access token'); return; }
  const btn = document.querySelector('#tab-token .btn-primary');
  setLoading(btn, true); hideAlert();
  const res = await post({action:'token_login', token});
  setLoading(btn, false);
  if(res.success){ showAlert('Login successful! Redirecting…','success'); setTimeout(()=>location.href=res.redirect, 800); }
  else showAlert(res.message||'Login failed');
}

async function sendOTP(){
  const mobile = document.getElementById('mobile').value.trim();
  if(!/^\d{10}$/.test(mobile)){ showAlert('Enter a valid 10-digit mobile number'); return; }
  const btn = document.getElementById('btnSendOtp');
  btn.disabled=true; hideAlert();
  const res = await post({action:'send_otp', mobile});
  if(res.success){
    showAlert('OTP sent to your mobile!','success');
    document.getElementById('otpHint').classList.add('show');
    document.getElementById('otpGroup').style.display='block';
    document.getElementById('btnVerifyOtp').style.display='flex';
    startCountdown(btn, 60);
  } else {
    showAlert(res.message||'Failed to send OTP');
    btn.disabled=false;
  }
}

function startCountdown(btn, sec){
  const cd = document.getElementById('countdown');
  cd.style.display='block';
  const t = setInterval(()=>{
    cd.textContent = `Resend OTP in ${sec}s`;
    sec--;
    if(sec<0){ clearInterval(t); cd.style.display='none'; btn.disabled=false; btn.textContent='Resend OTP'; }
  },1000);
}

async function verifyOTP(){
  const otp = document.getElementById('otp').value.trim();
  if(!/^\d{4,8}$/.test(otp)){ showAlert('Enter a valid OTP'); return; }
  const btn = document.getElementById('btnVerifyOtp');
  setLoading(btn,true); hideAlert();
  const res = await post({action:'verify_otp', otp});
  setLoading(btn,false);
  if(res.success){ showAlert('Verified! Redirecting…','success'); setTimeout(()=>location.href=res.redirect,800); }
  else showAlert(res.message||'OTP verification failed');
}

async function post(data){
  try{
    const r = await fetch('auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
    return await r.json();
  }catch(e){ return {success:false, message:'Network error. Please try again.'}; }
}

// Enter key support
document.addEventListener('keydown',e=>{
  if(e.key==='Enter'){
    if(document.getElementById('tab-token').style.display!=='none') tokenLogin();
    else if(document.getElementById('otp').value) verifyOTP();
    else sendOTP();
  }
});
</script>
</body>
</html>
