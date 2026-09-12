<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$u = require_page_role('student');
$counsellors = db()->query("SELECT id, name, title FROM users WHERE role = 'counsellor' ORDER BY name")->fetchAll();
page_head('Your space');
?>
<div class="mc-content container" style="max-width:760px">
  <?php page_header($u); ?>

  <div class="glass-deep p-1 d-flex mb-3" style="border-radius:20px">
    <?php foreach (['chat' => 'Talk', 'mood' => 'Mood', 'assess' => 'Check-in', 'sessions' => 'Sessions', 'history' => 'History'] as $k => $t): ?>
      <button class="tabbtn flex-fill <?= $k === 'chat' ? 'active' : '' ?>" data-tab="<?= $k ?>" onclick="showTab('<?= $k ?>')"><?= $t ?></button>
    <?php endforeach; ?>
  </div>

  <!-- TALK -->
  <section id="tab-chat">
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="orb breathe" id="amaraOrb" style="width:44px;height:44px"></span>
      <div><div class="mc-serif fw-semibold">Amara</div>
        <div style="font-size:.75rem;color:#6D28D9">● Here for you, 24/7 · Confidential</div></div>
    </div>
    <div id="chatBox" class="d-flex flex-column gap-2 mb-2" style="min-height:40vh;max-height:52vh;overflow-y:auto"></div>
    <div id="reviewBanner" class="glass p-3 mb-2 d-none msg-in" style="border:1px solid #FDE68A;background:rgba(255,251,235,.85)">
      <div class="d-flex align-items-start gap-2">
        <div class="flex-fill" style="font-size:.9rem">Sounds like a lot is sitting on you. If it would help to talk it through with a person, a counsellor can see you this week.</div>
        <button class="btn btn-sm glow-btn px-3" onclick="showTab('sessions')">Book</button>
        <button class="btn btn-sm" onclick="reviewBanner.classList.add('d-none')" aria-label="Dismiss">✕</button>
      </div>
    </div>
    <form class="glass-deep p-2 d-flex gap-2 align-items-end" onsubmit="return sendMsg(event)">
      <textarea id="msgInput" class="form-control border-0 bg-transparent" rows="1" placeholder="Type what's on your mind…" style="resize:none;box-shadow:none!important"></textarea>
      <button class="btn glow-btn px-3 py-2" id="sendBtn" aria-label="Send">Send</button>
    </form>
    <p class="text-center mt-2" style="font-size:.7rem;color:rgba(109,40,217,.7)">Amara supports you but doesn't replace a professional counsellor. In an emergency call <span id="crisisLineNote">112</span>.</p>
  </section>

  <!-- MOOD -->
  <section id="tab-mood" class="d-none">
    <div class="glass p-4 mb-3">
      <h3 class="mc-serif fs-5 fw-semibold">How are you feeling today?</h3>
      <div class="d-flex justify-content-between mt-3" id="moodRow"></div>
      <input id="moodNote" class="form-control mt-3" placeholder="Add a note (optional)…">
      <button class="btn glow-btn w-100 mt-3 py-2" onclick="saveMood()">Save check-in</button>
    </div>
    <div class="glass p-4"><h4 class="mc-serif fs-6 fw-semibold mb-2">Your fortnight</h4><canvas id="moodChart" height="160"></canvas>
      <p style="font-size:.75rem;color:rgba(65,41,127,.6)" class="mt-2 mb-0">Your counsellor can see this trend if you're referred, so support meets you where you are.</p></div>
  </section>

  <!-- CHECK-IN -->
  <section id="tab-assess" class="d-none"><div id="assessRoot"></div></section>

  <!-- SESSIONS -->
  <section id="tab-sessions" class="d-none">
    <h3 class="mc-serif fs-5 fw-semibold mb-3">Talk to a human counsellor</h3>
    <div class="glass p-4 mb-3">
      <select id="apCouns" class="form-select mb-2">
        <?php foreach ($counsellors as $c): ?><option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name'] . ' · ' . $c['title']) ?></option><?php endforeach; ?>
      </select>
      <div class="d-grid gap-2 mb-2" style="grid-template-columns:1fr 1fr" id="slotGrid"></div>
      <input id="apReason" class="form-control mb-3" placeholder="What would you like to talk about? (optional)">
      <button class="btn glow-btn w-100 py-2" onclick="bookAppt()">Book session</button>
    </div>
    <div id="apList"></div>
  </section>

  <!-- HISTORY -->
  <section id="tab-history" class="d-none">
    <div class="glass p-4 mb-3 d-flex align-items-center gap-3">
      <div class="d-flex align-items-center justify-content-center text-white fw-bold fs-5" style="width:48px;height:48px;border-radius:16px;background:linear-gradient(135deg,#A78BFA,#5B3BC4)"><?= htmlspecialchars(mb_substr($u['name'], 0, 1)) ?></div>
      <div><div class="fw-bold"><?= htmlspecialchars($u['name']) ?></div>
        <div style="font-size:.78rem;color:rgba(65,41,127,.6)"><?= htmlspecialchars($u['username']) ?> · <?= htmlspecialchars($u['dept'] ?? '—') ?> · <?= htmlspecialchars($u['level'] ?? '') ?></div></div>
    </div>
    <div class="glass p-4 mb-3"><h4 class="mc-serif fs-6 fw-semibold mb-2">Assessments taken</h4><div id="histAssess"></div></div>
    <div class="glass p-4"><h4 class="mc-serif fs-6 fw-semibold mb-2">Counselling sessions</h4><div id="histAppts"></div></div>
  </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const $ = id => document.getElementById(id);
const esc = s => { const d=document.createElement('div'); d.textContent=s; return d.innerHTML; };
async function api(url, data){
  const opt = data ? {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)} : {};
  const r = await fetch(url, opt); const j = await r.json().catch(()=>({}));
  if(!r.ok) throw new Error(j.error || 'Request failed'); return j;
}
function showTab(k){
  ['chat','mood','assess','sessions','history'].forEach(t=>{
    $('tab-'+t).classList.toggle('d-none', t!==k);
    document.querySelector(`[data-tab="${t}"]`).classList.toggle('active', t===k);
  });
  if(k==='mood') loadMood(); if(k==='sessions') loadAppts(); if(k==='history') loadHistory(); if(k==='assess') renderAssessMenu();
}

/* ---------- Chat ---------- */
function bubble(role, text, from){
  const wrap = document.createElement('div');
  wrap.className = 'msg-in d-flex ' + (role==='user'?'justify-content-end':'justify-content-start');
  const b = document.createElement('div');
  b.className = 'px-3 py-2 ' + (role==='user'?'bubble-user':role==='counsellor'?'bubble-couns':'glass bubble-ai');
  b.style.maxWidth='85%'; b.style.fontSize='.95rem';
  b.innerHTML = (role==='counsellor'?`<div style="font-size:.72rem;font-weight:700;color:#047857">${esc(from||'Counsellor')} · Counsellor</div>`:'') + esc(text);
  wrap.appendChild(b); $('chatBox').appendChild(wrap); $('chatBox').scrollTop = 1e9;
  return b;
}
async function loadChat(){
  try{ const j = await api('api/chat.php?action=history');
    $('chatBox').innerHTML='';
    if(!j.messages.length) bubble('assistant', "Hi <?= htmlspecialchars(explode(' ', $u['name'])[0]) ?>, I'm Amara. This is your space. Exams, family wahala, low days, anything on your mind. I listen without judging, and if you ever need more than me, I'll connect you to a real counsellor.");
    j.messages.forEach(m=>bubble(m.role, m.text, m.from_name));
  }catch(e){}
}
async function sendMsg(ev){ ev.preventDefault();
  const t = $('msgInput').value.trim(); if(!t) return false;
  $('msgInput').value=''; bubble('user', t);
  $('amaraOrb').classList.replace('breathe','think'); $('sendBtn').disabled=true;
  const typing = bubble('assistant',''); typing.classList.add('dot-typing'); typing.innerHTML='<span></span><span></span><span></span>';
  try{
    const j = await api('api/chat.php', {message:t});
    typing.classList.remove('dot-typing'); typing.textContent=j.reply;
    if(j.action==='crisis') showCrisis(j.crisis);
    else if(j.action==='review') $('reviewBanner').classList.remove('d-none');
  }catch(e){ typing.classList.remove('dot-typing'); typing.textContent='Connection hiccup. Please try again, or call 112 / visit the counselling centre if things feel urgent.'; }
  $('amaraOrb').classList.replace('think','breathe'); $('sendBtn').disabled=false;
  return false;
}
function showCrisis(info){
  const d = document.createElement('div');
  d.style.cssText='position:fixed;inset:0;z-index:50;background:rgba(46,16,101,.4);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;padding:1rem';
  d.innerHTML = `<div class="glass-deep risk-ring p-4 msg-in" style="max-width:420px;width:100%">
    <h3 class="mc-serif fs-5 fw-semibold">You deserve support right now</h3>
    <p style="font-size:.9rem;color:rgba(65,41,127,.85)">What you're carrying sounds heavy, and you shouldn't carry it alone. A human counsellor has been notified and can see you quickly.</p>
    <button class="btn glow-btn w-100 py-2 mb-2" onclick="this.closest('div[style]').remove();showTab('sessions')">Book an urgent session</button>
    <a class="btn glass w-100 py-2 mb-2 fw-semibold" style="border-radius:16px" href="tel:${esc(info.line)}">Emergency line: ${esc(info.line)} (Nigeria)</a>
    <div class="text-center" style="font-size:.72rem;color:rgba(65,41,127,.7)">${esc(info.centre)}. Your conversation stays confidential.</div>
    <button class="btn w-100 mt-2" style="color:#6D28D9;font-size:.85rem" onclick="this.parentElement.parentElement.remove()">Continue talking with Amara</button>
  </div>`;
  document.body.appendChild(d);
}
$('msgInput').addEventListener('keydown', e=>{ if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendMsg(e);} });

/* ---------- Mood ---------- */
const FACES=[['😞','Very low'],['🙁','Low'],['😐','Okay'],['🙂','Good'],['😄','Great']];
let moodPick=null, moodChart=null;
FACES.forEach(([f,l],i)=>{
  const b=document.createElement('button'); b.type='button'; b.className='mood-btn'; b.setAttribute('aria-label',l);
  b.innerHTML=`<div style="font-size:1.8rem">${f}</div><div style="font-size:.68rem;font-weight:700;color:#5B21B6">${l}</div>`;
  b.onclick=()=>{moodPick=i+1;[...$('moodRow').children].forEach((c,j)=>c.classList.toggle('active',j===i))};
  $('moodRow').appendChild(b);
});
async function saveMood(){ if(!moodPick) return;
  await api('api/mood.php',{value:moodPick,note:$('moodNote').value});
  $('moodNote').value=''; loadMood();
}
async function loadMood(){
  const j = await api('api/mood.php');
  const labels=j.moods.map(m=>m.entry_date.slice(5)), data=j.moods.map(m=>+m.value);
  if(moodChart) moodChart.destroy();
  moodChart=new Chart($('moodChart'),{type:'line',data:{labels,datasets:[{data,borderColor:'#7C5CE0',backgroundColor:'rgba(124,92,224,.12)',fill:true,tension:.4,pointBackgroundColor:'#7C5CE0'}]},
    options:{plugins:{legend:{display:false}},scales:{y:{min:1,max:5,ticks:{stepSize:1}}}}});
}

/* ---------- Assessments ---------- */
const PHQ9=<?= json_encode(["Little interest or pleasure in doing things","Feeling down, depressed, or hopeless","Trouble falling or staying asleep, or sleeping too much","Feeling tired or having little energy","Poor appetite or overeating","Feeling bad about yourself, or that you are a failure or have let yourself or your family down","Trouble concentrating on things, such as reading or lectures","Moving or speaking so slowly that other people could have noticed, or being fidgety or restless","Thoughts that you would be better off not being here, or of hurting yourself"]) ?>;
const GAD7=<?= json_encode(["Feeling nervous, anxious, or on edge","Not being able to stop or control worrying","Worrying too much about different things","Trouble relaxing","Being so restless that it's hard to sit still","Becoming easily annoyed or irritable","Feeling afraid as if something awful might happen"]) ?>;
const OPTS=["Not at all","Several days","More than half the days","Nearly every day"];
function renderAssessMenu(){
  $('assessRoot').innerHTML=`<h3 class="mc-serif fs-5 fw-semibold mb-2">Check in with yourself</h3>
  <p style="font-size:.9rem;color:rgba(65,41,127,.8)">Two short, standard wellbeing questionnaires. Your answers are private and only shared with a counsellor if follow-up would help you.</p>
  <button class="glass w-100 p-3 mb-2 text-start border-0" onclick="startAssess('phq')"><b>Mood check</b><div style="font-size:.75rem;color:rgba(65,41,127,.6)">PHQ-9 · 9 questions · about 2 min</div></button>
  <button class="glass w-100 p-3 text-start border-0" onclick="startAssess('gad')"><b>Worry check</b><div style="font-size:.75rem;color:rgba(65,41,127,.6)">GAD-7 · 7 questions · about 90 sec</div></button>`;
}
let asTool=null, asAnswers=[];
function startAssess(t){ asTool=t; asAnswers=Array(t==='phq'?9:7).fill(null); renderQ(); }
function renderQ(){
  const items = asTool==='phq'?PHQ9:GAD7;
  const idx = asAnswers.findIndex(a=>a===null);
  const cur = idx===-1?items.length-1:idx;
  const doneCount = asAnswers.filter(a=>a!==null).length;
  $('assessRoot').innerHTML=`
    <button class="btn p-0 mb-2" style="color:#6D28D9;font-size:.85rem" onclick="renderAssessMenu()">← Back</button>
    <div style="font-size:.75rem;font-weight:700;color:#6D28D9">Over the last 2 weeks… · ${Math.min(cur+1,items.length)} of ${items.length}</div>
    <div class="my-2" style="height:6px;border-radius:9999px;background:#EDE9FE"><div style="height:100%;border-radius:9999px;background:#8B5CF6;width:${doneCount/items.length*100}%"></div></div>
    <div class="glass p-4">
      <p class="mc-serif fs-6 fw-medium">${esc(items[cur])}</p>
      <div class="d-grid gap-2 mt-3">${OPTS.map((o,v)=>`<button class="btn text-start py-2 px-3 ${asAnswers[cur]===v?'glow-btn':''}" style="border-radius:16px;background:${asAnswers[cur]===v?'':'rgba(255,255,255,.7)'};font-size:.9rem" onclick="pickA(${cur},${v})">${o}</button>`).join('')}</div>
    </div>
    ${asAnswers.every(a=>a!==null)?'<button class="btn glow-btn w-100 mt-3 py-2" onclick="submitAssess()">See my result</button>':''}`;
}
function pickA(i,v){ asAnswers[i]=v; renderQ(); }
async function submitAssess(){
  const j = await api('api/assessment.php',{tool:asTool,answers:asAnswers});
  const msg = j.flagged || j.score>=15 ? "Thank you for being honest. Based on your answers, we've let a counsellor know so someone can reach out. You've done the brave part."
    : j.refer ? "You're carrying a bit much lately. Talking to Amara or booking a counsellor session could genuinely help."
    : "You're doing okay. Keep checking in with yourself, it builds the habit of noticing early.";
  $('assessRoot').innerHTML=`<div class="glass p-4 text-center">
    <div class="mc-serif fw-bold" style="font-size:3rem">${j.score}</div><div class="fw-bold">${esc(j.band)}</div>
    <p class="mt-2" style="font-size:.9rem;color:rgba(65,41,127,.8)">${msg}</p>
    ${j.refer?'<button class="btn glow-btn px-4 py-2 mt-2" onclick="showTab(\'sessions\')">Book a session</button>':''}
    <button class="btn d-block mx-auto mt-2" style="color:#6D28D9;font-size:.85rem" onclick="renderAssessMenu()">Back to assessments</button></div>`;
}

/* ---------- Sessions & History ---------- */
["Thu 11 Sep, 14:00","Fri 12 Sep, 09:00","Fri 12 Sep, 15:00","Mon 15 Sep, 10:00"].forEach(s=>{
  const b=document.createElement('button'); b.type='button'; b.textContent=s;
  b.className='btn py-2'; b.style.cssText='border-radius:16px;background:rgba(255,255,255,.7);font-size:.85rem;font-weight:600';
  b.onclick=()=>{window._slot=s;[...$('slotGrid').children].forEach(c=>{c.classList.remove('glow-btn');c.style.background='rgba(255,255,255,.7)'});b.classList.add('glow-btn')};
  $('slotGrid').appendChild(b);
});
async function bookAppt(){
  if(!window._slot) return;
  await api('api/appointments.php',{action:'book',counsellor_id:+$('apCouns').value,when:window._slot,reason:$('apReason').value});
  $('apReason').value=''; loadAppts();
}
const pillFor = s => s==='confirmed'?'pill-green':'pill-amber';
async function loadAppts(){
  const j = await api('api/appointments.php');
  $('apList').innerHTML = j.appointments.map(a=>`<div class="glass p-3 mb-2 d-flex align-items-center gap-2" style="font-size:.9rem">
    <div class="flex-fill"><b>${esc(a.when_text)}</b> · ${esc(a.counsellor||'Any counsellor')}${a.is_follow_up==1?' <span class="pill pill-violet">follow-up</span>':''}</div>
    <span class="pill ${pillFor(a.status)}">${esc(a.status)}</span></div>`).join('') || '<div class="glass p-3" style="font-size:.85rem;color:rgba(65,41,127,.7)">No sessions booked yet.</div>';
}
async function loadHistory(){
  const [a, ap] = await Promise.all([api('api/assessment.php'), api('api/appointments.php')]);
  $('histAssess').innerHTML = a.assessments.map(x=>`<div class="d-flex gap-2 align-items-center p-2 mb-1" style="background:rgba(255,255,255,.6);border-radius:14px;font-size:.88rem">
    <b>${esc(x.tool)}</b><span style="font-size:.75rem;color:rgba(65,41,127,.6)">${esc(x.date)}</span>
    <span class="ms-auto fw-bold">${x.score}</span><span class="pill ${/Severe/i.test(x.band)?'pill-red':/Moderate/.test(x.band)?'pill-amber':'pill-green'}">${esc(x.band)}</span></div>`).join('')
    || '<div style="font-size:.85rem;color:rgba(65,41,127,.7)">None yet. A check-in takes about two minutes.</div>';
  $('histAppts').innerHTML = ap.appointments.map(x=>`<div class="d-flex gap-2 align-items-center p-2 mb-1" style="background:rgba(255,255,255,.6);border-radius:14px;font-size:.88rem">
    <div class="flex-fill"><b>${esc(x.when_text)}</b> · ${esc(x.counsellor||'—')}<div style="font-size:.75rem;color:rgba(65,41,127,.6)">${esc(x.reason||'')}</div></div>
    <span class="pill ${pillFor(x.status)}">${esc(x.status)}</span></div>`).join('')
    || '<div style="font-size:.85rem;color:rgba(65,41,127,.7)">No sessions yet.</div>';
}
loadChat();
</script>
</body></html>
