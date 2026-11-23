<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Erro - TerraSara</title>
    <link rel="stylesheet" href="view/style.css">
</head>
<body>

<header class="top-header">
    <div class="logo">
        <img src="view/logo.png" alt="TerraSara Orgânicos & Naturais">
        <span>TerraSara Orgânicos &amp; Naturais</span>
    </div>
</header>

<section class="form-container">
    <div class="alerta-erro"><?= htmlspecialchars($mensagem ?? 'Erro desconhecido.') ?></div>
    <a href="index.php?action=listar" class="btn-success">Voltar</a>
</section>

</body>
</html>