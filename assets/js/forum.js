/* ============================================================
   forum.js — MentalCare System
   Covers: UC-28 (pseudonyms), UC-29 (keyword scan), UC-30 (resources),
           UC-31 (moderation), UC-32 (performance), UC-35 (audit log)
   ============================================================ */

/* ── Shared toast helper ── */
function showToast(msg, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const id = 'toast_' + Date.now();
    const bg  = type === 'success' ? 'var(--primary-green)'
              : type === 'danger'  ? '#dc3545'
              : type === 'warning' ? '#F4B41A'
              : '#343a40';
    container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="toast align-items-center text-white border-0 show"
             style="background:${bg}; border-radius:10px; min-width:260px;"
             role="alert" aria-live="assertive">
            <div class="d-flex">
                <div class="toast-body fw-semibold">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        onclick="this.closest('.toast').remove()"></button>
            </div>
        </div>`);
    setTimeout(() => { const t = document.getElementById(id); if (t) t.remove(); }, 4000);
}

/* ══════════════════════════════════════════════════════════
   UC-28 / UC-29 / UC-30  — PATIENT FORUM
   ══════════════════════════════════════════════════════════ */
const CRISIS_KEYWORDS = ['suicide','kill myself','end my life','can\'t go on','want to die',
                         'no reason to live','hurt myself','harm myself','overdose'];

const PSEUDONYM_POOL = [
    ['SilentBirch','CalmRiver','QuietMoon','WinterFox','SilverPine','NightWillow','MorningDew'],
    ['4027','3312','8821','7740','9931','0055','1182']
];

/* UC-28: Generate or return existing pseudonym */
function getPseudonym() {
    let p = sessionStorage.getItem('mc_pseudonym');
    if (!p) {
        const a = PSEUDONYM_POOL[0][Math.floor(Math.random() * PSEUDONYM_POOL[0].length)];
        const b = PSEUDONYM_POOL[1][Math.floor(Math.random() * PSEUDONYM_POOL[1].length)];
        p = `${a}_${b}`;
        sessionStorage.setItem('mc_pseudonym', p);
    }
    return p;
}

function getAvatarInitials(pseudonym) {
    const parts = pseudonym.split('_');
    return (parts[0].slice(0,1) + (parts[1] ? parts[1].slice(0,1) : '')).toUpperCase();
}

const AVATAR_COLORS = ['#2F8F7E','#48B6A2','#F4B41A','#8F5E2F','#6f42c1','#e67e22','#dc6fa0'];
function randomColor() { return AVATAR_COLORS[Math.floor(Math.random() * AVATAR_COLORS.length)]; }

/* UC-29: Keyword scan */
function scanForCrisis(text) {
    const lower = text.toLowerCase();
    return CRISIS_KEYWORDS.some(kw => lower.includes(kw));
}

/* UC-30: Emergency resources data */
const RESOURCES = {
    eg:   [{ name:'Nefsy (Egypt)', phone:'08008880700', note:'Free hotline, 24/7' },
            { name:'Mental Health Helpline', phone:'08008880700', note:'Ministry of Health' }],
    us:   [{ name:'988 Suicide & Crisis Lifeline', phone:'988', note:'Call or text, 24/7' },
            { name:'Crisis Text Line', phone:'Text HOME to 741741', note:'Free, 24/7' }],
    uk:   [{ name:'Samaritans', phone:'116 123', note:'Free, 24/7' },
            { name:'PAPYRUS HOPElineUK', phone:'0800 068 4141', note:'Youth crisis support' }],
    intl: [{ name:'International Association for Suicide Prevention',
              phone:'https://www.iasp.info/resources/Crisis_Centres/', note:'Find local centres' }]
};

function showEmergencyResources() {
    const modal = new bootstrap.Modal(document.getElementById('emergencyModal'));
    modal.show();
}

function loadResources() {
    const region = document.getElementById('regionSelect').value;
    const list   = document.getElementById('resourcesList');
    if (!region) { list.innerHTML = ''; return; }
    const items = RESOURCES[region] || RESOURCES.intl;
    list.innerHTML = items.map(r => `
        <div class="d-flex align-items-start gap-3 p-3 mb-2 rounded-3"
             style="background:#fff8f5; border:1px solid #ffd6c0;">
            <i class="bi bi-telephone-fill text-danger mt-1"></i>
            <div>
                <strong class="d-block">${r.name}</strong>
                <span class="text-danger fw-bold">${r.phone}</span>
                <small class="text-secondary-custom d-block">${r.note}</small>
            </div>
        </div>`).join('');
}

/* UC-28: React to post */
function reactPost(btn, type) {
    const countEl = btn.querySelector('.react-count');
    if (!countEl) return;
    const wasActive = btn.classList.contains('reacted');
    btn.classList.toggle('reacted');
    countEl.textContent = wasActive
        ? parseInt(countEl.textContent) - 1
        : parseInt(countEl.textContent) + 1;
    if (!wasActive) { btn.style.color = 'var(--primary-green)'; btn.style.background = 'var(--light-green)'; }
    else            { btn.style.color = ''; btn.style.background = ''; }
}

/* UC-31: Flag a post from patient side */
function flagPost(btn) {
    const card = btn.closest('.forum-post-card');
    card.classList.add('flagged-post');
    btn.innerHTML = '<i class="bi bi-flag-fill me-1"></i>Flagged';
    btn.classList.add('text-danger');
    btn.disabled = true;
    showToast('Post reported to moderators. Thank you.', 'success');
}

/* Patient Forum — initialise */
(function initPatientForum() {
    const pseudonymDisplay = document.getElementById('pseudonymDisplay');
    if (!pseudonymDisplay) return;          // not on patient forum page

    const p = getPseudonym();
    const initials = getAvatarInitials(p);
    const color    = sessionStorage.getItem('mc_avatar_color') || (() => {
        const c = randomColor(); sessionStorage.setItem('mc_avatar_color', c); return c;
    })();

    // Set pseudonym displays
    document.getElementById('pseudonymText').textContent       = p;
    const widgetEl = document.getElementById('widgetPseudonym');
    if (widgetEl) widgetEl.textContent = p;
    const avatar   = document.getElementById('composeAvatar');
    if (avatar) { avatar.textContent = initials; avatar.style.background = color; }

    // UC-28 Alt: Reset pseudonym
    document.getElementById('btnResetPseudonym')?.addEventListener('click', () => {
        sessionStorage.removeItem('mc_pseudonym');
        sessionStorage.removeItem('mc_avatar_color');
        showToast('Your pseudonym has been reset. Reload to see your new identity.', 'warning');
    });

    // UC-29: Live keyword scan while typing
    const textarea = document.getElementById('postContent');
    const alertEl  = document.getElementById('keywordAlert');
    textarea?.addEventListener('input', () => {
        if (alertEl) alertEl.style.display = scanForCrisis(textarea.value) ? 'block' : 'none';
    });

    // Submit post
    document.getElementById('btnSubmitPost')?.addEventListener('click', () => {
        const content  = textarea?.value.trim();
        const category = document.getElementById('postCategory')?.value;
        if (!content) { showToast('Please write something before posting.', 'warning'); return; }

        const isCrisis = scanForCrisis(content);
        if (isCrisis) {
            showToast('⚠️ Crisis keywords detected. Moderators have been alerted.', 'danger');
        }

        const feed = document.getElementById('postsFeed');
        const categoryLabels = {
            general: '💬 General Support', anxiety: '😰 Anxiety & Stress',
            depression: '🌧️ Depression', recovery: '🌱 Recovery Journey', gratitude: '🙏 Gratitude'
        };
        const card = document.createElement('div');
        card.className = 'forum-post-card p-4' + (isCrisis ? ' crisis-post' : '');
        card.dataset.category = category;
        card.innerHTML = `
            <div class="d-flex align-items-start gap-3">
                <div class="avatar-anon" style="background:${color};">${initials}</div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <strong class="text-primary-custom">${p}</strong>
                        <span class="badge bg-light text-primary-custom border" style="font-size:.75rem;">${categoryLabels[category]}</span>
                        <small class="text-secondary-custom ms-auto">Just now</small>
                        ${isCrisis ? '<span class="badge bg-danger ms-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Flagged</span>' : ''}
                    </div>
                    <p class="mb-3">${content.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</p>
                    <div class="d-flex align-items-center gap-2 flex-wrap border-top pt-2">
                        <button class="reaction-btn" onclick="reactPost(this,'heart')"><i class="bi bi-heart me-1"></i><span class="react-count">0</span></button>
                        <button class="reaction-btn" onclick="reactPost(this,'hug')"><i class="bi bi-emoji-smile me-1"></i><span class="react-count">0</span></button>
                        <button class="reaction-btn ms-auto text-danger flag-btn" onclick="flagPost(this)"><i class="bi bi-flag me-1"></i>Flag</button>
                    </div>
                </div>
            </div>`;
        feed.prepend(card);
        textarea.value = '';
        if (alertEl) alertEl.style.display = 'none';
        showToast(isCrisis ? 'Posted — moderators notified due to sensitive content.' : 'Posted anonymously!', isCrisis ? 'warning' : 'success');
    });

    // Filter chips
    document.querySelectorAll('.filter-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            const filter = chip.dataset.filter;
            document.querySelectorAll('#postsFeed .forum-post-card').forEach(card => {
                card.style.display = (filter === 'all' || card.dataset.category === filter) ? '' : 'none';
            });
        });
    });
})();

/* ══════════════════════════════════════════════════════════
   UC-31  — MODERATION CONTROLLER
   ══════════════════════════════════════════════════════════ */
const Forum = (() => {

    const STATUS_CONFIG = {
        'under-review': { label:'Under Review', cls:'status-under-review', icon:'bi-hourglass-split', accentCls:'accent-review' },
        'hidden':       { label:'Hidden',        cls:'status-hidden',       icon:'bi-eye-slash',      accentCls:'accent-hidden' },
        'deleted':      { label:'Deleted',       cls:'status-deleted',      icon:'bi-trash3-fill',    accentCls:'accent-crisis' },
        'cleared':      { label:'Cleared',       cls:'status-cleared',      icon:'bi-check-circle-fill', accentCls:'accent-review' }
    };

    function moderatePost(btn, newStatus) {
        const card   = btn.closest('.mod-post-card');
        const postId = card.dataset.postid;
        const noteEl = card.querySelector('.mod-note');
        const note   = noteEl?.value.trim() || '(no note)';
        const cfg    = STATUS_CONFIG[newStatus];

        // Update badge in header
        const badgeEl = card.querySelector('.status-badge');
        if (badgeEl) {
            badgeEl.className = `status-badge ${cfg.cls}`;
            badgeEl.innerHTML = `<i class="bi ${cfg.icon}"></i> ${cfg.label}`;
        }

        // Update accent bar
        const accent = card.querySelector('.card-accent');
        if (accent) { accent.className = `card-accent ${cfg.accentCls}`; }

        // Append to timeline
        const timeline = document.getElementById(`timeline-${postId}`);
        if (timeline) {
            const dotColor = newStatus === 'deleted' ? '#dc3545'
                           : newStatus === 'hidden'  ? '#adb5bd'
                           : newStatus === 'cleared' ? 'var(--primary-green)'
                           : '#ffc107';
            const now = new Date().toLocaleString('en-GB', { hour12:false }).replace(',','');
            timeline.insertAdjacentHTML('beforeend', `
                <div class="d-flex gap-2 align-items-start mt-1">
                    <div class="timeline-dot" style="background:${dotColor}; margin-top:5px;"></div>
                    <span><strong>${now}</strong> — ${cfg.label} by <em>Sarah M.</em> — "${note}"</span>
                </div>`);
        }

        // Update counter badges
        updateCounters();

        // If deleted, fade card
        if (newStatus === 'deleted') {
            card.style.opacity = '0.45';
            card.querySelectorAll('button:not(.timeline-btn)').forEach(b => b.disabled = true);
        }

        card.dataset.status = newStatus;
        showToast(`Post ${cfg.label}.`, newStatus === 'deleted' ? 'danger' : 'success');
    }

    function escalateToAdmin(btn) {
        const card = btn.closest('.mod-post-card');
        const noteEl = card.querySelector('.mod-note');
        const note   = noteEl?.value.trim() || '(no note)';
        showToast('🚨 Post escalated to Admin. Audit entry created.', 'danger');
        const postId  = card.dataset.postid;
        const timeline = document.getElementById(`timeline-${postId}`);
        if (timeline) {
            const now = new Date().toLocaleString('en-GB', { hour12:false }).replace(',','');
            timeline.insertAdjacentHTML('beforeend', `
                <div class="d-flex gap-2 align-items-start mt-1">
                    <div class="timeline-dot" style="background:#dc3545; margin-top:5px;"></div>
                    <span><strong>${now}</strong> — Escalated to Admin by <em>Sarah M.</em> — "${note}"</span>
                </div>`);
        }
    }

    function updateCounters() {
        const all    = document.querySelectorAll('.mod-post-card');
        const count  = s => [...all].filter(c => c.dataset.status === s).length;
        const f = document.getElementById('countFlagged');
        const r = document.getElementById('countReview');
        const h = document.getElementById('countHidden');
        if (f) f.textContent = count('flagged');
        if (r) r.textContent = count('under-review');
        if (h) h.textContent = count('hidden');
    }

    // Filter tabs
    document.querySelectorAll('#modFilterBar .mod-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.mod-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const filter = tab.dataset.filter;
            document.querySelectorAll('.mod-post-card').forEach(card => {
                const match = filter === 'all' || card.dataset.category === filter || card.dataset.status === filter;
                card.style.display = match ? '' : 'none';
            });
        });
    });

    return { moderatePost, escalateToAdmin };
})();

/* ══════════════════════════════════════════════════════════
   UC-35  — AUDIT LOG CONTROLLER
   ══════════════════════════════════════════════════════════ */
const Audit = (() => {

    function appendAction(evtKey, btn) {
        const typeEl = document.getElementById(`actionType-${evtKey}`);
        const noteEl = document.getElementById(`actionNote-${evtKey}`);
        const formEl = document.getElementById(`actionForm-${evtKey}`);
        if (!typeEl) return;

        const actionLabel = typeEl.options[typeEl.selectedIndex].text;
        const note        = noteEl?.value.trim() || '(no note)';
        const now         = new Date().toLocaleString('en-GB', { hour12:false }).replace(',','');

        // Append to the timeline above the form
        const timeline = formEl?.previousElementSibling;
        if (timeline && timeline.classList.contains('audit-timeline')) {
            timeline.insertAdjacentHTML('beforeend', `
                <div class="tl-item">
                    <div class="tl-dot tl-dot-mod"></div>
                    <div><strong>${now}</strong> — Sarah M.: <em>${actionLabel}</em> — "${note}"</div>
                </div>`);
        }
        if (noteEl) noteEl.value = '';
        showToast('Action appended to audit record.', 'success');
    }

    function tryTamper(btn, evtId) {
        // Derive key from evt ID e.g. "EVT-2026-0481" → "evt0481"
        const key = evtId.replace('EVT-2026-','evt').replace('-','');
        const warnEl = document.getElementById(`tamperWarn-${key}`);
        if (warnEl) { warnEl.style.display = 'block'; }
        showToast('❌ Modification denied — entry is WORM-protected. Attempt logged.', 'danger');
        btn.disabled = true;
    }

    // Severity filter chips
    document.querySelectorAll('[data-sevfilter]').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('[data-sevfilter]').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            const filter = chip.dataset.sevfilter;
            document.querySelectorAll('#auditLogContainer .audit-entry').forEach(entry => {
                entry.style.display = (filter === 'all' || entry.dataset.severity === filter) ? '' : 'none';
            });
        });
    });

    // Search
    document.getElementById('auditSearch')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#auditLogContainer .audit-entry').forEach(entry => {
            entry.style.display = entry.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    return { appendAction, tryTamper };
})();

/* ══════════════════════════════════════════════════════════
   UC-32  — PERFORMANCE METRICS CONTROLLER
   ══════════════════════════════════════════════════════════ */
const Performance = (() => {

    const DATA = {
        t1: { name:'Dr. Sarah Harding', initials:'SH', color:'#2F8F7E', spec:'Specialisation: Anxiety & CBT · 5 years experience',
               sessions:187, patients:34, noShow:'3.2%',
               breakdown:[72,18,7,2,1],
               trend:[60,65,72,78,80,90,98],
               feedback:[
                   { stars:5, text:'"Incredibly supportive and always listens carefully."', age:'2 days ago' },
                   { stars:4, text:'"Great therapist. Would appreciate slightly longer sessions."', age:'5 days ago' }
               ]},
        t2: { name:'Dr. James Okafor', initials:'JO', color:'#48B6A2', spec:'Specialisation: Depression & Grief · 8 years experience',
               sessions:142, patients:27, noShow:'5.1%',
               breakdown:[52,28,12,5,3],
               trend:[55,60,58,65,70,75,78],
               feedback:[
                   { stars:5, text:'"Very insightful and professional."', age:'1 day ago' },
                   { stars:4, text:'"Helpful but sometimes sessions run short."', age:'3 days ago' }
               ]},
        t3: { name:'Dr. Lena Novak', initials:'LN', color:'#F4B41A', spec:'Specialisation: Trauma & PTSD · 6 years experience',
               sessions:103, patients:19, noShow:'7.8%',
               breakdown:[40,30,18,8,4],
               trend:[45,50,52,55,60,65,68],
               feedback:[
                   { stars:4, text:'"Empathetic and thorough."', age:'4 days ago' },
                   { stars:3, text:'"Good but I\'d like more structure."', age:'1 week ago' }
               ]},
        t4: { name:'Dr. Yusuf Al-Amin', initials:'YA', color:'#8F5E2F', spec:'Specialisation: Family Therapy · 3 years experience',
               sessions:80, patients:14, noShow:'10.2%',
               breakdown:[28,25,22,15,10],
               trend:[30,35,38,40,42,44,48],
               feedback:[]   // triggers "no data" alt scenario for feedback
             }
    };

    const STAR_ICONS = n => {
        let s = '';
        for (let i = 1; i <= 5; i++) {
            s += `<i class="bi bi-star${i <= n ? '-fill star-filled' : '-empty star-empty'}" style="font-size:.75rem;"></i>`;
        }
        return s;
    };

    function selectTherapist(tid, rowEl) {
        const d = DATA[tid];
        if (!d) return;

        // Highlight row
        document.querySelectorAll('.therapist-row').forEach(r => r.classList.remove('selected'));
        if (rowEl) rowEl.classList.add('selected');

        // Header
        const avatar = document.getElementById('detailAvatar');
        if (avatar) { avatar.textContent = d.initials; avatar.style.background = d.color; }
        const nameEl = document.getElementById('detailName');
        if (nameEl) nameEl.textContent = d.name;
        const specEl = document.getElementById('detailSpec');
        if (specEl) specEl.innerHTML = d.spec;

        // KPIs
        const s = document.getElementById('dKpiSessions'); if (s) s.textContent = d.sessions;
        const p = document.getElementById('dKpiPatients'); if (p) p.textContent = d.patients;
        const n = document.getElementById('dKpiNoShow');   if (n) n.textContent = d.noShow;

        // Rating breakdown bars
        const bk = document.getElementById('ratingBreakdown');
        if (bk) {
            const labels = ['5 Stars','4 Stars','3 Stars','2 Stars','1 Star'];
            bk.innerHTML = d.breakdown.map((pct, i) => {
                const cls   = pct <= 5 ? 'danger' : pct <= 15 ? 'warn' : '';
                const color = cls === 'danger' ? '#dc3545' : cls === 'warn' ? '#F4B41A' : 'var(--primary-green)';
                return `<div class="chart-row">
                    <span class="chart-label"><i class="bi bi-star-fill star-filled me-1" style="font-size:.75rem;"></i>${labels[i]}</span>
                    <div class="chart-track"><div class="chart-fill ${cls}" style="width:${pct}%"></div></div>
                    <span class="chart-value" style="color:${color};">${pct}%</span>
                </div>`;
            }).join('');
        }

        // Sparkline
        const sp = document.getElementById('sparkline');
        if (sp) {
            sp.innerHTML = d.trend.map(h =>
                `<div class="spark-bar" style="height:${h}%;" title="${(h/20).toFixed(1)}"></div>`
            ).join('');
        }

        // Feedback
        const fl       = document.getElementById('feedbackList');
        const noData   = document.getElementById('noDataState');
        if (d.feedback.length === 0) {
            if (fl)     fl.innerHTML = '';
            if (noData) noData.classList.remove('d-none');
        } else {
            if (noData) noData.classList.add('d-none');
            if (fl) {
                fl.innerHTML = d.feedback.map(f => `
                    <div class="feedback-item">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div class="star-rating">${STAR_ICONS(f.stars)}</div>
                            <small class="text-secondary-custom">${f.age}</small>
                        </div>
                        ${f.text}
                    </div>`).join('');
            }
        }
    }

    // Period filter buttons
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            showToast(`Showing data for last ${btn.dataset.period} days.`, 'success');
        });
    });

    // Therapist dropdown + Apply
    document.getElementById('btnApplyFilter')?.addEventListener('click', () => {
        const val = document.getElementById('therapistFilter')?.value;
        if (val && val !== 'all') {
            const row = document.querySelector(`[data-tid="${val}"]`);
            selectTherapist(val, row);
        } else {
            showToast('Showing all therapists.', 'success');
        }
    });

    return { selectTherapist };
})();
