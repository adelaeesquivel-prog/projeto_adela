# Léo Barbas

Site da barbearia com páginas PHP, cabeçalho e rodapé compartilhados e
agendamentos/serviços salvos no servidor.

## Arquivos principais

- `index.php`: menu inicial com os links para Home, Serviços e Agendamento.
- `home.php`, `servicos.php` e `agenda.php`: páginas do site.
- `header.php` e `footer.php`: componentes compartilhados pelas páginas.
- `api.php`: leitura e gravação de serviços, horários e agendamentos.
- `style.css` e `site-pages.css`: estilos existentes, sem alterações.

## Executar localmente

É necessário ter PHP instalado. No PowerShell, na pasta do projeto, configure
uma senha para o painel administrativo e inicie o servidor:

```powershell
$env:LEO_ADMIN_PASSWORD = "escolha-uma-senha-forte"
php -S localhost:8000
```

Abra `http://localhost:8000`. Não abra os arquivos `.php` diretamente no
navegador e não use apenas o Live Server do VS Code: eles precisam ser
servidos pelo PHP.

Os dados ficam em `projeto_adela_dados/dados.json`, na pasta ao lado do projeto.
O servidor precisa ter permissão para criar e gravar nessa pasta. Para escolher
outro local, configure `LEO_DATA_DIR` antes de iniciar o PHP.

O painel administrativo usa `LEO_ADMIN_PASSWORD`; sem essa variável, o login
fica desativado. Os serviços iniciais são criados automaticamente quando o
arquivo de dados ainda não existe.

Os dados antigos guardados no `localStorage` do navegador não são migrados
automaticamente. A partir desta versão, o agendamento e o catálogo usam os
dados compartilhados do servidor.
