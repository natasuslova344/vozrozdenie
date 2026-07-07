<?php
// Панель для просмотра заявок. Смените логин/пароль ниже перед публикацией на сервере.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

$ADMIN_USER = 'admin';
$ADMIN_PASS = 'secret123';

if (!isset($_SERVER['PHP_AUTH_USER'])
    || $_SERVER['PHP_AUTH_USER'] !== $ADMIN_USER
    || $_SERVER['PHP_AUTH_PW']   !== $ADMIN_PASS
) {
    header('WWW-Authenticate: Basic realm="Заявки — ООО Возрождение"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Доступ запрещён';
    exit;
}

try {
    $db    = new Database();
    $leads = $db->getAllLeads();
} catch (Exception $e) {
    die('<p style="color:red">Ошибка подключения к базе данных: ' . htmlspecialchars($e->getMessage()) . '</p>');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Заявки — ООО «Возрождение»</title>
<style>
  body { font-family: Arial, sans-serif; padding: 20px; background: #f4f6f8; }
  h1 { color: #2C3E4E; }
  .count { color: #888; font-size: 14px; margin-bottom: 16px; }
  table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
  th { background: #2C3E4E; color: #fff; padding: 12px 10px; text-align: left; font-size: 13px; }
  td { padding: 10px; border-bottom: 1px solid #eee; font-size: 13px; vertical-align: top; }
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: #f9fafb; }
  .id { color: #aaa; }
  a { color: #D94A1F; }
</style>
</head>
<body>
<h1>📋 Заявки с сайта</h1>
<p class="count">Всего заявок: <strong><?= count($leads) ?></strong></p>

<?php if (empty($leads)): ?>
    <p>Заявок пока нет.</p>
<?php else: ?>
<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Дата</th>
      <th>Имя / Компания</th>
      <th>Телефон</th>
      <th>Email</th>
      <th>Сообщение</th>
      <th>Источник</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($leads as $lead): ?>
    <tr>
      <td class="id"><?= $lead['id'] ?></td>
      <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($lead['created_at']))) ?></td>
      <td><?= htmlspecialchars($lead['name']) ?></td>
      <td><a href="tel:<?= htmlspecialchars($lead['phone']) ?>"><?= htmlspecialchars($lead['phone']) ?></a></td>
      <td><?= $lead['email'] ? htmlspecialchars($lead['email']) : '—' ?></td>
      <td><?= nl2br(htmlspecialchars($lead['message'])) ?></td>
      <td><?= htmlspecialchars($lead['source'] ?? '—') ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</body>
</html>
