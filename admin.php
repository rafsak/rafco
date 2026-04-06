<?php
/**
 * ATFP Chatbot — Admin Panel
 * Gestion des intents, centres, spécialités, conversations
 */

session_start();
require_once __DIR__ . '/config.php';

// ============================================
// AUTH
// ============================================
$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, password_hash FROM admins WHERE username = :u');
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $username;
            header('Location: admin.php');
            exit;
        }
    } catch (Exception $ex) {
        // DB error — show login with error
    }

    $loginError = true;
}

$isLoggedIn = !empty($_SESSION['admin_id']);

// ============================================
// API actions (AJAX)
// ============================================
if ($isLoggedIn && $action === 'api') {
    header('Content-Type: application/json; charset=utf-8');
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    $entity = $_GET['entity'] ?? '';

    try {
        switch ($entity) {
            case 'stats':
                $convos = $db->query('SELECT COUNT(*) AS c FROM conversations')->fetch()['c'];
                $msgs   = $db->query('SELECT COUNT(*) AS c FROM messages')->fetch()['c'];
                $today  = $db->query("SELECT COUNT(*) AS c FROM conversations WHERE DATE(created_at) = CURDATE()")->fetch()['c'];

                // Top intents
                $topIntents = $db->query("
                    SELECT intent, COUNT(*) AS cnt
                    FROM messages
                    WHERE role = 'bot' AND intent IS NOT NULL AND intent != 'default'
                    GROUP BY intent ORDER BY cnt DESC LIMIT 5
                ")->fetchAll();

                jsonResponse([
                    'conversations' => (int)$convos,
                    'messages'      => (int)$msgs,
                    'today'         => (int)$today,
                    'top_intents'   => $topIntents,
                ]);
                break;

            case 'conversations':
                $stmt = $db->query("
                    SELECT c.id, c.session_id, c.lang, c.created_at,
                           COUNT(m.id) AS msg_count
                    FROM conversations c
                    LEFT JOIN messages m ON m.conversation_id = c.id
                    GROUP BY c.id
                    ORDER BY c.created_at DESC
                    LIMIT 50
                ");
                jsonResponse(['items' => $stmt->fetchAll()]);
                break;

            case 'messages':
                $cid = (int)($_GET['conversation_id'] ?? 0);
                $stmt = $db->prepare("
                    SELECT role, content, intent, created_at
                    FROM messages
                    WHERE conversation_id = :cid
                    ORDER BY id ASC
                ");
                $stmt->execute([':cid' => $cid]);
                jsonResponse(['items' => $stmt->fetchAll()]);
                break;

            case 'intents':
                if ($method === 'GET') {
                    $intents = loadIntents();
                    jsonResponse($intents);
                } elseif ($method === 'POST') {
                    $body = json_decode(file_get_contents('php://input'), true);
                    if ($body) {
                        file_put_contents(__DIR__ . '/intents.json', json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        jsonResponse(['success' => true]);
                    } else {
                        jsonResponse(['error' => 'Invalid JSON'], 400);
                    }
                }
                break;

            case 'centres':
                $stmt = $db->query("
                    SELECT c.id, c.nom_fr, c.nom_ar, c.telephone,
                           g.nom_fr AS gouvernorat
                    FROM centres c
                    JOIN gouvernorats g ON c.gouvernorat_id = g.id
                    ORDER BY g.nom_fr, c.nom_fr
                ");
                jsonResponse(['items' => $stmt->fetchAll()]);
                break;

            case 'specialites':
                $stmt = $db->query("
                    SELECT s.id, s.nom_fr, s.nom_ar, s.niveau, s.duree_mois,
                           sec.nom_fr AS secteur
                    FROM specialites s
                    JOIN secteurs sec ON s.secteur_id = sec.id
                    ORDER BY sec.nom_fr, s.niveau, s.nom_fr
                ");
                jsonResponse(['items' => $stmt->fetchAll()]);
                break;

            default:
                jsonResponse(['error' => 'Unknown entity'], 404);
        }
    } catch (Exception $ex) {
        jsonResponse(['error' => $ex->getMessage()], 500);
    }
    exit;
}

// ============================================
// HTML
// ============================================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — ATFP Chatbot</title>
    <style>
        :root {
            --primary: #0084ff;
            --primary-dark: #0066cc;
            --bg: #f0f2f5;
            --card-bg: #fff;
            --text: #1c1e21;
            --text-light: #65676b;
            --border: #dddfe2;
            --success: #31a24c;
            --danger: #e4405f;
            --font: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font); background: var(--bg); color: var(--text); }

        /* Login */
        .login-page {
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 20px;
        }
        .login-box {
            background: var(--card-bg); border-radius: 12px; padding: 40px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1); max-width: 400px; width: 100%;
            text-align: center;
        }
        .login-box h1 { font-size: 22px; margin-bottom: 8px; }
        .login-box p { color: var(--text-light); font-size: 14px; margin-bottom: 24px; }
        .login-box input {
            width: 100%; padding: 12px 16px; border: 1.5px solid var(--border);
            border-radius: 8px; font-size: 14px; margin-bottom: 12px;
            outline: none; font-family: var(--font);
        }
        .login-box input:focus { border-color: var(--primary); }
        .login-box button {
            width: 100%; padding: 12px; background: var(--primary); color: #fff;
            border: none; border-radius: 8px; font-size: 15px; font-weight: 600;
            cursor: pointer; font-family: var(--font);
        }
        .login-box button:hover { background: var(--primary-dark); }
        .login-error { color: var(--danger); font-size: 13px; margin-bottom: 12px; }

        /* Dashboard */
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 240px; background: #1c1e21; color: #fff; padding: 20px 0;
            position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto;
        }
        .sidebar h2 { padding: 0 20px; font-size: 18px; margin-bottom: 24px; }
        .sidebar a {
            display: block; padding: 10px 20px; color: rgba(255,255,255,0.7);
            text-decoration: none; font-size: 14px; transition: all 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.1); color: #fff;
        }
        .sidebar .logout { margin-top: 24px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 12px; }
        .sidebar .logout a { color: var(--danger); }

        .main { margin-left: 240px; padding: 24px; flex: 1; }
        .main h1 { font-size: 22px; margin-bottom: 20px; }

        /* Stats cards */
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px; margin-bottom: 24px;
        }
        .stat-card {
            background: var(--card-bg); border-radius: 12px; padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .stat-card .stat-value { font-size: 32px; font-weight: 700; color: var(--primary); }
        .stat-card .stat-label { font-size: 13px; color: var(--text-light); margin-top: 4px; }

        /* Tables */
        .card {
            background: var(--card-bg); border-radius: 12px; padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06); margin-bottom: 20px;
        }
        .card h3 { font-size: 16px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border); }
        th { font-weight: 600; color: var(--text-light); font-size: 12px; text-transform: uppercase; }
        tr:hover { background: #f8f9fa; }
        .badge {
            display: inline-block; padding: 3px 10px; border-radius: 12px;
            font-size: 11px; font-weight: 600;
        }
        .badge-blue { background: #e3f2fd; color: #1565c0; }
        .badge-green { background: #e8f5e9; color: #2e7d32; }
        .badge-orange { background: #fff3e0; color: #e65100; }

        /* Intents editor */
        .intents-editor textarea {
            width: 100%; height: 500px; font-family: 'Fira Code', monospace;
            font-size: 13px; padding: 16px; border: 1.5px solid var(--border);
            border-radius: 8px; resize: vertical; outline: none;
        }
        .intents-editor textarea:focus { border-color: var(--primary); }
        .intents-editor .save-btn {
            margin-top: 12px; padding: 10px 24px; background: var(--success); color: #fff;
            border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: pointer; font-family: var(--font);
        }
        .intents-editor .save-btn:hover { opacity: 0.9; }
        .save-msg { margin-top: 8px; font-size: 13px; color: var(--success); }

        /* Conversation detail */
        .conv-detail { max-height: 400px; overflow-y: auto; padding: 12px; }
        .conv-msg { margin-bottom: 8px; padding: 8px 12px; border-radius: 12px; max-width: 80%; }
        .conv-msg.user { background: #e3f2fd; margin-left: auto; }
        .conv-msg.bot { background: #f0f0f0; }
        .conv-msg .role { font-size: 11px; font-weight: 600; color: var(--text-light); }
        .conv-msg .text { font-size: 13px; white-space: pre-wrap; margin-top: 4px; }

        /* Tabs */
        .tab-links { display: flex; gap: 0; margin-bottom: 20px; border-bottom: 2px solid var(--border); }
        .tab-links a {
            padding: 10px 20px; text-decoration: none; color: var(--text-light);
            font-size: 14px; font-weight: 500; border-bottom: 2px solid transparent;
            margin-bottom: -2px; cursor: pointer;
        }
        .tab-links a.active { color: var(--primary); border-bottom-color: var(--primary); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .clickable { cursor: pointer; }
        .clickable:hover { background: #eef5ff; }

        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; }
            .main { margin-left: 0; }
            .admin-wrapper { flex-direction: column; }
        }
    </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
<!-- ============ LOGIN ============ -->
<div class="login-page">
    <div class="login-box">
        <h1>🤖 Admin Panel</h1>
        <p>ATFP Chatbot — Administration</p>
        <?php if (!empty($loginError)): ?>
            <div class="login-error">Identifiants incorrects</div>
        <?php endif; ?>
        <form method="POST" action="admin.php?action=login">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ============ DASHBOARD ============ -->
<div class="admin-wrapper">
    <div class="sidebar">
        <h2>🤖 ATFP Admin</h2>
        <a href="#" class="active" data-tab="dashboard">📊 Tableau de bord</a>
        <a href="#" data-tab="conversations">💬 Conversations</a>
        <a href="#" data-tab="centres">🏢 Centres</a>
        <a href="#" data-tab="specialites">🎓 Spécialités</a>
        <a href="#" data-tab="intents">🧠 Intents</a>
        <div class="logout">
            <a href="admin.php?action=logout">🚪 Déconnexion</a>
        </div>
    </div>

    <div class="main">
        <!-- DASHBOARD TAB -->
        <div class="tab-content active" id="tab-dashboard">
            <h1>Tableau de bord</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" id="statConv">-</div>
                    <div class="stat-label">Conversations totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="statMsgs">-</div>
                    <div class="stat-label">Messages totaux</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="statToday">-</div>
                    <div class="stat-label">Conversations aujourd'hui</div>
                </div>
            </div>
            <div class="card">
                <h3>Top Intents</h3>
                <table>
                    <thead><tr><th>Intent</th><th>Nombre</th></tr></thead>
                    <tbody id="topIntentsBody"></tbody>
                </table>
            </div>
        </div>

        <!-- CONVERSATIONS TAB -->
        <div class="tab-content" id="tab-conversations">
            <h1>Conversations</h1>
            <div class="card">
                <table>
                    <thead><tr><th>#</th><th>Session</th><th>Langue</th><th>Messages</th><th>Date</th></tr></thead>
                    <tbody id="convBody"></tbody>
                </table>
            </div>
            <div class="card" id="convDetailCard" style="display:none;">
                <h3>Détail de la conversation</h3>
                <div class="conv-detail" id="convDetail"></div>
            </div>
        </div>

        <!-- CENTRES TAB -->
        <div class="tab-content" id="tab-centres">
            <h1>Centres ATFP</h1>
            <div class="card">
                <table>
                    <thead><tr><th>#</th><th>Centre</th><th>Arabe</th><th>Gouvernorat</th><th>Téléphone</th></tr></thead>
                    <tbody id="centresBody"></tbody>
                </table>
            </div>
        </div>

        <!-- SPECIALITES TAB -->
        <div class="tab-content" id="tab-specialites">
            <h1>Spécialités</h1>
            <div class="card">
                <table>
                    <thead><tr><th>#</th><th>Spécialité</th><th>Secteur</th><th>Niveau</th><th>Durée</th></tr></thead>
                    <tbody id="specsBody"></tbody>
                </table>
            </div>
        </div>

        <!-- INTENTS TAB -->
        <div class="tab-content" id="tab-intents">
            <h1>Éditeur d'Intents</h1>
            <div class="card intents-editor">
                <textarea id="intentsEditor"></textarea>
                <button class="save-btn" id="saveIntentsBtn">💾 Sauvegarder</button>
                <div class="save-msg" id="saveMsg"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    const API = 'admin.php?action=api&entity=';

    // -- Tab navigation --
    document.querySelectorAll('.sidebar a[data-tab]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var tab = this.getAttribute('data-tab');
            document.querySelectorAll('.sidebar a').forEach(function(a) { a.classList.remove('active'); });
            this.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(function(t) { t.classList.remove('active'); });
            document.getElementById('tab-' + tab).classList.add('active');
            loadTabData(tab);
        });
    });

    function loadTabData(tab) {
        switch(tab) {
            case 'dashboard': loadStats(); break;
            case 'conversations': loadConversations(); break;
            case 'centres': loadCentres(); break;
            case 'specialites': loadSpecialites(); break;
            case 'intents': loadIntents(); break;
        }
    }

    // -- Stats --
    function loadStats() {
        fetch(API + 'stats').then(function(r) { return r.json(); }).then(function(d) {
            document.getElementById('statConv').textContent = d.conversations;
            document.getElementById('statMsgs').textContent = d.messages;
            document.getElementById('statToday').textContent = d.today;

            var html = '';
            (d.top_intents || []).forEach(function(i) {
                html += '<tr><td>' + esc(i.intent) + '</td><td>' + i.cnt + '</td></tr>';
            });
            document.getElementById('topIntentsBody').innerHTML = html || '<tr><td colspan="2">Aucune donnée</td></tr>';
        });
    }

    // -- Conversations --
    function loadConversations() {
        fetch(API + 'conversations').then(function(r) { return r.json(); }).then(function(d) {
            var html = '';
            (d.items || []).forEach(function(c) {
                var langBadge = c.lang === 'ar'
                    ? '<span class="badge badge-orange">AR</span>'
                    : '<span class="badge badge-blue">FR</span>';
                html += '<tr class="clickable" data-cid="' + c.id + '">';
                html += '<td>' + c.id + '</td>';
                html += '<td>' + esc(c.session_id.substr(0,16)) + '…</td>';
                html += '<td>' + langBadge + '</td>';
                html += '<td>' + c.msg_count + '</td>';
                html += '<td>' + c.created_at + '</td>';
                html += '</tr>';
            });
            document.getElementById('convBody').innerHTML = html || '<tr><td colspan="5">Aucune conversation</td></tr>';

            // Click to show detail
            document.querySelectorAll('#convBody tr.clickable').forEach(function(tr) {
                tr.addEventListener('click', function() {
                    loadConvDetail(this.getAttribute('data-cid'));
                });
            });
        });
    }

    function loadConvDetail(cid) {
        fetch(API + 'messages&conversation_id=' + cid).then(function(r) { return r.json(); }).then(function(d) {
            var html = '';
            (d.items || []).forEach(function(m) {
                html += '<div class="conv-msg ' + m.role + '">';
                html += '<div class="role">' + (m.role === 'user' ? '👤 Utilisateur' : '🤖 Bot') + ' — ' + m.created_at + '</div>';
                html += '<div class="text">' + esc(m.content) + '</div>';
                html += '</div>';
            });
            document.getElementById('convDetail').innerHTML = html || 'Aucun message';
            document.getElementById('convDetailCard').style.display = 'block';
        });
    }

    // -- Centres --
    function loadCentres() {
        fetch(API + 'centres').then(function(r) { return r.json(); }).then(function(d) {
            var html = '';
            (d.items || []).forEach(function(c) {
                html += '<tr>';
                html += '<td>' + c.id + '</td>';
                html += '<td>' + esc(c.nom_fr) + '</td>';
                html += '<td>' + esc(c.nom_ar) + '</td>';
                html += '<td>' + esc(c.gouvernorat) + '</td>';
                html += '<td>' + esc(c.telephone || '-') + '</td>';
                html += '</tr>';
            });
            document.getElementById('centresBody').innerHTML = html;
        });
    }

    // -- Specialites --
    function loadSpecialites() {
        fetch(API + 'specialites').then(function(r) { return r.json(); }).then(function(d) {
            var html = '';
            (d.items || []).forEach(function(s) {
                var lvl = s.niveau;
                var cls = lvl === 'BTS' ? 'badge-green' : (lvl === 'BTP' ? 'badge-blue' : 'badge-orange');
                html += '<tr>';
                html += '<td>' + s.id + '</td>';
                html += '<td>' + esc(s.nom_fr) + '</td>';
                html += '<td>' + esc(s.secteur) + '</td>';
                html += '<td><span class="badge ' + cls + '">' + lvl + '</span></td>';
                html += '<td>' + s.duree_mois + ' mois</td>';
                html += '</tr>';
            });
            document.getElementById('specsBody').innerHTML = html;
        });
    }

    // -- Intents --
    function loadIntents() {
        fetch(API + 'intents').then(function(r) { return r.json(); }).then(function(d) {
            document.getElementById('intentsEditor').value = JSON.stringify(d, null, 2);
        });
    }

    document.getElementById('saveIntentsBtn').addEventListener('click', function() {
        var raw = document.getElementById('intentsEditor').value;
        try {
            var parsed = JSON.parse(raw);
        } catch(e) {
            document.getElementById('saveMsg').textContent = '❌ JSON invalide : ' + e.message;
            document.getElementById('saveMsg').style.color = '#e4405f';
            return;
        }

        fetch(API + 'intents', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(parsed),
        }).then(function(r) { return r.json(); }).then(function(d) {
            if (d.success) {
                document.getElementById('saveMsg').textContent = '✅ Intents sauvegardés !';
                document.getElementById('saveMsg').style.color = '#31a24c';
            } else {
                document.getElementById('saveMsg').textContent = '❌ Erreur : ' + (d.error || 'inconnue');
                document.getElementById('saveMsg').style.color = '#e4405f';
            }
        });
    });

    // -- Helpers --
    function esc(s) {
        if (!s) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(s));
        return div.innerHTML;
    }

    // -- Init --
    loadStats();
})();
</script>

<?php endif; ?>
</body>
</html>
