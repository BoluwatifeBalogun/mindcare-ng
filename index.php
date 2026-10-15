<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$u = current_user();
if ($u) { header('Location: ' . ($u['role'] === 'student' ? 'student.php' : ($u['role'] === 'counsellor' ? 'counsellor.php' : 'admin.php'))); exit; }
page_head('Welcome');
?>
<div class="mc-content auth-wrap">
  <aside class="auth-side">
    <div class="brandrow">
      <span class="orb breathe" style="width:40px;height:40px"></span>
      <span>
        <span class="mc-serif fw-semibold d-block" style="font-size:1.15rem;line-height:1.1">MindCare NG</span>
        <span class="unitline d-block">Student Counselling Unit · Yaba College of Technology</span>
      </span>
    </div>
    <h1 class="mc-serif fw-semibold">It's okay to not be okay.<br>Talk to someone today.</h1>
    <p class="lede">Amara listens first, any hour of the day, and connects you with a real campus counsellor whenever you need one. Everything you share stays confidential.</p>
    <div class="ill-frame msg-in">
      <img src="assets/img/hero-counselling.png" alt="Illustration of a student talking with a campus counsellor">
    </div>
    <ul class="trust-list">
      <li>Confidential by default, between you and your care team</li>
      <li>Licensed campus counsellors review every referral personally</li>
      <li>Crisis support any hour: emergency line 112, urgent same-week sessions</li>
    </ul>
  </aside>

  <main class="auth-main">
    <div class="glass-deep text-start" id="authCard">
      <div class="mc-serif fw-semibold fs-4 mb-1">Welcome</div>
      <div class="cardkick">Sign in with your matric number, or create your student account.</div>
      <div class="d-flex gap-2 mb-3">
        <button class="tabbtn active flex-fill" id="tabLogin" onclick="mode('login')">Sign in</button>
        <button class="tabbtn flex-fill" id="tabReg" onclick="mode('reg')">Student registration</button>
      </div>
      <div id="err" class="alert alert-danger py-2 d-none" style="border-radius:12px;font-size:.85rem"></div>

      <form id="loginForm" onsubmit="return doLogin(event)">
        <input class="form-control mb-2" id="l_user" placeholder="Matric or staff username" required>
        <input class="form-control mb-3" id="l_pass" type="password" placeholder="Password" required>
        <button class="btn glow-btn w-100 py-2">Continue</button>
      </form>

      <form id="regForm" class="d-none" onsubmit="return doReg(event)">
        <input class="form-control mb-2" id="r_name" placeholder="Full name" required>
        <input class="form-control mb-2" id="r_matric" placeholder="Matric number" required>
        <input class="form-control mb-2" id="r_dept" placeholder="Department" required>
        <select class="form-select mb-2" id="r_level"><option>ND I</option><option>ND II</option><option>HND I</option><option selected>HND II</option></select>
        <input class="form-control mb-3" id="r_pass" type="password" placeholder="Choose a password (min 8 chars)" minlength="8" required>
        <button class="btn glow-btn w-100 py-2">Register & continue</button>
        <div class="text-center mt-2" style="font-size:.72rem;color:rgba(34,52,44,.6)">Only used to personalise support. Your conversations stay confidential.</div>
      </form>
    </div>
    <div class="trust-mini"><span>Confidential</span><span>Licensed counsellors</span><span>Crisis line 112</span></div>
    <div class="auth-foot">HND Seminar Project · Dept. of Computer Science, YabaTech · 2026</div>
  </main>
</div>
<script>
function mode(m){
  document.getElementById('loginForm').classList.toggle('d-none', m!=='login');
  document.getElementById('regForm').classList.toggle('d-none', m!=='reg');
  document.getElementById('tabLogin').classList.toggle('active', m==='login');
  document.getElementById('tabReg').classList.toggle('active', m==='reg');
  document.getElementById('err').classList.add('d-none');
}
async function post(url, data){
  const r = await fetch(url, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)});
  const j = await r.json();
  if(!r.ok) throw new Error(j.error || 'Something went wrong');
  return j;
}
function fail(msg){ const e=document.getElementById('err'); e.textContent=msg; e.classList.remove('d-none'); }
async function doLogin(ev){ ev.preventDefault();
  try{ const j = await post('api/login.php', {username:l_user.value, password:l_pass.value});
    location.href = j.user.role==='student'?'student.php':(j.user.role==='counsellor'?'counsellor.php':'admin.php');
  }catch(e){ fail(e.message); } return false; }
async function doReg(ev){ ev.preventDefault();
  try{ await post('api/register.php', {name:r_name.value, matric:r_matric.value, dept:r_dept.value, level:r_level.value, password:r_pass.value});
    location.href='student.php';
  }catch(e){ fail(e.message); } return false; }
</script>
</body></html>
