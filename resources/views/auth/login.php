<?php $title = 'Iniciar sesión'; $errors = $errors ?? []; ob_start(); ?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <h1 class="h3 mb-4 text-center">Iniciar sesión</h1>
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?= implode(', ', $errors['general']) ?></div>
        <?php endif; ?>
        <form method="post" action="/bela/public/login">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <?php if (!empty($errors['email'])): ?><div class="text-danger small"><?= implode(', ', $errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control">
                <?php if (!empty($errors['password'])): ?><div class="text-danger small"><?= implode(', ', $errors['password']) ?></div><?php endif; ?>
            </div>
            <button class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>