import React, { useState, useEffect, useRef, useMemo } from "react";
import { LineChart, Line, XAxis, YAxis, Tooltip, ResponsiveContainer, BarChart, Bar, CartesianGrid } from "recharts";
import { Heart, MessageCircle, Sun, CalendarDays, ClipboardList, Users, BarChart3, ShieldAlert, Send, ChevronRight, ArrowLeft, Sparkles, Phone, CheckCircle2, Clock, LogOut, Menu, X, AlertTriangle, BookOpen, Activity, Download, Settings, UserRound } from "lucide-react";

/* ============================================================
   MindCare NG — AI Counselling & Therapist System
   Implementation of: "Counselling and Therapist AI for Nigerian
   Institutions" (Anozia Mary, F/HD/24/3211080, YabaTech, 2026)
   ============================================================ */

const GlobalStyle = () => (
  <style>{`
    @import url('https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Raleway:wght@300;400;500;600;700;800&display=swap');
    :root{
      --violet:#7C5CE0; --violet-deep:#41297F; --violet-soft:#C4B5FD;
      --green:#0B8A5F; --bg:#FAF7FF; --ink:#33235E;
    }
    .mc-root{ font-family:'Raleway',system-ui,sans-serif; color:var(--ink); background:var(--bg); }
    .mc-serif{ font-family:'Lora',Georgia,serif; }
    .glass{ background:rgba(255,255,255,0.62); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px);
      border:1px solid rgba(255,255,255,0.75); box-shadow:0 8px 32px rgba(76,29,149,0.08); }
    .glass-deep{ background:rgba(255,255,255,0.82); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,0.9); box-shadow:0 12px 40px rgba(76,29,149,0.10); }
    .aurora{ position:absolute; inset:0; overflow:hidden; pointer-events:none; }
    .aurora span{ position:absolute; border-radius:9999px; filter:blur(70px); opacity:.5; }
    .aurora .a1{ width:44rem;height:44rem; background:radial-gradient(circle,#C4B5FD, transparent 65%); top:-14rem; left:-10rem; animation:drift1 26s ease-in-out infinite alternate; }
    .aurora .a2{ width:36rem;height:36rem; background:radial-gradient(circle,#99F0CE, transparent 65%); bottom:-12rem; right:-8rem; animation:drift2 30s ease-in-out infinite alternate; }
    .aurora .a3{ width:26rem;height:26rem; background:radial-gradient(circle,#FBD1E8, transparent 65%); top:38%; left:52%; animation:drift1 34s ease-in-out infinite alternate-reverse; }
    @keyframes drift1{ from{transform:translate(0,0) scale(1)} to{transform:translate(4rem,3rem) scale(1.12)} }
    @keyframes drift2{ from{transform:translate(0,0) scale(1.05)} to{transform:translate(-3.5rem,-2.5rem) scale(.95)} }
    .orb{ position:relative; border-radius:9999px;
      background:radial-gradient(circle at 32% 30%, #EDE6FF 0%, #A78BFA 42%, #6D4FD0 78%, #41297F 100%);
      box-shadow:0 0 40px rgba(139,92,246,.45), inset 0 0 30px rgba(255,255,255,.35); }
    .orb::after{ content:''; position:absolute; inset:-14px; border-radius:9999px;
      background:radial-gradient(circle,rgba(167,139,250,.35), transparent 70%); z-index:-1; }
    .orb.breathe{ animation:breathe 4.6s ease-in-out infinite; }
    .orb.think{ animation:breathe 1.5s ease-in-out infinite; }
    @keyframes breathe{ 0%,100%{transform:scale(1)} 50%{transform:scale(1.07)} }
    .shimmer{ background:linear-gradient(110deg,var(--violet-deep) 35%, #A78BFA 50%, var(--violet-deep) 65%);
      background-size:220% 100%; -webkit-background-clip:text; background-clip:text; color:transparent;
      animation:shimmer 5s linear infinite; }
    @keyframes shimmer{ to{ background-position:-220% 0 } }
    .glow-btn{ position:relative; background:linear-gradient(135deg,#7C5CE0,#5B3BC4); color:#fff;
      box-shadow:0 4px 20px rgba(124,92,224,.45); transition:transform .18s ease, box-shadow .25s ease; }
    .glow-btn:hover{ transform:translateY(-2px); box-shadow:0 8px 30px rgba(124,92,224,.6); }
    .glow-btn:active{ transform:translateY(0) scale(.98); }
    .msg-in{ animation:msgIn .38s cubic-bezier(.21,1.02,.73,1) both; }
    @keyframes msgIn{ from{opacity:0; transform:translateY(14px) scale(.97)} to{opacity:1; transform:none} }
    .risk-ring{ box-shadow:0 0 0 1px rgba(220,38,38,.35), 0 0 24px rgba(220,38,38,.22); animation:riskPulse 2.4s ease-in-out infinite; }
    @keyframes riskPulse{ 0%,100%{box-shadow:0 0 0 1px rgba(220,38,38,.35),0 0 18px rgba(220,38,38,.18)} 50%{box-shadow:0 0 0 1px rgba(220,38,38,.5),0 0 34px rgba(220,38,38,.32)} }
    .dot-typing span{ display:inline-block; width:7px;height:7px; margin:0 2px; border-radius:9999px; background:#A78BFA; animation:bounceDot 1.2s infinite; }
    .dot-typing span:nth-child(2){ animation-delay:.15s } .dot-typing span:nth-child(3){ animation-delay:.3s }
    @keyframes bounceDot{ 0%,60%,100%{transform:translateY(0); opacity:.5} 30%{transform:translateY(-6px); opacity:1} }
    .fade-up{ animation:fadeUp .55s ease both; }
    @keyframes fadeUp{ from{opacity:0; transform:translateY(18px)} to{opacity:1; transform:none} }
    .mc-scroll::-webkit-scrollbar{ width:6px } .mc-scroll::-webkit-scrollbar-thumb{ background:#DDD3F7; border-radius:9999px }
    @media (prefers-reduced-motion: reduce){
      .aurora span,.orb,.shimmer,.msg-in,.fade-up,.risk-ring,.dot-typing span{ animation:none !important }
      .glow-btn,.glow-btn:hover{ transition:none; transform:none }
    }
  `}</style>
);

/* ---------------- Risk engine (Figure 3.3 flowchart) ---------------- */
const TIER3 = [/suicid/i, /kill (myself|me)/i, /end (my|it all|my life)/i, /don'?t want to (live|be alive|exist)/i, /take my (own )?life/i, /hurt(ing)? myself/i, /self[- ]?harm/i, /no reason to live/i, /better off (dead|without me)/i];
const TIER2 = [/hopeless/i, /worthless/i, /can'?t (go on|cope|take (it|this) anymore)/i, /give up/i, /hate myself/i, /nobody cares/i, /no way out/i, /empty inside/i, /panic attack/i];
const preScan = (t) => TIER3.some(r => r.test(t)) ? "high" : TIER2.some(r => r.test(t)) ? "moderate" : "low";
const maxRisk = (a, b) => { const o = { low: 0, moderate: 1, high: 2 }; return o[a] >= o[b] ? a : b; };
/* Item-8 resolver: the keyword pre-scan is high-recall/low-precision; the model
   reads context. Agreement on high -> crisis. Disagreement (keywords fired, model
   read it as hyperbole) -> downgrade to a supportive banner + human-review referral,
   never to silence. modelRisk === null means the API was unreachable: the pre-scan
   decides alone, preserving the offline fail-safe. */
const resolveRisk = (pre, modelRisk) => {
  if (modelRisk === null) return { risk: pre, action: pre === "high" ? "crisis" : pre === "moderate" ? "soft" : "none" };
  if (pre === "high" && modelRisk !== "high") return { risk: "moderate", action: "review" };
  const risk = maxRisk(pre, modelRisk);
  return { risk, action: risk === "high" ? "crisis" : risk === "moderate" ? "soft" : "none" };
};

const SYSTEM_PROMPT = `You are Amara, the AI counselling companion inside MindCare NG, a student mental wellbeing platform for Nigerian tertiary institutions (a Higher National Diploma project system). You support students dealing with academic stress, anxiety, low mood, relationship and family pressure, financial worry and loneliness.

How you respond:
- Warm, natural, conversational. 2-5 short sentences per reply. Never lecture or list unless asked.
- Use Cognitive Behavioural Therapy techniques conversationally: reflect feelings, gently explore thoughts behind them, help reframe unhelpful thinking, suggest one small concrete step (breathing, journaling, a walk, talking to someone trusted).
- You understand Nigerian student life (carryovers, strikes, hostel stress, family expectations, sapa). Be relatable but professional. Light Nigerian English is fine when the student uses it.
- You are a support companion, NOT a doctor or licensed therapist. Never diagnose, never name a disorder the student has not named, never advise on medication. If asked, warmly explain your limits and point to the campus counsellor.
- If the student shows possible crisis signs (self-harm, suicidal thoughts, wanting to disappear, hurting others): respond with calm care first, tell them they deserve immediate human support, and encourage connecting with the campus counsellor now. Do not mention or ask about methods. Keep them company in tone, not alarm.
- Encourage booking a session with a human counsellor whenever distress seems persistent.

After EVERY reply, on a new final line, append exactly one tag assessing this conversation's current risk level: <risk>low</risk> or <risk>moderate</risk> or <risk>high</risk>. "high" = any self-harm or suicidal ideation, or intent to harm others. "moderate" = significant persistent distress, hopelessness. This tag is machine-read and hidden from the student.`;

/* Few-shot exemplars drawn from the real corpus behind Chapter 3.3's dataset:
   CounselChat Q&A between help-seekers and licensed therapists (Bertagnolli, 2020;
   github.com/nbertagnolli/counsel-chat — the raw source of the Kaggle/Hugging Face
   "NLP Mental Health Conversations" dataset). Curated: non-crisis topics, concise
   professional responses. Extend after counsellor review (Chapter 3.5.1). */
const EXEMPLARS = `

Style exemplars from the counselling corpus (real help-seeker questions and licensed-therapist responses from the CounselChat data underlying the Chapter 3.3 dataset). Match this tone and depth:

Q: "I think I'm annoying and bothersome, but my girlfriend says I'm just fine. I don't think so, I think I'm stressing everyone else out"
A: "I think you have identified something important here. It's not unusual to experience feelings that don't seem to match up with what your rational mind recognizes. In this case, it sounds like a part of you recognizes that the people around you do not find you "annoying and bothersome." Despite knowing that people like you, it seems that there's still a part of you that feels like you're a bother.Finding a local counselor who can work with you to process those feelings might be just the help you need."

Q: "I’m a teenager. My entire family needs family therapy, and more than likely individual therapy. My parents refuse to take action, and I'm tired of it. Is there any way I can get out of this myself?"
A: "This sounds like a really tough situation. As a teenager, you may be able to get counseling on your own (without needing your parents' consent) under some circumstances. If your parents are refusing to consider counseling, you might want to try talking to your doctor or another trusted adult about finding some counseling resources - even without your parents' help."

Q: "I'm in a relationship, but I feel like I'm always putting more into it and not getting reciprocated. My ex told me that I will never find anyone else, and that's lingering in the back of my mind."
A: "The most crucial key to any relationship is that mutual feeling you hold between you both: that you matter. Sounds like you are stuck in a cycle of hearing your ex say you don't matter. That's why it didn't work with him btw. He wasn't reflecting to you that you mattered. However it ended, clearly though that's the sentiment that's lingering with you. So here you are hanging around a new man why is telling you the same message. Move on. You aren't unworthy, you just haven't found a man who is worthy of you! To be worthy of you, he must see your worth. Often though before anyone else can see your worth, you have to believe it."

Q: "I feel like I'm trying to convince myself that I'm okay when I'm not. I'm always blocking out the bad things and forgetting. I also feel like nobody cares for me and they never will. I feel truly alone."
A: "I can relate! When things are going badly, I feel like my life has always been and will always be that way. (But I also do this when things are going well. That is, I forget how good things can turn bad. Personally, to avoid this emotional roller-coaster, I try and heed the famous advice by author and feminist Rita Mae Brown: "One of the keys to happiness is a bad memory."So maybe this weakness of your for forgetting is really a strength! It sounds a lot like living in the moment to me. And while it’s hard to manage my past and my future, the moment seems like a small enough piece of temporal real estate to sort out. So that’s the “what” of my answer. The “how” goes like this: Choose one from column A, two from column B, and three from column C in the following chart. Then try doing them for as long as you can. Then see what happens.A B CGratitude Forgiveness AppreciationExpectations Meditation ExerciseBitterness Distraction Volunteering Resentment Substances WorryRegret Possessions PessimismShame Desire SuperstitionRage Isolation WishingSelf-loathing Criticism Withholding If you feel as though what you think and believe are out of your control, or that your values were imposed on you, or that nothing good will ever happen again, then we will have to respectfully disagree. You’ve ask a very deep and insightful question, proving that your hope has gotten you this far. Hang onto that hope because I’m an example of things working out despite my previous way of looking at my life.Instead of “convincing” yourself that you’re okay when you’re not, how about calling it “accepting yourself as okay just the way you are, without judging your okayness.” You probably have high standards (perfectionism?) and that’s a thing to talk with a counselor about. The opposite of perfect is not horrible. It’s called “good enough.”Blocking out the bad things and forgetting is as natural as eating and sleeping. All the other mammals do it (except when it comes to life-threatening bad things) so why shouldn’t we? This might be called optimism. Feeling alone and uncared for is a worse feeling than being despised. This is good! This means (I suppose) that you don’t despise yourself as much as you just don’t care for you (because we can often project our own self-beliefs onto others). This is an abstract concept that will take some time to get used to. But I have a suspicion that, with just a little more self-care, and a little more caring for others, you might be better off very soon."

Q: "We’ve been together almost three years. We argue and he ends it by telling me he doesn’t love me. It's hurtful because I am all about resolving the problem, and he dwells on the issue even if I drop what he's done and just swallow my pride and say I am sorry. How can this be resolved? We have kids, and I don't want a broken family because we can't communicate."
A: "Under duress the very youngest parts of you and your boyfriend emerge. So, while you still look like grown ups, your actions and words are motivated by very early preverbal parts of both of you. In simple terms it is as if two infants somehow acquire the ability to say words but they are motivated by very basic early wounds that were created due to deficits in what was able to be done for each of you, and likely understandable in terms of caregiver's life experience, and that still fell short of what you needed. You are both still trying to get very early needs met. The best advice I can offer is to try and remember this as early as possible when things start to heat up, and then restrain expression that you already know leads nowhere helpful. And seek out a therapist who can help you to work with these early issues and can support you to grow and develop from this stuck point.Avraham Cohen, Ph.D, R.C.C., C.C.C."`;

async function askAmara(history, ctx) {
  const contextNote = ctx ? `\n\n[Context from the app, not from the student: ${ctx}]` : "";
  const messages = history.filter(m => m.role !== "counsellor").map(m => ({ role: m.role, content: m.text }));
  
  if (messages.length && contextNote) messages[messages.length - 1] = { ...messages[messages.length - 1], content: messages[messages.length - 1].content + contextNote };
  const res = await fetch("https://api.anthropic.com/v1/messages", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ model: "claude-sonnet-4-6", max_tokens: 1000, system: SYSTEM_PROMPT + EXEMPLARS, messages }),
  });
  const data = await res.json();
  const raw = (data.content || []).filter(b => b.type === "text").map(b => b.text).join("\n").trim();
  const m = raw.match(/<risk>(low|moderate|high)<\/risk>/i);
  return { text: raw.replace(/<risk>(low|moderate|high)<\/risk>/gi, "").trim(), risk: m ? m[1].toLowerCase() : "low" };
}

/* ---------------- Seed data ---------------- */
const COUNSELLORS = [
  { id: "c1", name: "Mrs. Adebayo Funke", title: "Senior Counsellor", focus: "Anxiety & academic stress" },
  { id: "c2", name: "Mr. Chukwu Daniel", title: "Counselling Psychologist", focus: "Depression & grief" },
];
const seedStudents = [
  { id: "s1", name: "Adaeze Okonkwo", matric: "F/HD/24/3211045", dept: "Computer Science", lastMood: 2 },
  { id: "s2", name: "Ibrahim Musa", matric: "F/HD/24/3211112", dept: "Statistics", lastMood: 4 },
  { id: "s3", name: "Blessing Eze", matric: "F/ND/25/1140233", dept: "Mass Comm", lastMood: 3 },
];
const seedMoods = [
  { day: "Thu", value: 3 }, { day: "Fri", value: 2 }, { day: "Sat", value: 2 },
  { day: "Sun", value: 3 }, { day: "Mon", value: 4 }, { day: "Tue", value: 3 },
];
const seedAppointments = [
  { id: "a1", student: "Ibrahim Musa", counsellor: "Mrs. Adebayo Funke", when: "Fri 12 Sep, 10:00", status: "confirmed", reason: "Exam anxiety follow-up" },
];
const seedReferrals = [
  { id: "r1", student: "Adaeze Okonkwo", source: "AI chat session", risk: "high", summary: "Expressed persistent hopelessness about carryover results; flagged by Amara for urgent human follow-up.", time: "Yesterday, 21:14", status: "open" },
];

/* ---------------- Assessments (public-domain instruments) ---------------- */
const PHQ9 = ["Little interest or pleasure in doing things", "Feeling down, depressed, or hopeless", "Trouble falling or staying asleep, or sleeping too much", "Feeling tired or having little energy", "Poor appetite or overeating", "Feeling bad about yourself, or that you are a failure or have let yourself or your family down", "Trouble concentrating on things, such as reading or lectures", "Moving or speaking so slowly that other people could have noticed, or being fidgety or restless", "Thoughts that you would be better off not being here, or of hurting yourself"];
const GAD7 = ["Feeling nervous, anxious, or on edge", "Not being able to stop or control worrying", "Worrying too much about different things", "Trouble relaxing", "Being so restless that it's hard to sit still", "Becoming easily annoyed or irritable", "Feeling afraid as if something awful might happen"];
const OPTIONS = ["Not at all", "Several days", "More than half the days", "Nearly every day"];
const phqBand = s => s <= 4 ? ["Minimal", "#0B8A5F"] : s <= 9 ? ["Mild", "#0B8A5F"] : s <= 14 ? ["Moderate", "#C2740A"] : s <= 19 ? ["Moderately severe", "#C2410C"] : ["Severe", "#DC2626"];
const gadBand = s => s <= 4 ? ["Minimal", "#0B8A5F"] : s <= 9 ? ["Mild", "#0B8A5F"] : s <= 14 ? ["Moderate", "#C2740A"] : ["Severe", "#DC2626"];

/* ---------------- Shared UI ---------------- */
const Aurora = () => (<div className="aurora"><span className="a1" /><span className="a2" /><span className="a3" /></div>);
const Orb = ({ size = 56, thinking = false }) => (
  <div className={`orb ${thinking ? "think" : "breathe"} shrink-0`} style={{ width: size, height: size }} />
);
const Pill = ({ children, tone = "violet" }) => {
  const tones = { violet: "bg-violet-100 text-violet-800", green: "bg-emerald-100 text-emerald-800", amber: "bg-amber-100 text-amber-800", red: "bg-red-100 text-red-700" };
  return <span className={`px-2.5 py-1 rounded-full text-xs font-semibold ${tones[tone]}`}>{children}</span>;
};

const CrisisPanel = ({ onBook, onClose, settings }) => (
  <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-violet-950/40 backdrop-blur-sm p-4">
    <div className="glass-deep risk-ring rounded-3xl max-w-md w-full p-6 msg-in">
      <div className="flex items-start gap-3">
        <div className="p-2.5 rounded-2xl bg-red-100"><ShieldAlert className="w-6 h-6 text-red-600" /></div>
        <div>
          <h3 className="mc-serif text-xl font-semibold">You deserve support right now</h3>
          <p className="text-sm mt-1 text-violet-900/80">What you're carrying sounds heavy, and you shouldn't carry it alone. A human counsellor has been notified and can see you quickly.</p>
        </div>
      </div>
      <div className="mt-5 space-y-3">
        <button onClick={onBook} className="glow-btn w-full rounded-2xl py-3.5 font-semibold flex items-center justify-center gap-2"><CalendarDays className="w-5 h-5" /> Book an urgent session</button>
        <a className="glass w-full rounded-2xl py-3.5 font-semibold flex items-center justify-center gap-2 text-violet-900" href={`tel:${settings.crisisLine}`}><Phone className="w-5 h-5" /> Emergency line: {settings.crisisLine} (Nigeria)</a>
        <div className="text-xs text-violet-900/70 text-center">{settings.centre}. Your conversation stays confidential.</div>
      </div>
      <button onClick={onClose} className="mt-4 w-full text-sm text-violet-700 font-medium py-2">Continue talking with Amara</button>
    </div>
  </div>
);

/* ---------------- Chat ---------------- */
function Chat({ state, dispatch }) {
  const [input, setInput] = useState("");
  const [thinking, setThinking] = useState(false);
  const [crisis, setCrisis] = useState(false);
  const [banner, setBanner] = useState(false);
  const endRef = useRef(null);
  const msgs = state.chat;
  useEffect(() => { endRef.current?.scrollIntoView({ behavior: "smooth" }); }, [msgs, thinking]);

  const send = async () => {
    const text = input.trim();
    if (!text || thinking) return;
    setInput("");
    const pre = preScan(text);
    const history = [...msgs, { role: "user", text }];
    dispatch({ type: "chat", msgs: history });
    setThinking(true);
    let reply, modelRisk = null;
    try {
      const la = state.lastAssessment || {};
      const parts = [];
      if (la.phq != null) parts.push(`PHQ-9 ${la.phq} (${phqBand(la.phq)[0]})`);
      if (la.gad != null) parts.push(`GAD-7 ${la.gad} (${gadBand(la.gad)[0]})`);
      const ctx = parts.length ? `latest self-assessment: ${parts.join(", ")}` : null;
      const r = await askAmara(history, ctx);
      reply = r.text; modelRisk = r.risk;
    } catch {
      reply = "I'm having trouble connecting right now, but I'm still here with you. While I reconnect, try one slow breath in for 4 and out for 6. If things feel urgent, please reach the campus counsellor or call 112.";
    }
    setThinking(false);
    const { risk, action } = resolveRisk(pre, modelRisk);
    const ctxMsgs = [...history, { role: "assistant", text: reply }].slice(-4);
    dispatch({ type: "chat", msgs: [...history, { role: "assistant", text: reply, risk: action === "crisis" ? "high" : undefined }] });
    if (action === "crisis") {
      setCrisis(true);
      dispatch({ type: "refer", referral: { id: "r" + Date.now(), student: state.user.name, source: "AI chat session", risk: "high", summary: "Crisis-level risk confirmed in a live session. Immediate human follow-up recommended.", time: "Just now", status: "open", context: ctxMsgs } });
    } else if (action === "review") {
      setBanner(true);
      dispatch({ type: "refer", referral: { id: "r" + Date.now(), student: state.user.name, source: "AI chat session", risk: "moderate", summary: "Keyword scanner flagged possible crisis language; Amara read it as non-crisis in context. Queued for human review.", time: "Just now", status: "open", context: ctxMsgs } });
    } else if (action === "soft") {
      dispatch({ type: "refer", referral: { id: "r" + Date.now(), student: state.user.name, source: "AI chat session", risk: "moderate", summary: "Persistent distress signals detected in conversation. Suggested follow-up within 48 hours.", time: "Just now", status: "open", context: ctxMsgs } });
    }
  };

  return (
    <div className="flex flex-col h-full">
      <div className="flex items-center gap-3 pb-4">
        <Orb size={46} thinking={thinking} />
        <div>
          <div className="mc-serif text-lg font-semibold leading-tight">Amara</div>
          <div className="text-xs text-violet-700 flex items-center gap-1.5"><span className="w-2 h-2 rounded-full bg-emerald-500 inline-block" /> Here for you, 24/7 · Confidential</div>
        </div>
      </div>
      <div className="flex-1 overflow-y-auto mc-scroll space-y-3 pr-1 pb-3">
        {msgs.length === 0 && (
          <div className="glass rounded-3xl p-5 msg-in">
            <p className="mc-serif text-lg">Hi {state.user.name.split(" ")[0]}, I'm Amara. 🌿</p>
            <p className="text-sm mt-1.5 text-violet-900/80">This is your space. Exams, family wahala, low days, anything on your mind. I listen without judging, and if you ever need more than me, I'll connect you to a real counsellor.</p>
            <div className="flex flex-wrap gap-2 mt-3">
              {["I'm stressed about exams", "I've been feeling low lately", "I can't sleep well"].map(s => (
                <button key={s} onClick={() => setInput(s)} className="text-xs font-semibold px-3 py-2 rounded-full bg-white/70 border border-violet-200 text-violet-800 hover:bg-violet-50 cursor-pointer">{s}</button>
              ))}
            </div>
          </div>
        )}
        {msgs.map((m, i) => (
          <div key={i} className={`msg-in flex ${m.role === "user" ? "justify-end" : "justify-start"}`}>
            <div className={`max-w-[85%] rounded-3xl px-4 py-3 text-[15px] leading-relaxed ${m.role === "user" ? "text-white" : m.role === "counsellor" ? "bg-emerald-50 border border-emerald-200" : "glass"}`}
              style={m.role === "user" ? { background: "linear-gradient(135deg,#7C5CE0,#5B3BC4)", borderBottomRightRadius: 8 } : { borderBottomLeftRadius: 8 }}>
              {m.role === "counsellor" && <div className="text-xs font-bold text-emerald-700 mb-1 flex items-center gap-1"><UserRound className="w-3.5 h-3.5" /> {m.from} · Counsellor</div>}
              {m.text}
              {m.risk === "high" && <div className="mt-2 text-xs font-semibold text-red-600 flex items-center gap-1"><ShieldAlert className="w-3.5 h-3.5" /> A counsellor has been notified for you</div>}
            </div>
          </div>
        ))}
        {thinking && <div className="msg-in glass rounded-3xl px-4 py-3 w-fit dot-typing" style={{ borderBottomLeftRadius: 8 }}><span /><span /><span /></div>}
        <div ref={endRef} />
      </div>
      {banner && (
        <div className="glass rounded-2xl p-3.5 mb-2 msg-in flex items-start gap-2.5" style={{ border: "1px solid #FDE68A", background: "rgba(255,251,235,.8)" }}>
          <Heart className="w-4 h-4 text-amber-600 mt-0.5 shrink-0" />
          <div className="flex-1 text-sm text-violet-900/85">Sounds like a lot is sitting on you. If it would help to talk it through with a person, a counsellor can see you this week.</div>
          <button onClick={() => { setBanner(false); dispatch({ type: "tab", tab: "sessions" }); }} className="text-xs font-bold text-violet-700 bg-violet-100 rounded-xl px-3 py-2 cursor-pointer shrink-0">Book</button>
          <button onClick={() => setBanner(false)} aria-label="Dismiss" className="p-1.5 cursor-pointer shrink-0"><X className="w-4 h-4 text-violet-400" /></button>
        </div>
      )}
      <div className="glass-deep rounded-3xl p-2 flex items-end gap-2">
        <textarea rows={1} value={input} onChange={e => setInput(e.target.value)}
          onKeyDown={e => { if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); send(); } }}
          placeholder="Type what's on your mind…"
          className="flex-1 bg-transparent resize-none outline-none px-3 py-2.5 text-[15px] placeholder-violet-400" />
        <button onClick={send} disabled={thinking || !input.trim()} aria-label="Send message"
          className="glow-btn rounded-2xl p-3 disabled:opacity-40 cursor-pointer"><Send className="w-5 h-5" /></button>
      </div>
      <p className="text-[11px] text-center text-violet-700/70 mt-2">Amara supports you but doesn't replace a professional counsellor. In an emergency call 112.</p>
      {crisis && <CrisisPanel settings={state.settings} onBook={() => { setCrisis(false); dispatch({ type: "tab", tab: "sessions" }); }} onClose={() => setCrisis(false)} />}
    </div>
  );
}

/* ---------------- Mood tracker ---------------- */
const MOOD_FACES = [["😞","Very low"],["🙁","Low"],["😐","Okay"],["🙂","Good"],["😄","Great"]];
function MoodTracker({ state, dispatch }) {
  const [note, setNote] = useState("");
  const [picked, setPicked] = useState(null);
  const save = () => {
    if (picked == null) return;
    dispatch({ type: "mood", entry: { day: "Today", value: picked + 1, note } });
    setPicked(null); setNote("");
  };
  return (
    <div className="space-y-5 fade-up">
      <div className="glass rounded-3xl p-5">
        <h3 className="mc-serif text-xl font-semibold">How are you feeling today?</h3>
        <div className="flex justify-between mt-4">
          {MOOD_FACES.map(([f, label], i) => (
            <button key={i} onClick={() => setPicked(i)} aria-label={label}
              className={`flex flex-col items-center gap-1.5 rounded-2xl px-2.5 py-2.5 transition cursor-pointer min-w-[54px] ${picked === i ? "bg-violet-100 ring-2 ring-violet-400" : "hover:bg-white/70"}`}>
              <span className="text-3xl" role="img">{f}</span>
              <span className="text-[11px] font-semibold text-violet-800">{label}</span>
            </button>
          ))}
        </div>
        <input value={note} onChange={e => setNote(e.target.value)} placeholder="Add a note (optional)…"
          className="mt-4 w-full glass rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-violet-300" />
        <button onClick={save} disabled={picked == null} className="glow-btn mt-4 w-full rounded-2xl py-3 font-semibold disabled:opacity-40 cursor-pointer">Save check-in</button>
      </div>
      <div className="glass rounded-3xl p-5">
        <h3 className="mc-serif text-lg font-semibold mb-2">Your week</h3>
        <div className="h-44">
          <ResponsiveContainer width="100%" height="100%">
            <LineChart data={state.moods} margin={{ top: 8, right: 8, left: -28, bottom: 0 }}>
              <CartesianGrid stroke="#EDE9FE" vertical={false} />
              <XAxis dataKey="day" tick={{ fontSize: 12, fill: "#6D4FD0" }} axisLine={false} tickLine={false} />
              <YAxis domain={[1, 5]} ticks={[1,2,3,4,5]} tick={{ fontSize: 12, fill: "#6D4FD0" }} axisLine={false} tickLine={false} />
              <Tooltip contentStyle={{ borderRadius: 16, border: "1px solid #EDE9FE", fontFamily: "Raleway" }} />
              <Line type="monotone" dataKey="value" stroke="#7C5CE0" strokeWidth={3} dot={{ r: 4, fill: "#7C5CE0" }} activeDot={{ r: 6 }} />
            </LineChart>
          </ResponsiveContainer>
        </div>
        <p className="text-xs text-violet-800/70 mt-1">Your counsellor can see this trend if you're referred, so support meets you where you are.</p>
      </div>
    </div>
  );
}

/* ---------------- Assessment ---------------- */
function Assessment({ state, dispatch }) {
  const [tool, setTool] = useState(null); // 'phq' | 'gad'
  const [answers, setAnswers] = useState([]);
  const [done, setDone] = useState(null);
  const items = tool === "phq" ? PHQ9 : GAD7;
  const start = t => { setTool(t); setAnswers(Array(t === "phq" ? 9 : 7).fill(null)); setDone(null); };
  const submit = () => {
    const score = answers.reduce((a, b) => a + b, 0);
    const flagged = tool === "phq" && answers[8] > 0;
    const [band, color] = tool === "phq" ? phqBand(score) : gadBand(score);
    setDone({ score, band, color, flagged });
    dispatch({ type: "assessment", result: { [tool]: score }, rec: { id: "as" + Date.now(), tool: tool === "phq" ? "PHQ-9" : "GAD-7", score, band, date: new Date().toLocaleDateString("en-NG", { day: "numeric", month: "short" }) } });
    if (flagged || score >= 15) {
      dispatch({ type: "refer", referral: { id: "r" + Date.now(), student: state.user.name, source: tool === "phq" ? "PHQ-9 self-assessment" : "GAD-7 self-assessment", risk: flagged ? "high" : "moderate", summary: flagged ? "Item 9 answered positively on PHQ-9. Priority outreach recommended." : `Score of ${score} (${band}). Follow-up recommended.`, time: "Just now", status: "open" }, soft: !flagged });
    }
  };
  if (!tool) return (
    <div className="space-y-4 fade-up">
      <h3 className="mc-serif text-xl font-semibold">Check in with yourself</h3>
      <p className="text-sm text-violet-900/80">Two short, standard wellbeing questionnaires. Your answers are private and only shared with a counsellor if follow-up would help you.</p>
      {[["phq", "Mood check", "PHQ-9 · 9 questions · about 2 min", Heart], ["gad", "Worry check", "GAD-7 · 7 questions · about 90 sec", Activity]].map(([k, t, s, Icon]) => (
        <button key={k} onClick={() => start(k)} className="glass w-full rounded-3xl p-5 flex items-center gap-4 text-left hover:bg-white/80 transition cursor-pointer">
          <div className="p-3 rounded-2xl bg-violet-100"><Icon className="w-6 h-6 text-violet-700" /></div>
          <div className="flex-1"><div className="font-bold">{t}</div><div className="text-xs text-violet-800/70">{s}</div></div>
          <ChevronRight className="w-5 h-5 text-violet-400" />
        </button>
      ))}
    </div>
  );
  if (done) return (
    <div className="glass rounded-3xl p-6 fade-up text-center">
      <div className="mc-serif text-5xl font-bold" style={{ color: done.color }}>{done.score}</div>
      <div className="font-bold mt-1" style={{ color: done.color }}>{done.band}</div>
      <p className="text-sm text-violet-900/80 mt-3">{done.flagged || done.score >= 15 ? "Thank you for being honest. Based on your answers, we've let a counsellor know so someone can reach out. You've done the brave part." : done.score >= 10 ? "You're carrying a bit much lately. Talking to Amara or booking a counsellor session could genuinely help." : "You're doing okay. Keep checking in with yourself, it builds the habit of noticing early."}</p>
      {(done.flagged || done.score >= 10) && <button onClick={() => dispatch({ type: "tab", tab: "sessions" })} className="glow-btn mt-4 rounded-2xl px-6 py-3 font-semibold cursor-pointer">Book a session</button>}
      <button onClick={() => setTool(null)} className="block mx-auto mt-3 text-sm text-violet-700 font-medium cursor-pointer">Back to assessments</button>
    </div>
  );
  const idx = answers.findIndex(a => a === null);
  const cur = idx === -1 ? items.length - 1 : idx;
  return (
    <div className="fade-up">
      <button onClick={() => setTool(null)} className="text-sm text-violet-700 font-medium flex items-center gap-1 mb-3 cursor-pointer"><ArrowLeft className="w-4 h-4" /> Back</button>
      <div className="text-xs font-semibold text-violet-700 mb-2">Over the last 2 weeks… · {Math.min(cur + 1, items.length)} of {items.length}</div>
      <div className="h-1.5 rounded-full bg-violet-100 mb-4"><div className="h-full rounded-full bg-violet-500 transition-all" style={{ width: `${(answers.filter(a => a !== null).length / items.length) * 100}%` }} /></div>
      <div className="glass rounded-3xl p-5">
        <p className="mc-serif text-lg font-medium leading-snug">{items[cur]}</p>
        <div className="mt-4 space-y-2">
          {OPTIONS.map((o, v) => (
            <button key={o} onClick={() => { const n = [...answers]; n[cur] = v; setAnswers(n); }}
              className={`w-full text-left rounded-2xl px-4 py-3 text-sm font-medium transition cursor-pointer ${answers[cur] === v ? "bg-violet-600 text-white" : "bg-white/70 hover:bg-violet-50 text-violet-900"}`}>{o}</button>
          ))}
        </div>
      </div>
      {answers.every(a => a !== null) && <button onClick={submit} className="glow-btn mt-4 w-full rounded-2xl py-3.5 font-semibold cursor-pointer">See my result</button>}
    </div>
  );
}

/* ---------------- Appointments (student) ---------------- */
function Sessions({ state, dispatch }) {
  const [pick, setPick] = useState(null);
  const slots = ["Thu 11 Sep, 14:00", "Fri 12 Sep, 09:00", "Fri 12 Sep, 15:00", "Mon 15 Sep, 10:00"];
  const [slot, setSlot] = useState(null);
  const [reason, setReason] = useState("");
  const book = () => {
    dispatch({ type: "book", appt: { id: "a" + Date.now(), student: state.user.name, counsellor: pick.name, when: slot, status: "pending", reason: reason || "General session" } });
    setPick(null); setSlot(null); setReason("");
  };
  const mine = state.appointments.filter(a => a.student === state.user.name);
  return (
    <div className="space-y-5 fade-up">
      <h3 className="mc-serif text-xl font-semibold">Talk to a human counsellor</h3>
      {!pick ? (
        <div className="space-y-3">
          {COUNSELLORS.map(c => (
            <button key={c.id} onClick={() => setPick(c)} className="glass w-full rounded-3xl p-5 flex items-center gap-4 text-left hover:bg-white/80 transition cursor-pointer">
              <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-400 to-violet-700 flex items-center justify-center text-white font-bold text-lg">{c.name.split(" ")[1][0]}</div>
              <div className="flex-1"><div className="font-bold">{c.name}</div><div className="text-xs text-violet-800/70">{c.title} · {c.focus}</div></div>
              <ChevronRight className="w-5 h-5 text-violet-400" />
            </button>
          ))}
        </div>
      ) : (
        <div className="glass rounded-3xl p-5">
          <div className="font-bold">{pick.name}</div>
          <div className="text-xs text-violet-800/70 mb-4">{pick.title}</div>
          <div className="grid grid-cols-2 gap-2">
            {slots.map(s => <button key={s} onClick={() => setSlot(s)} className={`rounded-2xl px-3 py-3 text-sm font-semibold transition cursor-pointer ${slot === s ? "bg-violet-600 text-white" : "bg-white/70 hover:bg-violet-50 text-violet-900"}`}>{s}</button>)}
          </div>
          <input value={reason} onChange={e => setReason(e.target.value)} placeholder="What would you like to talk about? (optional)"
            className="mt-3 w-full glass rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-violet-300" />
          <div className="flex gap-2 mt-4">
            <button onClick={() => setPick(null)} className="flex-1 rounded-2xl py-3 font-semibold bg-white/70 text-violet-800 cursor-pointer">Back</button>
            <button onClick={book} disabled={!slot} className="glow-btn flex-1 rounded-2xl py-3 font-semibold disabled:opacity-40 cursor-pointer">Book session</button>
          </div>
        </div>
      )}
      {mine.length > 0 && (
        <div>
          <h4 className="font-bold text-sm mb-2">Your sessions</h4>
          <div className="space-y-2">
            {mine.map(a => (
              <div key={a.id} className="glass rounded-2xl p-4 flex items-center gap-3">
                <Clock className="w-5 h-5 text-violet-600 shrink-0" />
                <div className="flex-1 text-sm"><span className="font-semibold">{a.when}</span> · {a.counsellor}</div>
                <Pill tone={a.status === "confirmed" ? "green" : "amber"}>{a.status}</Pill>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

/* ---------------- History (student) ---------------- */
function History({ state, dispatch }) {
  const [editing, setEditing] = useState(false);
  const [draft, setDraft] = useState({ name: state.user.name, dept: state.user.dept || "" });
  const mine = state.appointments.filter(a => a.student === state.user.name);
  const aiTurns = state.chat.filter(m => m.role === "user").length;
  const lastRisk = [...state.chat].reverse().find(m => m.risk)?.risk;
  return (
    <div className="space-y-4 fade-up">
      <div className="glass rounded-3xl p-5">
        <div className="flex items-center gap-3">
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-400 to-violet-700 flex items-center justify-center text-white font-bold text-lg">{state.user.name[0]}</div>
          <div className="flex-1 min-w-0">
            {!editing ? (<>
              <div className="font-bold truncate">{state.user.name}</div>
              <div className="text-xs text-violet-800/60">{state.user.matric || "—"} · {state.user.dept || "—"}</div>
            </>) : (<div className="space-y-2">
              <input value={draft.name} onChange={e => setDraft({ ...draft, name: e.target.value })} className="w-full glass rounded-xl px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-300" placeholder="Full name" />
              <input value={draft.dept} onChange={e => setDraft({ ...draft, dept: e.target.value })} className="w-full glass rounded-xl px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-violet-300" placeholder="Department" />
            </div>)}
          </div>
          <button onClick={() => { if (editing) dispatch({ type: "profile", patch: draft }); setEditing(!editing); }}
            className="text-xs font-bold text-violet-700 bg-violet-100 rounded-xl px-3 py-2 cursor-pointer shrink-0">{editing ? "Save" : "Edit"}</button>
        </div>
      </div>
      <div className="glass rounded-3xl p-5">
        <h4 className="font-bold text-sm mb-3 flex items-center gap-1.5"><MessageCircle className="w-4 h-4 text-violet-600" /> AI counselling sessions</h4>
        {aiTurns === 0 ? <p className="text-sm text-violet-800/70">No sessions yet. Amara is one tap away whenever you need her.</p> : (
          <div className="flex items-center gap-3 bg-white/60 rounded-2xl px-4 py-3">
            <div className="flex-1 text-sm"><span className="font-semibold">Current session</span> · {aiTurns} message{aiTurns > 1 ? "s" : ""} shared</div>
            {lastRisk && <Pill tone={lastRisk === "high" ? "red" : lastRisk === "moderate" ? "amber" : "green"}>{lastRisk === "low" ? "steady" : lastRisk}</Pill>}
          </div>
        )}
      </div>
      <div className="glass rounded-3xl p-5">
        <h4 className="font-bold text-sm mb-3 flex items-center gap-1.5"><ClipboardList className="w-4 h-4 text-violet-600" /> Assessments taken</h4>
        {state.assessments.length === 0 ? <p className="text-sm text-violet-800/70">None yet. A check-in takes about two minutes.</p> : (
          <div className="space-y-2">
            {state.assessments.map(a => (
              <div key={a.id} className="flex items-center gap-3 bg-white/60 rounded-2xl px-4 py-3 text-sm">
                <span className="font-semibold">{a.tool}</span>
                <span className="text-violet-800/60 text-xs">{a.date}</span>
                <span className="ml-auto font-bold">{a.score}</span><Pill tone={/Severe|severe/.test(a.band) ? "red" : /Moderate/.test(a.band) ? "amber" : "green"}>{a.band}</Pill>
              </div>
            ))}
          </div>
        )}
      </div>
      <div className="glass rounded-3xl p-5">
        <h4 className="font-bold text-sm mb-3 flex items-center gap-1.5"><CalendarDays className="w-4 h-4 text-violet-600" /> Counselling sessions</h4>
        {mine.length === 0 ? <p className="text-sm text-violet-800/70">No sessions booked yet.</p> : (
          <div className="space-y-2">{mine.map(a => (
            <div key={a.id} className="flex items-center gap-3 bg-white/60 rounded-2xl px-4 py-3 text-sm">
              <div className="flex-1"><span className="font-semibold">{a.when}</span> · {a.counsellor}<div className="text-xs text-violet-800/60">{a.reason}</div></div>
              <Pill tone={a.status === "confirmed" ? "green" : "amber"}>{a.status}</Pill>
            </div>
          ))}</div>
        )}
      </div>
    </div>
  );
}

/* ---------------- Counsellor dashboard ---------------- */
function CounsellorView({ state, dispatch }) {
  const [tab, setTab] = useState("referrals");
  const [openCtx, setOpenCtx] = useState(null);
  const [reply, setReply] = useState("");
  const open = state.referrals.filter(r => r.status === "open");
  const sendReply = (r) => {
    if (!reply.trim()) return;
    dispatch({ type: "counsellorMsg", msg: { role: "counsellor", from: state.user.name, text: reply.trim() } });
    setReply(""); setOpenCtx(null);
  };
  return (
    <div className="fade-up">
      <div className="flex gap-2 mb-5">
        {[["referrals", "Referrals", ShieldAlert], ["appts", "Appointments", CalendarDays], ["progress", "Progress", BarChart3]].map(([k, t, Icon]) => (
          <button key={k} onClick={() => setTab(k)} className={`flex items-center gap-1.5 px-4 py-2.5 rounded-2xl text-sm font-semibold transition cursor-pointer ${tab === k ? "bg-violet-600 text-white" : "glass text-violet-800"}`}>
            <Icon className="w-4 h-4" />{t}{k === "referrals" && open.length > 0 && <span className="ml-1 bg-red-500 text-white text-[10px] rounded-full px-1.5 py-0.5">{open.length}</span>}
          </button>
        ))}
      </div>
      {tab === "referrals" && (
        <div className="space-y-3">
          {state.referrals.length === 0 && <div className="glass rounded-3xl p-6 text-sm text-violet-800/70">No referrals yet. High-risk cases from Amara and assessments appear here automatically.</div>}
          {state.referrals.map(r => (
            <div key={r.id} className={`glass rounded-3xl p-5 ${r.risk === "high" && r.status === "open" ? "risk-ring" : ""}`}>
              <div className="flex items-center gap-2 flex-wrap">
                <span className="font-bold">{r.student}</span>
                <Pill tone={r.risk === "high" ? "red" : "amber"}>{r.risk === "high" ? "High risk" : "Follow up"}</Pill>
                <span className="text-xs text-violet-800/60 ml-auto">{r.time}</span>
              </div>
              <p className="text-sm mt-2 text-violet-900/85">{r.summary}</p>
              <div className="text-xs text-violet-800/60 mt-1">Source: {r.source}</div>
              {openCtx === r.id && (
                <div className="mt-3 bg-white/70 rounded-2xl p-4 space-y-2">
                  {r.context?.length ? r.context.map((m, i) => (
                    <div key={i} className="text-sm"><span className={`font-bold ${m.role === "user" ? "text-violet-700" : "text-emerald-700"}`}>{m.role === "user" ? r.student.split(" ")[0] : "Amara"}:</span> <span className="text-violet-900/85">{m.text}</span></div>
                  )) : <div className="text-sm text-violet-800/70">No conversation excerpt attached (assessment-based referral).</div>}
                  <div className="flex gap-2 pt-2">
                    <input value={reply} onChange={e => setReply(e.target.value)} placeholder={`Send a note to ${r.student.split(" ")[0]}…`}
                      onKeyDown={e => e.key === "Enter" && sendReply(r)}
                      className="flex-1 glass rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-emerald-300" />
                    <button onClick={() => sendReply(r)} className="rounded-xl px-3 py-2.5 bg-emerald-600 text-white cursor-pointer" aria-label="Send note"><Send className="w-4 h-4" /></button>
                  </div>
                  <p className="text-[11px] text-violet-800/60">Your note appears inside the student's chat, clearly marked as from you.</p>
                </div>
              )}
              {r.status === "open" ? (
                <div className="flex gap-2 mt-3 flex-wrap">
                  <button onClick={() => dispatch({ type: "resolveReferral", id: r.id, counsellor: state.user.name })} className="glow-btn rounded-2xl px-4 py-2.5 text-sm font-semibold cursor-pointer flex items-center gap-1.5"><CheckCircle2 className="w-4 h-4" /> Take case</button>
                  <button onClick={() => setOpenCtx(openCtx === r.id ? null : r.id)} className="rounded-2xl px-4 py-2.5 text-sm font-semibold bg-white/70 text-violet-800 cursor-pointer flex items-center gap-1.5"><MessageCircle className="w-4 h-4" /> {openCtx === r.id ? "Hide context" : "Session context & reply"}</button>
                </div>
              ) : <div className="mt-2 flex items-center gap-2 flex-wrap"><Pill tone="green">In progress with you</Pill><span className="text-xs text-violet-800/60">Follow-up auto-scheduled in {state.settings.followUpHours}h</span></div>}
            </div>
          ))}
        </div>
      )}
      {tab === "appts" && (
        <div className="space-y-3">
          {state.appointments.map(a => (
            <div key={a.id} className="glass rounded-3xl p-5">
              <div className="flex items-center gap-2 flex-wrap"><span className="font-bold">{a.student}</span><Pill tone={a.status === "confirmed" ? "green" : "amber"}>{a.status}</Pill>{a.followUp && <Pill tone="violet">follow-up</Pill>}</div>
              <div className="text-sm mt-1">{a.when} · <span className="text-violet-800/70">{a.reason}</span></div>
              {a.status === "pending" && <button onClick={() => dispatch({ type: "confirm", id: a.id })} className="glow-btn mt-3 rounded-2xl px-4 py-2.5 text-sm font-semibold cursor-pointer">Confirm slot</button>}
            </div>
          ))}
        </div>
      )}
      {tab === "progress" && (
        <div className="space-y-3">
          {seedStudents.map(s => (
            <div key={s.id} className="glass rounded-3xl p-5 flex items-center gap-4">
              <div className="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-300 to-violet-600 flex items-center justify-center text-white font-bold">{s.name[0]}</div>
              <div className="flex-1">
                <div className="font-bold text-sm">{s.name}</div>
                <div className="text-xs text-violet-800/60">{s.dept} · {s.matric}</div>
              </div>
              <div className="text-right">
                <div className="text-2xl">{MOOD_FACES[s.lastMood - 1][0]}</div>
                <div className="text-[10px] text-violet-800/60">latest mood</div>
              </div>
            </div>
          ))}
          <div className="glass rounded-3xl p-5">
            <h4 className="font-bold text-sm mb-2">Adaeze Okonkwo · 7-day mood trend</h4>
            <div className="h-36">
              <ResponsiveContainer width="100%" height="100%">
                <LineChart data={seedMoods} margin={{ top: 8, right: 8, left: -28, bottom: 0 }}>
                  <CartesianGrid stroke="#EDE9FE" vertical={false} />
                  <XAxis dataKey="day" tick={{ fontSize: 11, fill: "#6D4FD0" }} axisLine={false} tickLine={false} />
                  <YAxis domain={[1, 5]} ticks={[1,3,5]} tick={{ fontSize: 11, fill: "#6D4FD0" }} axisLine={false} tickLine={false} />
                  <Tooltip contentStyle={{ borderRadius: 16, border: "1px solid #EDE9FE" }} />
                  <Line type="monotone" dataKey="value" stroke="#DC2626" strokeWidth={2.5} dot={{ r: 3.5, fill: "#DC2626" }} />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

/* ---------------- Admin dashboard ---------------- */
const usageData = [
  { m: "Apr", sessions: 120 }, { m: "May", sessions: 210 }, { m: "Jun", sessions: 340 },
  { m: "Jul", sessions: 290 }, { m: "Aug", sessions: 460 }, { m: "Sep", sessions: 380 },
];
function AdminView({ state, dispatch }) {
  const [line, setLine] = useState(state.settings.crisisLine);
  const [centre, setCentre] = useState(state.settings.centre);
  const [hours, setHours] = useState(state.settings.followUpHours);
  const [saved, setSaved] = useState(false);
  const exportReport = () => {
    const rows = [
      ["MindCare NG — System Report", new Date().toLocaleString("en-NG")], [],
      ["Referrals"], ["Student", "Risk", "Source", "Status", "Time"],
      ...state.referrals.map(r => [r.student, r.risk, r.source, r.status, r.time]), [],
      ["Appointments"], ["Student", "Counsellor", "When", "Status", "Reason"],
      ...state.appointments.map(a => [a.student, a.counsellor, a.when, a.status, a.reason]), [],
      ["Monthly AI sessions"], ["Month", "Sessions"], ...usageData.map(u => [u.m, u.sessions]),
    ];
    const csv = rows.map(r => r.map(c => `"${String(c ?? "").replace(/"/g, '""')}"`).join(",")).join("\n");
    const url = URL.createObjectURL(new Blob([csv], { type: "text/csv" }));
    const a = document.createElement("a"); a.href = url; a.download = "mindcare-report.csv"; a.click();
    URL.revokeObjectURL(url);
  };
  return (
    <div className="space-y-5 fade-up">
      <div className="grid grid-cols-3 gap-3">
        {[["1,248", "Students"], [String(state.referrals.length), "Referrals"], ["96%", "Uptime"]].map(([v, l]) => (
          <div key={l} className="glass rounded-3xl p-4 text-center">
            <div className="mc-serif text-2xl font-bold text-violet-800">{v}</div>
            <div className="text-[11px] font-semibold text-violet-800/60">{l}</div>
          </div>
        ))}
      </div>
      <div className="glass rounded-3xl p-5">
        <div className="flex items-center justify-between mb-2">
          <h4 className="font-bold text-sm">AI counselling sessions per month</h4>
          <button onClick={exportReport} className="flex items-center gap-1.5 text-xs font-bold text-violet-700 bg-violet-100 rounded-xl px-3 py-2 cursor-pointer"><Download className="w-3.5 h-3.5" /> Export report</button>
        </div>
        <div className="h-44">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={usageData} margin={{ top: 8, right: 8, left: -24, bottom: 0 }}>
              <CartesianGrid stroke="#EDE9FE" vertical={false} />
              <XAxis dataKey="m" tick={{ fontSize: 11, fill: "#6D4FD0" }} axisLine={false} tickLine={false} />
              <YAxis tick={{ fontSize: 11, fill: "#6D4FD0" }} axisLine={false} tickLine={false} />
              <Tooltip contentStyle={{ borderRadius: 16, border: "1px solid #EDE9FE" }} cursor={{ fill: "rgba(196,181,253,.2)" }} />
              <Bar dataKey="sessions" fill="#7C5CE0" radius={[10, 10, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>
      <div className="glass rounded-3xl p-5">
        <h4 className="font-bold text-sm mb-3 flex items-center gap-1.5"><Settings className="w-4 h-4 text-violet-600" /> System settings</h4>
        <div className="space-y-3">
          <label className="block text-xs font-semibold text-violet-800/70">Emergency line shown to students
            <input value={line} onChange={e => setLine(e.target.value)} className="mt-1 w-full glass rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-violet-300" /></label>
          <label className="block text-xs font-semibold text-violet-800/70">Campus counselling centre details
            <input value={centre} onChange={e => setCentre(e.target.value)} className="mt-1 w-full glass rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-violet-300" /></label>
          <label className="block text-xs font-semibold text-violet-800/70">Auto follow-up window (hours)
            <input type="number" min="1" value={hours} onChange={e => setHours(Number(e.target.value) || 48)} className="mt-1 w-full glass rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-violet-300" /></label>
          <button onClick={() => { dispatch({ type: "settings", patch: { crisisLine: line, centre, followUpHours: hours } }); setSaved(true); setTimeout(() => setSaved(false), 2000); }}
            className="glow-btn rounded-2xl px-5 py-3 text-sm font-semibold cursor-pointer">{saved ? "Saved ✓" : "Save settings"}</button>
        </div>
      </div>
      <div className="glass rounded-3xl p-5">
        <h4 className="font-bold text-sm mb-3">User management</h4>
        <div className="space-y-2">
          {[...seedStudents.map(s => ({ ...s, role: "Student" })), ...COUNSELLORS.map(c => ({ ...c, dept: c.title, role: "Counsellor" }))].map(u => (
            <div key={u.id} className="flex items-center gap-3 bg-white/60 rounded-2xl px-4 py-3">
              <div className="flex-1 min-w-0"><div className="font-semibold text-sm truncate">{u.name}</div><div className="text-xs text-violet-800/60 truncate">{u.dept}</div></div>
              <Pill tone={u.role === "Student" ? "violet" : "green"}>{u.role}</Pill>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

/* ---------------- Landing + registration + App shell ---------------- */
function RegisterCard({ onDone, onBack }) {
  const [f, setF] = useState({ name: "", matric: "", dept: "", level: "HND II" });
  const ok = f.name.trim() && f.matric.trim() && f.dept.trim();
  return (
    <div className="glass-deep rounded-3xl p-6 w-full max-w-sm text-left msg-in">
      <h3 className="mc-serif text-xl font-semibold">Create your space</h3>
      <p className="text-xs text-violet-800/70 mt-1">Only used to personalise support. Your conversations stay confidential.</p>
      <div className="mt-4 space-y-3">
        <input value={f.name} onChange={e => setF({ ...f, name: e.target.value })} placeholder="Full name" className="w-full glass rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-violet-300" />
        <input value={f.matric} onChange={e => setF({ ...f, matric: e.target.value })} placeholder="Matric number" className="w-full glass rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-violet-300" />
        <input value={f.dept} onChange={e => setF({ ...f, dept: e.target.value })} placeholder="Department" className="w-full glass rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-violet-300" />
        <select value={f.level} onChange={e => setF({ ...f, level: e.target.value })} className="w-full glass rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-violet-300 bg-white/60">
          {["ND I", "ND II", "HND I", "HND II"].map(l => <option key={l}>{l}</option>)}
        </select>
      </div>
      <button onClick={() => onDone(f)} disabled={!ok} className="glow-btn mt-4 w-full rounded-2xl py-3.5 font-semibold disabled:opacity-40 cursor-pointer">Register & continue</button>
      <button onClick={() => onDone({ name: "Adaeze Okonkwo", matric: "F/HD/24/3211045", dept: "Computer Science", level: "HND II" })} className="mt-2 w-full text-sm text-violet-700 font-medium py-2 cursor-pointer">Use demo student instead</button>
      <button onClick={onBack} className="w-full text-xs text-violet-500 py-1 cursor-pointer">Back</button>
    </div>
  );
}

function Landing({ registering, onPick, onRegister, onBack }) {
  return (
    <div className="relative min-h-screen flex flex-col overflow-hidden">
      <Aurora />
      <div className="relative z-10 flex-1 flex flex-col items-center justify-center px-6 py-14 text-center">
        {registering ? <RegisterCard onDone={onRegister} onBack={onBack} /> : (<>
          <Orb size={110} />
          <h1 className="mc-serif text-4xl sm:text-5xl font-semibold mt-8 leading-tight max-w-xl">
            It's okay to not be okay.<br /><span className="shimmer">Talk to Amara.</span>
          </h1>
          <p className="mt-4 max-w-md text-violet-900/75 text-[15px] leading-relaxed">
            Confidential AI counselling for Nigerian students, any hour of the day. Built to listen first, and to connect you with a real campus counsellor whenever you need one.
          </p>
          <div className="mt-8 w-full max-w-sm space-y-3">
            {[["student", "Continue as Student", "Chat, mood tracking, assessments", Heart],
              ["counsellor", "Continue as Counsellor", "Referrals, appointments, progress", Users],
              ["admin", "Continue as Admin", "Users, analytics, settings, reports", BarChart3]].map(([role, t, s, Icon]) => (
              <button key={role} onClick={() => onPick(role)} className="glass-deep w-full rounded-3xl p-4 flex items-center gap-4 text-left hover:bg-white/90 transition cursor-pointer">
                <div className="p-2.5 rounded-2xl bg-violet-100"><Icon className="w-5 h-5 text-violet-700" /></div>
                <div className="flex-1"><div className="font-bold text-sm">{t}</div><div className="text-xs text-violet-800/60">{s}</div></div>
                <ChevronRight className="w-5 h-5 text-violet-400" />
              </button>
            ))}
          </div>
          <div className="mt-8 flex items-center gap-2 text-xs text-violet-800/60"><Sparkles className="w-3.5 h-3.5" /> HND Seminar Project · Dept. of Computer Science, YabaTech · 2026</div>
        </>)}
      </div>
    </div>
  );
}

const STUDENT_TABS = [["chat", "Talk", MessageCircle], ["mood", "Mood", Sun], ["assess", "Check-in", ClipboardList], ["sessions", "Sessions", CalendarDays], ["history", "History", BookOpen]];

function reducer(state, action) {
  switch (action.type) {
    case "pick": {
      if (action.role === "student") return { ...state, registering: true };
      const users = { counsellor: { name: "Mrs. Adebayo Funke", role: "counsellor" }, admin: { name: "Registrar's Office", role: "admin" } };
      return { ...state, user: users[action.role] };
    }
    case "register": return { ...state, registering: false, user: { ...action.profile, role: "student" } };
    case "backToLanding": return { ...state, registering: false };
    case "profile": return { ...state, user: { ...state.user, ...action.patch } };
    case "exit": return { ...state, user: null, registering: false, tab: "chat" };
    case "tab": return { ...state, tab: action.tab };
    case "chat": return { ...state, chat: action.msgs };
    case "counsellorMsg": return { ...state, chat: [...state.chat, action.msg] };
    case "mood": return { ...state, moods: [...state.moods.filter(m => m.day !== "Today"), action.entry] };
    case "assessment": return { ...state, lastAssessment: { ...(state.lastAssessment || {}), ...action.result }, assessments: [action.rec, ...state.assessments] };
    case "book": return { ...state, appointments: [...state.appointments, action.appt] };
    case "confirm": return { ...state, appointments: state.appointments.map(a => a.id === action.id ? { ...a, status: "confirmed" } : a) };
    case "refer": return { ...state, referrals: [action.referral, ...state.referrals] };
    case "resolveReferral": {
      const ref = state.referrals.find(r => r.id === action.id);
      const followUp = ref ? { id: "f" + Date.now(), student: ref.student, counsellor: action.counsellor || "Assigned counsellor", when: `Within ${state.settings.followUpHours}h (auto-scheduled)`, status: "pending", reason: "Referral follow-up support", followUp: true } : null;
      return { ...state, referrals: state.referrals.map(r => r.id === action.id ? { ...r, status: "taken" } : r), appointments: followUp ? [...state.appointments, followUp] : state.appointments };
    }
    case "settings": return { ...state, settings: { ...state.settings, ...action.patch } };
    default: return state;
  }
}

export default function App() {
  const [state, dispatch] = React.useReducer(reducer, {
    user: null, registering: false, tab: "chat", chat: [], moods: seedMoods,
    appointments: seedAppointments, referrals: seedReferrals, lastAssessment: null,
    assessments: [], settings: { crisisLine: "112", centre: "Campus Counselling Centre, Student Affairs Block, 8am to 4pm", followUpHours: 48 },
  });
  const { user, tab } = state;
  return (
    <div className="mc-root min-h-screen">
      <GlobalStyle />
      {!user ? (
        <Landing registering={state.registering}
          onPick={role => dispatch({ type: "pick", role })}
          onRegister={profile => dispatch({ type: "register", profile })}
          onBack={() => dispatch({ type: "backToLanding" })} />
      ) : (
        <div className="relative min-h-screen overflow-hidden">
          <Aurora />
          <div className="relative z-10 max-w-3xl mx-auto flex flex-col min-h-screen px-4 sm:px-6">
            <header className="flex items-center gap-3 py-4">
              <div className="w-9 h-9 orb breathe" />
              <div className="mc-serif font-semibold text-lg">MindCare <span className="text-violet-500">NG</span></div>
              <div className="ml-auto flex items-center gap-2">
                <Pill tone={user.role === "student" ? "violet" : user.role === "counsellor" ? "green" : "amber"}>{user.role}</Pill>
                <button onClick={() => dispatch({ type: "exit" })} aria-label="Sign out" className="glass rounded-2xl p-2.5 cursor-pointer"><LogOut className="w-4 h-4 text-violet-700" /></button>
              </div>
            </header>
            <main className="flex-1 flex flex-col pb-24 sm:pb-8" style={{ minHeight: 0 }}>
              {user.role === "student" && (
                <div className="flex-1 flex flex-col" style={{ minHeight: "70vh" }}>
                  {tab === "chat" && <Chat state={state} dispatch={dispatch} />}
                  {tab === "mood" && <MoodTracker state={state} dispatch={dispatch} />}
                  {tab === "assess" && <Assessment state={state} dispatch={dispatch} />}
                  {tab === "sessions" && <Sessions state={state} dispatch={dispatch} />}
                  {tab === "history" && <History state={state} dispatch={dispatch} />}
                </div>
              )}
              {user.role === "counsellor" && <CounsellorView state={state} dispatch={dispatch} />}
              {user.role === "admin" && <AdminView state={state} dispatch={dispatch} />}
            </main>
            {user.role === "student" && (
              <nav className="fixed bottom-0 left-0 right-0 z-20 sm:static sm:mt-2 sm:mb-4">
                <div className="max-w-3xl mx-auto px-4 sm:px-0 pb-4 sm:pb-0">
                  <div className="glass-deep rounded-3xl p-1.5 flex">
                    {STUDENT_TABS.map(([k, t, Icon]) => (
                      <button key={k} onClick={() => dispatch({ type: "tab", tab: k })}
                        className={`flex-1 flex flex-col items-center gap-0.5 rounded-2xl py-2.5 transition cursor-pointer ${tab === k ? "bg-violet-600 text-white" : "text-violet-700 hover:bg-white/60"}`}>
                        <Icon className="w-5 h-5" /><span className="text-[11px] font-semibold">{t}</span>
                      </button>
                    ))}
                  </div>
                </div>
              </nav>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
