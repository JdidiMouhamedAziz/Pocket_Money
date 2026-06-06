<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

function formatCurrency($amount, int $decimals = 2) {
    return '$' . number_format((float) $amount, $decimals, '.', ',');
}

function initials($name, $lastName = '') {
    $first = trim($name);
    $last = trim($lastName);
    if ($first === '' && $last === '') {
        return 'NA';
    }
    $firstInitial = $first !== '' ? mb_substr($first, 0, 1) : '';
    $lastInitial = $last !== '' ? mb_substr($last, 0, 1) : '';
    return strtoupper($firstInitial . $lastInitial ?: $firstInitial ?: $lastInitial);
}

function statusBadge($status) {
    $status = strtolower((string) $status);
    return match ($status) {
        'active' => ['class' => 'sb-active', 'label' => 'Active'],
        'pending' => ['class' => 'sb-pending', 'label' => 'Pending'],
        'blocked' => ['class' => 'sb-blocked', 'label' => 'Blocked'],
        'deleted' => ['class' => 'sb-blocked', 'label' => 'Deleted'],
        default => ['class' => 'sb-pending', 'label' => ucfirst($status ?: 'Unknown')],
    };
}

function roleBadgeClass($role) {
    return strtolower($role) === 'admin' ? 'rb-admin' : 'rb-user';
}

function categoryStyle($index) {
    $colors = ['#4f46e5', '#3b82f6', '#f59e0b', '#10b981', '#8b5cf6', '#f97316'];
    return $colors[$index % count($colors)];
}

function formatDate($dateTime) {
    if (!$dateTime) {
        return 'Unknown';
    }
    return date('M d, Y', strtotime($dateTime));
}

function progressClass($pct) {
    if ($pct >= 90) {
        return 'pb-red';
    }
    if ($pct >= 70) {
        return 'pb-orange';
    }
    return 'pb-green';
}

function relativeTime($dateTime) {
    $timestamp = strtotime($dateTime);
    if (!$timestamp) {
        return 'Unknown';
    }
    $diff = time() - $timestamp;
    if ($diff < 60) {
        return 'Just now';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . ' mins ago';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    }
    return floor($diff / 86400) . ' days ago';
}

function setAdminFlash(string $message, bool $success = true) {
    $_SESSION['admin_flash'] = ['success' => $success, 'message' => $message];
}

function getAdminFlash(): ?array {
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return $flash;
}
