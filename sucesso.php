<?php
// /sucesso.php

session_start();

$nomeCliente = $_SESSION['cliente_nome'] ?? 'Cliente';
$primeiroNome = explode(' ', $nomeCliente)[0];

unset($_SESSION['cliente_id']);
unset($_SESSION['cliente_nome']);
unset($_SESSION['cpf_cliente']);

include 'templates/header.php'; 
?>

<title>Sucesso!</title>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate3d(0, 40px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    .success-container {
        animation: fadeInUp 0.8s ease-out;
    }
</style>

<div class="success-container">
    <div class="success-icon">
        <span>&#10004;</span>
    </div>

    <h1>Parabéns, <?php echo htmlspecialchars($primeiroNome); ?>!</h1>

    <p>Sua participação foi registrada com sucesso. Você já está concorrendo!!! Fique de olho no sorteio e no seu Whatsapp. Boa sorte! 🍀</p>
    
    <a href="index.php" class="btn btn-verde" style="margin-top: 25px; text-decoration: none;">Voltar ao Início</a>

</div>

<?php 
include 'templates/footer.php'; 
?>


<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        function startConfetti() {
            const duration = 3 * 1000;
            const animationEnd = Date.now() + duration;
            const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }

            const interval = setInterval(function() {
                const timeLeft = animationEnd - Date.now();

                if (timeLeft <= 0) {
                    return clearInterval(interval);
                }

                const particleCount = 50 * (timeLeft / duration);
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
            }, 250);
        }

        startConfetti();
    });
</script>