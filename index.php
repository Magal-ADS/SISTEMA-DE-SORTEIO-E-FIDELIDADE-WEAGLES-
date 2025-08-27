<?php
// /index.php (Versão Dinâmica)

// 1. Incluímos a configuração do banco para buscar o texto
require_once 'php/db_config.php';

// 2. Busca o texto do card de informação no banco
$result_config = $link->query("SELECT valor FROM configuracoes WHERE chave = 'tela_inicial_info_card_texto'");
$info_card_text = "Participe e concorra a prêmios incríveis!"; // Texto padrão
if ($result_config) {
    $config_data = $result_config->fetch_assoc();
    if ($config_data) {
        $info_card_text = $config_data['valor'];
    }
}
$link->close();

include 'templates/header.php'; 
?>

<title>Magal Store - Participe</title>

<div class="card-container">
    <img src="images/sacola.avif" alt="Sacola de Compras" class="logo-image">
    
    <h1>Participe e Concorra!</h1>
    
    <p class="subtitle">Faça seu cadastro para concorrer a prêmios incríveis e ter acesso a ofertas exclusivas.</p>
    
    <div class="info-card">
        <span class="info-card-icon">&#127915;</span>
        <p class="info-card-text"><?php echo htmlspecialchars($info_card_text); ?></p>
    </div>
    
    <a href="cpf.php" class="btn btn-laranja">QUERO PARTICIPAR</a>
    
    <div class="security-info">
        <span>&#128274;</span>
        <span>Seus dados estão protegidos.</span>
    </div>
</div>

<?php include 'templates/footer.php'; ?>