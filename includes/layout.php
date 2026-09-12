<?php
function page_head(string $title): void { ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?> · MindCare NG</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/mindcare.css" rel="stylesheet">
</head>
<body>
<div class="aurora"><span class="a1"></span><span class="a2"></span><span class="a3"></span></div>
<?php }
function page_header(array $u): void { ?>
<header class="d-flex align-items-center gap-2 py-3">
  <span class="orb breathe" style="width:36px;height:36px"></span>
  <span class="mc-serif fw-semibold fs-5">MindCare <span style="color:#8B5CF6">NG</span></span>
  <span class="ms-auto pill pill-<?= $u['role'] === 'student' ? 'violet' : ($u['role'] === 'counsellor' ? 'green' : 'amber') ?>"><?= htmlspecialchars($u['role']) ?></span>
  <a class="btn btn-sm glass px-3" style="border-radius:14px" href="api/logout.php">Sign out</a>
</header>
<?php } ?>
