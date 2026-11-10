MESARESERVA — SISTEMA DO RESTAURANTE

REQUISITOS
- XAMPP com Apache + MySQL
- PHP 8.1+
- PDO MySQL

PRIMEIRA INSTALAÇÃO
1. Coloque a pasta MesaReserva em C:\xampp\htdocs\MesaReserva.
2. Ligue Apache e MySQL.
3. No phpMyAdmin, importe database/mesareserva.sql.
4. Acesse http://localhost/MesaReserva/

SE VOCÊ JÁ POSSUI O BANCO ANTIGO
- Faça backup.
- No phpMyAdmin, importe database/atualizar-banco.sql.
- A primeira conta antiga será mantida como administradora e marcada como verificada.

ACESSOS
- O endereço inicial abre o site institucional, não o login.
- Cliente: cria conta, confirma o e-mail e entra em cliente.php para reservar.
- Administrador: escolhe Administrador no login e, no cadastro, precisa da chave @MesaReserva_231009.
- Clientes não possuem dashboard administrativo.

E-MAIL
A verificação utiliza a função mail() do PHP. Em um XAMPP local, é necessário configurar o envio de e-mails no PHP/XAMPP para que as mensagens sejam entregues de verdade. O fluxo de token, expiração de 24 horas, confirmação e bloqueio do login até a verificação já está implementado.

RECURSOS
- Landing page institucional
- Login com escolha Cliente/Administrador
- Cadastro com chave administrativa
- Verificação de e-mail
- Reserva online para clientes
- Consulta de mesas disponíveis
- Minhas reservas
- Perguntas e atendimento
- Avaliações
- Dashboard e CRUDs para administração
- Política de privacidade detalhada
- Ícones do Instagram e WhatsApp no rodapé, com acesso direto pelos botões

SEGURANÇA
- PDO + prepared statements
- password_hash/password_verify
- CSRF
- Sessões regeneradas no login
- Tokens de verificação armazenados como SHA-256


ATUALIZAÇÃO DO BANCO (NOV/2026)
- Faça backup do banco MesaReserva antes de atualizar o projeto.
- Ao abrir o site, config/database.php migra contas antigas da tabela usuarios para administradores e clientes, atualizando perguntas e avaliações antes de remover usuarios.
- Para instalação nova, importe database/mesareserva.sql.
- Administradores ficam em administradores; contas e dados de clientes ficam em clientes.
- A recuperação de senha funciona para os dois tipos de conta e o link expira em 1 hora.


CONFIRMACAO DE CADASTRO (ATUALIZACAO)
- Antes da confirmação do e-mail, os dados do cadastro ficam temporariamente em storage/pending-verifications e NÃO são inseridos nas tabelas clientes ou administradores.
- Depois de clicar no link válido, o cadastro é gravado exclusivamente na tabela correspondente.
- A pasta storage é protegida por .htaccess. Em hospedagens que não respeitam .htaccess, configure essa pasta fora da raiz pública do site.
- Os links internos do MesaReserva navegam na mesma guia. A abertura de um link a partir do Gmail/Outlook pode criar outra guia conforme a configuração do navegador ou do próprio serviço de e-mail; uma página PHP não pode fechar uma guia que foi aberta externamente.
