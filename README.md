# Léo Barbas

Site da barbearia em PHP com catálogo de serviços, cadastro e login de clientes,
agendamento online e consulta dos pedidos do próprio cliente. O administrador
acessa um painel protegido para acompanhar os agendamentos recebidos.

## Páginas principais

- `index.php` e `home.php`: início do site.
- `servicos.php`: catálogo de serviços.
- `agenda.php`: reserva de horário para clientes autenticados.
- `historico.php`: histórico de agendamentos da conta conectada.
- `admin/index.php`: resumo administrativo.
- `admin/agendamento.php`: lista de agendamentos com dados do cliente e serviço.
- `api.php`: autenticação, cadastro, disponibilidade e operações de agendamento.

O ícone de perfil no cabeçalho permite criar uma conta de cliente ou entrar
como cliente/administrador. Os agendamentos ficam associados à conta conectada;
cada cliente só consulta os próprios registros.

Se a tabela `servico` estiver vazia, o catálogo cadastra nela os seis serviços
padrão do site. A agenda usa os mesmos registros e IDs para preencher o seletor
e validar a reserva.

## Executar localmente

É necessário ter PHP 8.1 ou superior, as extensões PDO e PDO MySQL, e o banco
MySQL configurado para o projeto. A conexão e o nome do banco são definidos em
`src/Database/ConexaoBD.php`; as tabelas `cliente`, `administrador`, `servico`
e `agendamento` precisam existir no banco antes de iniciar o site.

Na pasta do projeto, inicie o servidor PHP:

```powershell
php -S localhost:8000
```

Abra `http://localhost:8000` no navegador. Não abra os arquivos `.php`
diretamente e não use apenas o Live Server do VS Code: as páginas precisam ser
servidas pelo PHP.
