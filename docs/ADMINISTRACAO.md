# Administração local

## Configuração inicial

No XAMPP, importe `database/setup_admin.sql` pelo phpMyAdmin ou execute no PowerShell:

```powershell
Get-Content -Raw database\setup_admin.sql | C:\xampp\mysql\bin\mysql.exe -uroot
```

A conta de demonstração é `admin@demo.local` com senha `UrnaDemo#2026`. Ela serve somente para desenvolvimento local. Troque a senha e remova essa conta antes de publicar o site ou usar dados reais.

Com Apache e MySQL iniciados, abra `http://localhost/trabalho%20carlos/public/?action=admin_login`.

## Fluxo recomendado

1. Cadastre uma eleição; ela começa em **RASCUNHO**.
2. Cadastre os partidos e candidatos da eleição em rascunho.
3. Edite a eleição e mude o status para **EM_ANDAMENTO**.
4. Ao terminar, edite a eleição e mude o status para **ENCERRADA**.

Uma eleição encerrada não pode ser reaberta. Candidatos só podem ser cadastrados/editados em eleições em rascunho; partidos vinculados a eleições iniciadas também ficam protegidos contra edição. Depois do primeiro voto, a eleição só pode ser encerrada. Todas as alterações administrativas usam sessão autenticada, validação de perfil e token CSRF.

## Testes automatizados

Os testes usam uma base separada chamada `simulador_urna_test`; não aponte o script para o banco de demonstração principal.

```powershell
Get-Content -Raw tests\schema.sql | C:\xampp\mysql\bin\mysql.exe -uroot
$env:DB_NAME = 'simulador_urna_test'
C:\xampp\php\php.exe tests\run.php
```

O script recusa qualquer `DB_NAME` diferente da base de teste e remove os registros de teste que cria.
