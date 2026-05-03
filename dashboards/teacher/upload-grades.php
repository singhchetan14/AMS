<?php
/**
 * upload-grades.php
 *
 * AJAX-powered grade management page.
 * - Select Group → courses load automatically (AJAX)
 * - Select Course → all students in that group shown with their marks
 * - Inline edit marks, auto-calculate Pass/Fail
 * - Save or Delete individual grades
 */

require_once 'includes/auth.php';
require_once 'config/db.php';

$teacherId = (int)$_SESSION['teacher_id'];

// ══════════════════════════════════════════════════════════════════════
// AJAX ENDPOINTS  (return JSON when ?action= is present)
// ══════════════════════════════════════════════════════════════════════
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    // ── Get courses for a group ──────────────────────────────────────
    if ($action === 'courses') {
        $group = trim($_GET['group'] ?? '');
        if (!$group) { echo json_encode([]); exit; }
        $stmt = $pdo->prepare("
            SELECT id, name
            FROM courses
            WHERE teacher_id = ? AND group_name = ?
            ORDER BY name ASC
        ");
        $stmt->execute([$teacherId, $group]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // ── Get students + their grades for a course/group ───────────────
    if ($action === 'grades') {
        $courseId = (int)($_GET['course'] ?? 0);
        $group    = trim($_GET['group'] ?? '');
        if (!$courseId || !$group) { echo json_encode(['error' => 'Missing params']); exit; }

        // LEFT JOIN so we get students even if they have no grade yet
        $stmt = $pdo->prepare("
            SELECT
                s.id       AS student_id,
                s.student_no,
                s.full_name,
                g.id       AS grade_id,
                g.score,
                g.status
            FROM students s
            LEFT JOIN grades g
                   ON g.student_id = s.id
                  AND g.course_id  = ?
                  AND g.teacher_id = ?
            WHERE s.group_name = ?
            ORDER BY s.student_no ASC
        ");
        $stmt->execute([$courseId, $teacherId, $group]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // ── Save (insert or update) a grade ─────────────────────────────
    if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data      = json_decode(file_get_contents('php://input'), true);
        $courseId  = (int)($data['course_id']  ?? 0);
        $studentId = (int)($data['student_id'] ?? 0);
        $score     = isset($data['score']) ? (int)$data['score'] : -1;

        if (!$courseId || !$studentId || $score < 0 || $score > 100) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']); exit;
        }

        // Verify course belongs to this teacher
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$courseId, $teacherId]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit;
        }

        $status = $score >= 50 ? 'Pass' : 'Fail';
        $stmt = $pdo->prepare("
            INSERT INTO grades (student_id, course_id, score, status, teacher_id)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE score = VALUES(score), status = VALUES(status)
        ");
        $stmt->execute([$studentId, $courseId, $score, $status, $teacherId]);

        // Return the grade id (needed by UI to enable Delete)
        $stmt = $pdo->prepare("SELECT id FROM grades WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$studentId, $courseId]);
        $row = $stmt->fetch();

        echo json_encode([
            'success'  => true,
            'grade_id' => $row ? (int)$row['id'] : null,
            'status'   => $status,
        ]);
        exit;
    }

    // ── Delete a grade ───────────────────────────────────────────────
    if ($action === 'delete') {
        $gradeId = (int)($_GET['id'] ?? 0);
        if (!$gradeId) { echo json_encode(['success' => false]); exit; }
        $stmt = $pdo->prepare("DELETE FROM grades WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$gradeId, $teacherId]);
        echo json_encode(['success' => $stmt->rowCount() > 0]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Unknown action']);
    exit;
}

// ══════════════════════════════════════════════════════════════════════
// PAGE RENDER
// ══════════════════════════════════════════════════════════════════════

// Fetch all groups this teacher has, with student count per group
$stmt = $pdo->prepare("
    SELECT
        c.group_name,
        COUNT(DISTINCT s.id) AS student_count
    FROM courses c
    LEFT JOIN students s ON s.group_name = c.group_name
    WHERE c.teacher_id = ?
    GROUP BY c.group_name
    ORDER BY c.group_name ASC
");
$stmt->execute([$teacherId]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Upload Grades | Academic Management System</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        body { background: #0d1b2a; color: #fff; font-family: "Inter","Segoe UI",Roboto,Arial,sans-serif; margin: 0; }

        /* ── Page wrap ──────────────────────────────────────────────── */
        .grades-wrap {
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 36px 20px;
        }
        .grades-card {
            background: #1b304c;
            border-radius: 14px;
            padding: 40px;
            width: 100%;
            max-width: 920px;
            box-shadow: 0 6px 30px rgba(0,0,0,0.45);
        }

        /* ── Header row ─────────────────────────────────────────────── */
        .pg-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 36px;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #c8d5e5;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }
        .back-link:hover { color: #fff; }
        .pg-title { font-size: 20px; font-weight: 400; margin: 0; }

        /* ── Step labels ────────────────────────────────────────────── */
        .step-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #8b99a8;
            margin-bottom: 12px;
        }

        /* ── Group pills ────────────────────────────────────────────── */
        .group-pills {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 32px;
        }
        .g-pill {
            padding: 9px 22px;
            border-radius: 22px;
            border: 1px solid rgba(255,255,255,0.18);
            background: transparent;
            color: #c8d5e5;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .g-pill:hover { background: rgba(255,255,255,0.07); border-color: rgba(255,255,255,0.35); }
        .g-pill.active {
            background: #1967d2;
            border-color: #1967d2;
            color: #fff;
            font-weight: 600;
        }
        .g-pill .badge { display: none; }

        /* ── Course selector ────────────────────────────────────────── */
        #course-section { display: none; margin-bottom: 32px; }
        .course-sel {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.05);
            color: #fff;
            font-size: 14px;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .course-sel:focus { border-color: #1967d2; }
        .course-sel option { background: #1b304c; }

        /* ── Grades area ────────────────────────────────────────────── */
        #grades-section { display: none; }
        .grades-hdr {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            flex-wrap: wrap;
            gap: 8px;
        }
        .breadcrumb { font-size: 15px; color: #c8d5e5; }
        .breadcrumb strong { color: #fff; }
        .dist-badge {
            font-size: 12px;
            background: rgba(25,103,210,0.18);
            border: 1px solid rgba(25,103,210,0.35);
            color: #90b8ff;
            padding: 4px 12px;
            border-radius: 10px;
        }

        /* ── Grade table ────────────────────────────────────────────── */
        .g-table { width: 100%; border-collapse: collapse; }
        .g-table thead tr { background: rgba(255,255,255,0.04); }
        .g-table th {
            padding: 10px 14px;
            font-weight: 500;
            font-size: 12px;
            color: #8b99a8;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .g-table td {
            padding: 11px 14px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 14px;
            vertical-align: middle;
        }
        .g-table tbody tr:last-child td { border-bottom: none; }
        .g-table tbody tr:hover { background: rgba(255,255,255,0.025); }

        .score-inp {
            width: 72px;
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.06);
            color: #fff;
            font-size: 14px;
            text-align: center;
            outline: none;
            transition: border-color 0.2s;
        }
        .score-inp:focus { border-color: #1967d2; }

        .s-badge {
            display: inline-block;
            padding: 3px 11px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.03em;
        }
        .s-pass  { background: rgba(0,200,80,0.15);  color: #5aea96; }
        .s-fail  { background: rgba(255,70,70,0.15);  color: #ff8080; }
        .s-none  { background: rgba(255,255,255,0.07); color: #8b99a8; font-weight: 400; }

        .act-btns { display: flex; gap: 8px; }
        .btn-save {
            padding: 5px 14px;
            background: #1967d2;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-save:hover:not(:disabled) { opacity: 0.82; }
        .btn-save:disabled { opacity: 0.35; cursor: default; }
        .btn-del {
            padding: 5px 11px;
            background: rgba(255,70,70,0.12);
            color: #ff8080;
            border: 1px solid rgba(255,70,70,0.28);
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-del:hover:not(:disabled) { background: rgba(255,70,70,0.22); }
        .btn-del:disabled { opacity: 0.3; cursor: default; }

        /* ── States ─────────────────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: #8b99a8;
            font-size: 14px;
        }
        .spinner {
            display: inline-block;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.15);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.65s linear infinite;
            vertical-align: middle;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Toast ──────────────────────────────────────────────────── */
        #toast {
            position: fixed;
            bottom: 28px; right: 28px;
            background: #0f2232;
            border: 1px solid rgba(25,103,210,0.45);
            color: #fff;
            padding: 13px 20px;
            border-radius: 10px;
            font-size: 14px;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            pointer-events: none;
            z-index: 9999;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }
        #toast.show        { opacity: 1; transform: translateY(0); }
        #toast.t-success   { border-color: rgba(0,200,80,0.45); }
        #toast.t-error     { border-color: rgba(255,80,80,0.45); }
    </style>
</head>
<body class="no-sidebar">

<div class="grades-wrap">
    <div class="grades-card">

        <div class="pg-header">
            <a href="dashboard.php" class="back-link">
                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 8 8 12 12 16"/>
                    <line x1="16" y1="12" x2="8" y2="12"/>
                </svg>
                Back
            </a>
            <h2 class="pg-title">Upload Grades</h2>
        </div>

        <!-- ── Step 1: Group ───────────────────────────────────────── -->
        <div class="step-label">Step 1 — Select Group</div>
        <div class="group-pills">
            <?php foreach ($groups as $g): ?>
                <button class="g-pill"
                        data-group="<?= htmlspecialchars($g['group_name']) ?>"
                        data-count="<?= (int)$g['student_count'] ?>">
                    Group <?= htmlspecialchars($g['group_name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- ── Step 2: Course ──────────────────────────────────────── -->
        <div id="course-section">
            <div class="step-label">Step 2 — Select Course</div>
            <select id="course-sel" class="course-sel">
                <option value="">— Loading courses… —</option>
            </select>
        </div>

        <!-- ── Step 3: Grades table ────────────────────────────────── -->
        <div id="grades-section">
            <div class="grades-hdr">
                <div class="breadcrumb">
                    Group <strong id="bc-group">—</strong>
                    &rsaquo;
                    <strong id="bc-course">—</strong>
                </div>
                <div class="dist-badge" id="dist-badge"></div>
            </div>
            <div id="grades-wrap">
                <div class="empty-state">Select a course above to load grades.</div>
            </div>
        </div>

    </div>
</div>

<div id="toast"></div>

<script>
/* ── Globals ─────────────────────────────────────────────────────── */
let selGroup  = '';
let selCourse = '';
let selCourseName = '';

/* ── Toast ───────────────────────────────────────────────────────── */
function toast(msg, type = 'success') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'show t-' + type;
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.className = ''; }, 3200);
}

/* ── Group pill click ─────────────────────────────────────────────── */
document.querySelectorAll('.g-pill').forEach(pill => {
    pill.addEventListener('click', () => {
        document.querySelectorAll('.g-pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        selGroup = pill.dataset.group;

        // Reset lower steps
        document.getElementById('grades-section').style.display = 'none';
        const cs = document.getElementById('course-section');
        cs.style.display = 'block';

        const sel = document.getElementById('course-sel');
        sel.innerHTML = '<option value="">Loading…</option>';

        fetch(`upload-grades.php?action=courses&group=${encodeURIComponent(selGroup)}`)
            .then(r => r.json())
            .then(list => {
                if (!list.length) {
                    sel.innerHTML = '<option value="">No courses for this group</option>';
                    return;
                }
                sel.innerHTML = '<option value="">— Select a course —</option>';
                list.forEach(c => {
                    const o = document.createElement('option');
                    o.value = c.id;
                    o.textContent = c.name;
                    sel.appendChild(o);
                });
            })
            .catch(() => {
                sel.innerHTML = '<option value="">Failed to load courses</option>';
                toast('Could not load courses', 'error');
            });
    });
});

/* ── Course change ────────────────────────────────────────────────── */
document.getElementById('course-sel').addEventListener('change', function () {
    selCourse     = this.value;
    selCourseName = this.options[this.selectedIndex]?.text || '';

    if (!selCourse) {
        document.getElementById('grades-section').style.display = 'none';
        return;
    }

    document.getElementById('bc-group').textContent  = selGroup;
    document.getElementById('bc-course').textContent = selCourseName;
    document.getElementById('grades-section').style.display = 'block';
    document.getElementById('grades-wrap').innerHTML =
        '<div class="empty-state"><span class="spinner"></span> Loading grades…</div>';
    document.getElementById('dist-badge').textContent = '';

    loadGrades();
});

/* ── Load grade rows ──────────────────────────────────────────────── */
function loadGrades() {
    const url = `upload-grades.php?action=grades&course=${selCourse}&group=${encodeURIComponent(selGroup)}`;
    fetch(url)
        .then(r => r.json())
        .then(rows => renderTable(rows))
        .catch(() => {
            document.getElementById('grades-wrap').innerHTML =
                '<div class="empty-state" style="color:#ff8080">Failed to load grades.</div>';
        });
}

/* ── Render table ─────────────────────────────────────────────────── */
function renderTable(students) {
    const wrap = document.getElementById('grades-wrap');

    if (!students.length) {
        wrap.innerHTML = '<div class="empty-state">No students found in this group.</div>';
        return;
    }

    const graded = students.filter(s => s.grade_id !== null).length;
    document.getElementById('dist-badge').textContent =
        `${graded} / ${students.length} graded`;

    const rows = students.map((s, i) => {
        const has    = s.grade_id !== null;
        const score  = has ? s.score : '';
        const stCls  = has ? (s.status === 'Pass' ? 's-pass' : 's-fail') : 's-none';
        const stTxt  = has ? s.status : '—';

        return `<tr data-sid="${s.student_id}" data-gid="${s.grade_id ?? ''}">
            <td style="color:#676e7a;font-size:13px;">${i + 1}</td>
            <td style="color:#8b99a8;font-size:13px;">${h(s.student_no)}</td>
            <td>${h(s.full_name)}</td>
            <td>
                <input class="score-inp" type="number" min="0" max="100"
                       value="${h(String(score))}" placeholder="0–100" />
            </td>
            <td><span class="s-badge ${stCls}">${stTxt}</span></td>
            <td>
                <div class="act-btns">
                    <button class="btn-save" onclick="saveGrade(this)">Save</button>
                    <button class="btn-del"  onclick="delGrade(this)"
                            ${!has ? 'disabled' : ''}>Delete</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    wrap.innerHTML = `
        <table class="g-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>S.No.</th>
                    <th>Student Name</th>
                    <th>Score / 100</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>`;
}

/* ── Save grade ───────────────────────────────────────────────────── */
function saveGrade(btn) {
    const row   = btn.closest('tr');
    const inp   = row.querySelector('.score-inp');
    const score = parseInt(inp.value, 10);

    if (isNaN(score) || score < 0 || score > 100) {
        toast('Score must be 0 – 100', 'error');
        inp.focus();
        return;
    }

    btn.disabled = true;
    btn.textContent = '…';

    fetch('upload-grades.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            course_id:  parseInt(selCourse, 10),
            student_id: parseInt(row.dataset.sid, 10),
            score
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            row.dataset.gid = d.grade_id;
            const badge = row.querySelector('.s-badge');
            badge.textContent = d.status;
            badge.className = 's-badge ' + (d.status === 'Pass' ? 's-pass' : 's-fail');
            row.querySelector('.btn-del').disabled = false;
            updateDist();
            toast('✓ Grade saved');
        } else {
            toast(d.error || 'Failed to save', 'error');
        }
    })
    .catch(() => toast('Network error', 'error'))
    .finally(() => { btn.disabled = false; btn.textContent = 'Save'; });
}

/* ── Delete grade ─────────────────────────────────────────────────── */
function delGrade(btn) {
    const row = btn.closest('tr');
    const gid = row.dataset.gid;
    if (!gid || !confirm('Delete this grade record?')) return;

    btn.disabled = true;

    fetch(`upload-grades.php?action=delete&id=${gid}`)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                row.dataset.gid = '';
                row.querySelector('.score-inp').value = '';
                const badge = row.querySelector('.s-badge');
                badge.textContent = '—';
                badge.className = 's-badge s-none';
                btn.disabled = true;
                updateDist();
                toast('Grade deleted');
            } else {
                toast('Could not delete grade', 'error');
                btn.disabled = false;
            }
        })
        .catch(() => { toast('Network error', 'error'); btn.disabled = false; });
}

/* ── Refresh graded count ─────────────────────────────────────────── */
function updateDist() {
    const rows   = document.querySelectorAll('.g-table tbody tr');
    const graded = [...rows].filter(r => r.dataset.gid !== '').length;
    document.getElementById('dist-badge').textContent = `${graded} / ${rows.length} graded`;
}

/* ── HTML escape helper ───────────────────────────────────────────── */
function h(s) {
    return String(s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<script src="script.js"></script>
</body>
</html>
