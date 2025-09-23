<?php
// /faq.php (VERSÃO FINAL COM TEXTO DE SUPORTE)

session_start();
include 'templates/header.php';
?>

<title>Central de Ajuda & FAQ</title>

<style>
    /* Estilos para a página de FAQ (inalterados) */
    .faq-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background-color: var(--cor-cinza-escuro);
        border-radius: 12px;
        color: var(--cor-branco);
    }
    .faq-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }
    .faq-header h1 {
        color: var(--cor-dourado);
        margin-bottom: 0.5rem;
    }
    .faq-header p {
        opacity: 0.8;
    }
    .faq-section {
        margin-bottom: 2rem;
    }
    .faq-section h2 {
        color: var(--cor-dourado);
        opacity: 0.9;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    .faq-item {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .faq-question {
        width: 100%;
        background: none;
        border: none;
        text-align: left;
        padding: 1rem 0;
        font-size: 1.1rem;
        font-weight: 500;
        color: var(--cor-branco);
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .faq-question::after {
        content: '+';
        font-size: 1.5rem;
        transition: transform 0.3s ease;
    }
    .faq-item.active .faq-question::after {
        transform: rotate(45deg);
    }
    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-out, padding 0.4s ease-out;
        padding: 0 1rem;
    }
    .faq-answer p, .faq-answer video {
        padding-bottom: 1rem;
        opacity: 0.85;
    }
    
    .support-button-container {
        text-align: center;
        margin-top: 2.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* =================== NOVO ESTILO PARA O TEXTO DE SUPORTE =================== */
    .support-button-container h3 {
        color: var(--cor-branco);
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .support-button-container p {
        opacity: 0.7;
        margin-bottom: 1.5rem; /* Espaço entre o texto e o botão */
        font-size: 0.95rem;
        max-width: 400px; /* Limita a largura do texto para melhor leitura */
        margin-left: auto;
        margin-right: auto;
    }
    /* ========================================================================= */

    .btn-whatsapp {
        background-color: #25D366;
        color: white;
        padding: 0.8rem 1.5rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.7rem;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }
    .btn-whatsapp:hover {
        background-color: #1DAE56;
        transform: scale(1.05);
    }
    .btn-whatsapp svg {
        width: 24px;
        height: 24px;
        fill: currentColor;
    }
</style>

<div class="faq-container">
    <header class="faq-header">
        <h1>Central de Ajuda & FAQ</h1>
        <p>Encontre aqui tutoriais e respostas para as dúvidas mais comuns.</p>
    </header>

    <section class="faq-section">
        <h2>Fluxo de Registro de Compra</h2>
        <div class="faq-item">
            <button class="faq-question">Como registrar uma compra para um cliente?</button>
            <div class="faq-answer">
                <p>Aqui você vai colocar seu vídeo ou tutorial em texto...</p>
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">O que fazer se um cliente não tiver cadastro?</button>
            <div class="faq-answer">
                <p>Aqui você vai colocar seu vídeo ou tutorial em texto...</p>
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">Como os números da sorte são calculados?</button>
            <div class="faq-answer">
                <p>Aqui você vai colocar seu vídeo ou tutorial em texto...</p>
            </div>
        </div>
    </section>

    <section class="faq-section">
        <h2>Para Vendedoras</h2>
        <div class="faq-item">
            <button class="faq-question">Como acesso meu painel?</button>
            <div class="faq-answer">
                <p>Aqui você vai colocar seu vídeo ou tutorial em texto...</p>
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">O que posso ver no meu painel?</button>
            <div class="faq-answer">
                <p>Aqui você vai colocar seu vídeo ou tutorial em texto...</p>
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">Esqueci minha senha. O que eu faço?</button>
            <div class="faq-answer">
                <p>Aqui você vai colocar seu vídeo ou tutorial em texto...</p>
            </div>
        </div>
    </section>

    <?php if (isset($_SESSION['cargo']) && $_SESSION['cargo'] == 1): ?>
    <section class="faq-section">
        <h2>Para o Administrador</h2>
        <div class="faq-item">
            <button class="faq-question">Como funciona o sorteio?</button>
            <div class="faq-answer">
                <p>Aqui você vai colocar seu vídeo ou tutorial em texto...</p>
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">Como eu gerencio minhas vendedoras?</button>
            <div class="faq-answer">
                <p>Aqui você vai colocar seu vídeo ou tutorial em texto...</p>
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">Posso alterar o valor base para ganhar números da sorte extras?</button>
            <div class="faq-answer">
                <p>Aqui você vai colocar seu vídeo ou tutorial em texto...</p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <div class="support-button-container">
        <h3>Ainda com dúvidas ou notou um erro?</h3>
        <p>Se sua pergunta não foi respondida acima, fale diretamente com o suporte.</p>
        <a href="https://wa.me/5516997452876" target="_blank" rel="noopener noreferrer" class="btn-whatsapp">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.8 0-67.6-9.5-97.2-27.2l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
            <span>Fale com o suporte</span>
        </a>
    </div>
    </div>

<script>
    // Script do acordeão (inalterado)
    document.querySelectorAll('.faq-question').forEach(button => {
        button.addEventListener('click', () => {
            const faqItem = button.parentElement;
            const answer = button.nextElementSibling;

            faqItem.classList.toggle('active');

            if (faqItem.classList.contains('active')) {
                answer.style.maxHeight = answer.scrollHeight + 'px';
                answer.style.padding = '1rem';
            } else {
                answer.style.maxHeight = 0;
                answer.style.padding = '0 1rem';
            }
        });
    });
</script>

<?php
include 'templates/footer.php';
?>