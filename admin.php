<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$u = require_page_role('admin');
$stats = [
  'students' => (int)db()->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
  'referrals' => (int)db()->query('SELECT COUNT(*) FROM referrals')->fetchColumn(),
  'sessions' => (int)db()->query("SELECT COUNT(*) FROM chat_messages WHERE role='user'")->fetchColumn(),
];
$usage = db()->query("SELECT DATE_FORMAT(created_at, '%b') AS m, COUNT(*) AS c FROM chat_messages WHERE role='user' GROUP BY DATE_FORMAT(created_at, '%Y-%m'), m ORDER BY MIN(created_at) LIMIT 12")->fetchAll();
page_head('Admin');
?>
<div class="mc-content container" style="max-width:760px">
  <?php page_header($u); ?>
  <div class="row g-2 mb-3">
    <?php foreach ([[$stats['students'], 'Students'], [$stats['referrals'], 'Referrals'], [$stats['sessions'], 'AI messages']] as [$v, $l]): ?>
      <div class="col-4"><div class="glass p-3 text-center">
        <div class="mc-serif fw-bold fs-4" style="color:#5B21B6"><?= $v ?></div>
        <div style="font-size:.7rem;font-weight:700;color:rgba(65,41,127,.6)"><?= $l ?></div></div></div>
    <?php endforeach; ?>
  </div>
  <div class="glass p-4 mb-3">
    <div class="d-flex align-items-center mb-2">
      <h4 class="mc-serif fs-6 fw-semibold mb-0">AI counselling messages per month</h4>
      <a class="btn btn-sm ms-auto px-3" style="background:#EDE9FE;color:#5B21B6;border-radius:12px;font-weight:700" href="api/report.php">Export report</a>
    </div>
    <canvas id="usage" height="150"></canvas>
  </div>
  <div class="glass p-4 mb-3">
    <h4 class="mc-serif fs-6 fw-semibold mb-2">System settings</h4>
    <label class="form-label" style="font-size:.75rem;font-weight:700;color:rgba(65,41,127,.7)">Emergency line shown to students</label>
    <input id="s_line" class="form-control mb-2">
    <label class="form-label" style="font-size:.75rem;font-weight:700;color:rgba(65,41,127,.7)">Campus counselling centre details</label>
    <input id="s_centre" class="form-control mb-2">
    <label class="form-label" style="font-size:.75rem;font-weight:700;color:rgba(65,41,127,.7)">Auto follow-up window (hours)</label>
    <input id="s_hours" type="number" min="1" class="form-control mb-3">
    <button class="btn glow-btn px-4 py-2" id="saveBtn" onclick="saveSettings()">Save settings</button>
  </div>
  <div class="glass p-4"><h4 class="mc-serif fs-6 fw-semibold mb-2">User management</h4><div id="userList"></div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const $=id=>document.getElementById(id);
const esc=s=>{const d=document.createElement('div');d.textContent=s??'';return d.innerHTML};
async function api(url,data){const o=data?{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}:{};
  const r=await fetch(url,o);const j=await r.json().catch(()=>({}));if(!r.ok)throw new Error(j.error||'Request failed');return j;}
new Chart($('usage'),{type:'bar',data:{labels:<?= json_encode(array_column($usage, 'm')) ?>,
  datasets:[{data:<?= json_encode(array_map('intval', array_column($usage, 'c'))) ?>,backgroundColor:'#7C5CE0',borderRadius:8}]},
  options:{plugins:{legend:{display:false}}}});
(async()=>{
  const s=(await api('api/settings.php')).settings;
  $('s_line').value=s.crisis_line||'112'; $('s_centre').value=s.centre||''; $('s_hours').value=s.follow_up_hours||48;
  const uj=await api('api/users.php');
  $('userList').innerHTML=uj.users.map(x=>`<div class="d-flex gap-2 align-items-center p-2 mb-1" style="background:rgba(255,255,255,.6);border-radius:14px;font-size:.88rem">
    <div class="flex-fill"><b>${esc(x.name)}</b><div style="font-size:.72rem;color:rgba(65,41,127,.6)">${esc(x.username)} · ${esc(x.title||x.dept||'')}</div></div>
    <span class="pill ${x.role==='student'?'pill-violet':x.role==='counsellor'?'pill-green':'pill-amber'}">${esc(x.role)}</span></div>`).join('');
})();
async function saveSettings(){
  await api('api/settings.php',{crisis_line:$('s_line').value,centre:$('s_centre').value,follow_up_hours:$('s_hours').value});
  $('saveBtn').textContent='Saved ✓'; setTimeout(()=>$('saveBtn').textContent='Save settings',2000);
}
</script>
</body></html>
