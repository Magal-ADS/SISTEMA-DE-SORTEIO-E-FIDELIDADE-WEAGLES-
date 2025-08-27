document.addEventListener("DOMContentLoaded", () => {
    
    // --- Lógica da Página de CPF (cpf.php) ---
    const cpfForm = document.getElementById("cpf-form");
    if (cpfForm) {
        cpfForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const cpf = document.getElementById("cpf").value;

            if (cpf.length < 11) {
                alert("Por favor, digite um CPF válido.");
                return;
            }

            localStorage.setItem("userCPF", cpf);
            const formData = new FormData();
            formData.append("cpf", cpf);

            fetch("php/verificar_cpf.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'exists') {
                    localStorage.setItem("userName", data.cliente.nome_completo);
                    window.location.href = "dados_compra.php";
                } else if (data.status === 'not_exists') {
                    window.location.href = "cadastro.php";
                } else {
                    console.error("Erro retornado pelo servidor:", data.message);
                }
            })
            .catch(error => {
                console.error("Erro na requisição Fetch:", error);
            });
        });
    }

    // --- Lógica da Página de Cadastro (cadastro.php) ---
    const cadastroForm = document.getElementById("cadastro-form");
    if (cadastroForm) {
        cadastroForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const formData = new FormData(cadastroForm);
            const cpf = localStorage.getItem("userCPF");
            
            const nomeCompleto = formData.get('nome');
            localStorage.setItem("userName", nomeCompleto);
            
            formData.append("cpf", cpf);

            fetch("php/cadastrar_cliente.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success'){
                    window.location.href = "dados_compra.php";
                } else {
                    console.error("Erro no cadastro:", data.message);
                }
            });
        });
    }

    // --- Lógica da Página de Dados da Compra (dados_compra.php) ---
    const dadosCompraPage = document.getElementById("dados-compra-form");
    if (dadosCompraPage) {
        const cpf = localStorage.getItem("userCPF");
        if (cpf) {
             fetch(`php/get_dados_cliente.php?cpf=${cpf}`)
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success'){
                    const nomeCliente = data.cliente.nome_completo.split(' ')[0];
                    document.getElementById("welcome-message").innerText = `Olá, ${nomeCliente}!`;
                    
                    const vendedorSelect = document.getElementById("vendedor");
                    data.vendedores.forEach(vendedor => {
                        const option = new Option(vendedor.nome, vendedor.nome);
                        vendedorSelect.add(option);
                    });
                }
            });
        }
        
        dadosCompraPage.addEventListener("submit", e => {
            e.preventDefault();
            const valorInput = document.getElementById("valor");
            let valorNumerico = valorInput.value.replace(",", ".");
            
            const formData = new FormData(dadosCompraPage);
            formData.set("valor", valorNumerico);
            formData.append("cpf", cpf);
            
            fetch("php/salvar_compra.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success'){
                    const nome = localStorage.getItem("userName");
                    window.location.href = `sucesso.php?nome=${encodeURIComponent(nome)}`;
                } else {
                    console.error("Erro ao salvar compra:", data.message);
                }
            });
        });
    }

    // --- Lógica do Menu Hamburguer ---
    const hamburgerBtn = document.getElementById("hamburger-menu");
    const sideNav = document.getElementById("side-nav");

    if (hamburgerBtn && sideNav) {
        hamburgerBtn.addEventListener("click", () => {
            hamburgerBtn.classList.toggle("active");
            sideNav.classList.toggle("open");
        });
    }
    
    // --- Lógica da Página de Dashboard (dashboard.php) ---
    const dashboardContainer = document.querySelector(".dashboard-container");
    if (dashboardContainer) {
        fetch("php/get_dashboard_data.php")
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('novos-clientes-valor').innerText = data.total_clientes;
                
                const valorVendas = parseFloat(data.total_vendas);
                document.getElementById('vendas-valor').innerText = valorVendas.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            } else {
                console.error("Erro ao buscar dados da dashboard:", data.message);
            }
        })
        .catch(error => console.error("Erro na requisição da dashboard:", error));
    }
    
});