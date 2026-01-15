    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p class="text-sm">
                        &copy; <?php echo date('Y'); ?> Sistema de Lista de Espera - <a href="https://aicode.dev.br" target="_blank" class="hover:text-blue-400 transition font-semibold">AiCode</a>
                    </p>
                </div>
                <div class="flex space-x-6 text-sm">
                    <a href="#" class="hover:text-blue-400 transition">Ajuda</a>
                    <a href="#" class="hover:text-blue-400 transition">Documentação</a>
                    <a href="#" class="hover:text-blue-400 transition">Suporte</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts JS -->
    <script>
        // Função para fechar alertas
        function fecharAlerta(elemento) {
            elemento.parentElement.style.display = 'none';
        }

        // Auto-fechar alertas após 5 segundos
        setTimeout(function() {
            const alertas = document.querySelectorAll('[role="alert"]');
            alertas.forEach(function(alerta) {
                alerta.style.transition = 'opacity 0.5s';
                alerta.style.opacity = '0';
                setTimeout(function() {
                    alerta.style.display = 'none';
                }, 500);
            });
        }, 5000);

        // Função para abrir modal
        function abrirModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Toggle Menu de Perfil
        function toggleMenuPerfil() {
            const dropdown = document.getElementById('menuPerfilDropdown');
            const icon = document.getElementById('menuPerfilIcon');
            
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                dropdown.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Fechar menu ao clicar fora
        document.addEventListener('click', function(event) {
            const menuContainer = document.getElementById('menuPerfilContainer');
            const dropdown = document.getElementById('menuPerfilDropdown');
            
            if (menuContainer && dropdown && !menuContainer.contains(event.target)) {
                dropdown.classList.add('hidden');
                document.getElementById('menuPerfilIcon').style.transform = 'rotate(0deg)';
            }
        });

        // Função para fechar modal
        function fecharModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-backdrop')) {
                const modals = document.querySelectorAll('.modal-backdrop');
                modals.forEach(function(modal) {
                    fecharModal(modal.id);
                });
            }
        }

        // Máscara de CPF
        function mascaraCPF(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length <= 11) {
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            input.value = valor;
        }

        // Máscara de Telefone
        function mascaraTelefone(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length <= 11) {
                if (valor.length <= 10) {
                    valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
                    valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
                    valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
                }
            }
            input.value = valor;
        }

        // Máscara de Data
        function mascaraData(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length <= 8) {
                valor = valor.replace(/(\d{2})(\d)/, '$1/$2');
                valor = valor.replace(/(\d{2})(\d)/, '$1/$2');
            }
            input.value = valor;
        }

        // Confirmação de exclusão
        function confirmarExclusao(mensagem) {
            return confirm(mensagem || 'Deseja realmente excluir este registro?');
        }
    </script>
</body>
</html>
