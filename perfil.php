<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_login();

$pageTitle = is_admin() ? 'Perfil administrativo' : 'Meu perfil';
$userId = current_user_id();
$isClient = is_cliente();
$error = null;

$stmt = $pdo->prepare(
    "SELECT u.id, u.nome, u.email, u.tipo, u.avatar, c.telefone
     FROM usuarios u
     LEFT JOIN clientes c ON c.id = u.cliente_id
     WHERE u.id = ? LIMIT 1"
);
$stmt->execute([$userId]);
$perfil = $stmt->fetch();

if (!$perfil) {
    logout_user();
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'alterar_senha') {
    verify_csrf();
    $atual = (string)($_POST['senha_atual'] ?? '');
    $nova = (string)($_POST['nova_senha'] ?? '');
    $confirmar = (string)($_POST['confirmar_senha'] ?? '');
    $check = $pdo->prepare('SELECT senha FROM usuarios WHERE id=?');
    $check->execute([$userId]); $hashAtual = (string)$check->fetchColumn();
    if (!password_verify($atual, $hashAtual)) $error = 'A senha atual está incorreta.';
    elseif (strlen($nova) < 8) $error = 'A nova senha deve ter pelo menos 8 caracteres.';
    elseif ($nova !== $confirmar) $error = 'A confirmação da nova senha não confere.';
    else {
        $pdo->prepare('UPDATE usuarios SET senha=? WHERE id=?')->execute([password_hash($nova, PASSWORD_DEFAULT), $userId]);
        flash('success', 'Senha alterada com sucesso.');
        redirect('perfil.php');
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $removeAvatar = isset($_POST['remove_avatar']);

    if (mb_strlen($nome) < 3) {
        $error = 'Digite um nome válido.';
    } elseif ($isClient && $telefone !== '' && !preg_match('/^[0-9()\s+\-]{8,30}$/', $telefone)) {
        $error = 'Digite um telefone válido.';
    } else {
        $newAvatar = $perfil['avatar'];
        $uploadedPath = null;

        try {
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('Não foi possível enviar a foto.');
                }

                if ((int)$_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                    throw new RuntimeException('A foto deve ter no máximo 2 MB.');
                }

                $imageInfo = @getimagesize($_FILES['avatar']['tmp_name']);
                $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

                if (!$imageInfo || !isset($allowedMime[$imageInfo['mime']])) {
                    throw new RuntimeException('Use uma imagem JPG, PNG ou WEBP.');
                }

                $uploadDir = __DIR__ . '/assets/uploads/avatars';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    throw new RuntimeException('Não foi possível preparar a pasta de fotos.');
                }

                $filename = 'user_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $allowedMime[$imageInfo['mime']];
                $target = $uploadDir . '/' . $filename;

                if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                    throw new RuntimeException('Não foi possível salvar a foto.');
                }

                $newAvatar = 'assets/uploads/avatars/' . $filename;
                $uploadedPath = $target;
            }

            if ($removeAvatar && !$uploadedPath) {
                $newAvatar = null;
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare('UPDATE usuarios SET nome = ?, avatar = ? WHERE id = ?');
            $stmt->execute([$nome, $newAvatar, $userId]);

            if ($isClient && !empty($perfil['id'])) {
                $stmt = $pdo->prepare('UPDATE clientes SET nome = ?, telefone = ? WHERE id = (SELECT cliente_id FROM usuarios WHERE id = ?)');
                $stmt->execute([$nome, $telefone !== '' ? $telefone : null, $userId]);
            }

            $pdo->commit();

            if (($perfil['avatar'] ?? null) && (($removeAvatar && !$uploadedPath) || $uploadedPath)) {
                $oldFile = __DIR__ . '/' . ltrim((string)$perfil['avatar'], '/');
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }

            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_avatar'] = $newAvatar;
            flash('success', 'Seu perfil foi atualizado com sucesso.');
            redirect('perfil.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($uploadedPath && is_file($uploadedPath)) {
                @unlink($uploadedPath);
            }
            $error = $e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível atualizar seu perfil.';
        }
    }
}

require __DIR__ . ($isClient ? '/partials/client-header.php' : '/partials/header.php');
?>

<div class="page-heading">
    <div>
        <span class="section-kicker">MINHA CONTA</span>
        <h2><?= $isClient ? 'Meu perfil de cliente' : 'Perfil administrativo' ?></h2>
        <p>Gerencie seus dados de acesso e as informações da sua conta.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-error">
        <span class="material-symbols-outlined">error</span>
        <span><?= e($error) ?></span>
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
<?php endif; ?>

<div class="profile-layout">
<section class="card profile-card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="profile-editor">
                <div class="profile-photo-editor">
                    <?php if (!empty($perfil['avatar'])): ?>
                        <img src="<?= url($perfil['avatar']) ?>" alt="Foto de <?= e($perfil['nome']) ?>" class="profile-photo-preview">
                    <?php else: ?>
                        <div class="profile-photo-placeholder">
                            <?= e(mb_strtoupper(mb_substr($perfil['nome'], 0, 1))) ?>
                        </div>
                    <?php endif; ?>
                    <label class="profile-photo-button" for="avatar">
                        <span class="material-symbols-outlined">photo_camera</span>
                        Trocar foto
                    </label>
                    <input id="avatar" class="sr-only" type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
                    <?php if (!empty($perfil['avatar'])): ?>
                        <label class="profile-remove-photo">
                            <input type="checkbox" name="remove_avatar">
                            Remover foto atual
                        </label>
                    <?php endif; ?>
                    <small>JPG, PNG ou WEBP · até 2 MB</small>
                </div>

                <div class="profile-fields">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Nome completo *</label>
                            <input class="form-control" name="nome" value="<?= e($perfil['nome']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>E-mail</label>
                            <input class="form-control" type="email" value="<?= e($perfil['email']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Tipo de conta</label>
                            <input class="form-control" value="<?= $isClient ? 'Cliente' : 'Administrador do restaurante' ?>" readonly>
                        </div>
                        <?php if ($isClient): ?>
                            <div class="form-group full">
                                <label>Telefone</label>
                                <input class="form-control" name="telefone" type="tel" value="<?= e($perfil['telefone'] ?? '') ?>" placeholder="(12) 99999-9999" autocomplete="tel">
                                <small class="field-help">Seu telefone será usado para confirmar e organizar suas reservas.</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <a class="btn btn-light" href="<?= url($isClient ? 'cliente.php' : 'dashboard.php') ?>">Voltar</a>
                        <button class="btn btn-primary" type="submit">
                            <span class="material-symbols-outlined">save</span>
                            Salvar alterações
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="card profile-security-card">
    <div class="card-header"><div><h3>Segurança da conta</h3><span>Altere sua senha de acesso</span></div></div>
    <div class="card-body"><form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="acao" value="alterar_senha">
        <div class="form-group full"><label>Senha atual</label><input class="form-control" type="password" name="senha_atual" required></div>
        <div class="form-group"><label>Nova senha</label><input class="form-control" type="password" name="nova_senha" minlength="8" required></div>
        <div class="form-group"><label>Confirmar nova senha</label><input class="form-control" type="password" name="confirmar_senha" minlength="8" required></div>
        <div class="form-actions full"><button class="btn btn-primary" type="submit">Alterar senha</button></div>
    </form></div>
</section>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
