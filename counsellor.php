<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$u = require_page_role('counsellor');
page_head('Counsellor');
?>
<div class="mc-content container" style="max-width:760px">
  <?php page_header($u); ?>
  <div class="d-flex gap-2 mb-3">
    <button class="tabbtn active" data-t="ref" onclick="ctab('ref')">Referrals <span id="openCount" class="pill pill-red d-none"></span></button>
    <button class="tabbtn" data-t="ap" onclick="ctab('ap')">Appointments</button>
  </div>
  <section id="sec-ref"></section>
  <section id="sec-ap" class="d-none"></section>
</div>
<script>
const $=id=>document.getElementById(id);
const esc=s=>{const d=document.createElement('div');d.textContent=s??'';return d.innerHTML};
async function api(url,data){const o=data?{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}:{};
  const r=await fetch(url,o);const j=await r.json().catch(()=>({}));if(!r.ok)throw new Error(j.error||'Request failed');return j;}
function ctab(t){['ref','ap'].forEach(k=>{$('sec-'+k).classList.toggle('d-none',k!==t);
  document.querySelector(`[data-t="${k}"]`).classList.toggle('active',k===t)});
  t==='ref'?loadRefs():loadAppts();}

async function loadRefs(){
  const j=await api('api/referrals.php');
  const open=j.referrals.filter(r=>r.status==='open').length;
  $('openCount').textContent=open; $('openCount').classList.toggle('d-none',!open);
  $('sec-ref').innerHTML=j.referrals.map(r=>{
    const ctx=r.context_json?JSON.parse(r.context_json):null;
    return `<div class="glass p-4 mb-3 ${r.risk==='high'&&r.status==='open'?'risk-ring':''}">
      <div class="d-flex gap-2 align-items-center flex-wrap">
        <b>${esc(r.student)}</b>
        <span class="pill ${r.risk==='high'?'pill-red':'pill-amber'}">${r.risk==='high'?'High risk':'Follow up'}</span>
        <span class="ms-auto" style="font-size:.75rem;color:rgba(65,41,127,.6)">${esc(r.time)}</span></div>
      <p class="mt-2 mb-1" style="font-size:.9rem">${esc(r.summary)}</p>
      <div style="font-size:.75rem;color:rgba(65,41,127,.6)">Source: ${esc(r.source)}</div>
      <div id="ctx-${r.id}" class="d-none mt-2 p-3" style="background:rgba(255,255,255,.7);border-radius:16px">
        ${ctx?ctx.map(m=>`<div style="font-size:.88rem"><b style="color:${m.role==='user'?'#6D28D9':'#047857'}">${m.role==='user'?esc(r.student.split(' ')[0]):'Amara'}:</b> ${esc(m.content||m.text)}</div>`).join(''):'<div style="font-size:.85rem;color:rgba(65,41,127,.7)">No conversation excerpt attached (assessment-based referral).</div>'}
        <div class="d-flex gap-2 mt-2">
          <input id="rep-${r.id}" class="form-control form-control-sm" placeholder="Send a note to ${esc(r.student.split(' ')[0])}…">
          <button class="btn btn-sm px-3 text-white" style="background:#059669;border-radius:12px" onclick="sendNote(${r.id},${r.student_id})">Send</button>
        </div>
        <div style="font-size:.68rem;color:rgba(65,41,127,.6)" class="mt-1">Your note appears inside the student's chat, clearly marked as from you.</div>
      </div>
      <div class="d-flex gap-2 mt-3 flex-wrap">
        ${r.status==='open'
          ? `<button class="btn glow-btn btn-sm px-3 py-2" onclick="takeCase(${r.id})">Take case</button>
             <button class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,.7);border-radius:14px;font-weight:600;color:#5B21B6" onclick="$('ctx-${r.id}').classList.toggle('d-none')">Session context & reply</button>`
          : `<span class="pill pill-green">In progress</span>
             <button class="btn btn-sm px-3 py-1" style="background:rgba(255,255,255,.7);border-radius:14px;font-weight:600;color:#5B21B6" onclick="$('ctx-${r.id}').classList.toggle('d-none')">Context & reply</button>`}
      </div></div>`;
  }).join('')||'<div class="glass p-4" style="font-size:.9rem;color:rgba(65,41,127,.7)">No referrals yet. High-risk cases from Amara and assessments appear here automatically.</div>';
}
async function takeCase(id){ await api('api/referrals.php',{action:'take',id}); loadRefs(); }
async function sendNote(rid,sid){
  const inp=$('rep-'+rid); if(!inp.value.trim())return;
  await api('api/referrals.php',{action:'reply',student_id:sid,text:inp.value}); inp.value='';
  inp.placeholder='Sent ✓ — it now shows in their chat';
}
async function loadAppts(){
  const j=await api('api/appointments.php');
  $('sec-ap').innerHTML=j.appointments.map(a=>`<div class="glass p-3 mb-2" style="font-size:.9rem">
    <div class="d-flex gap-2 align-items-center flex-wrap"><b>${esc(a.student)}</b>
      <span class="pill ${a.status==='confirmed'?'pill-green':'pill-amber'}">${esc(a.status)}</span>
      ${a.is_follow_up==1?'<span class="pill pill-violet">follow-up</span>':''}</div>
    <div class="mt-1">${esc(a.when_text)} · <span style="color:rgba(65,41,127,.7)">${esc(a.reason||'')}</span></div>
    ${a.status==='pending'?`<button class="btn glow-btn btn-sm px-3 py-2 mt-2" onclick="confirmAp(${a.id})">Confirm slot</button>`:''}</div>`).join('')
    ||'<div class="glass p-4" style="font-size:.9rem;color:rgba(65,41,127,.7)">No appointments yet.</div>';
}
async function confirmAp(id){ await api('api/appointments.php',{action:'confirm',id}); loadAppts(); }
loadRefs();
</script>
</body></html>
