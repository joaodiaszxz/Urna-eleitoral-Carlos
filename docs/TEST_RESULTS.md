# Resultados dos testes

Data da execução: 26/10/2026  
Ambiente: PHP 8.3.36, MySQL do XAMPP, Windows; testes de banco executados em `simulador_urna_test`, separado do banco principal.

## Testes de integração

Comando no PowerShell: `$env:DB_NAME = 'simulador_urna_test'; C:\xampp\php\php.exe tests\run.php`

| Cenário | Resultado |
|---|---|
| Login administrativo válido; recusa de senha inválida e perfil OPERADOR | Aprovado |
| Criação e edição de eleição em rascunho | Aprovado |
| Criação e edição de partido | Aprovado |
| Criação e edição de candidato em rascunho | Aprovado |
| Ativação da eleição e bloqueio de edição da cédula | Aprovado |
| Bloqueio de mover candidato para outra eleição após início | Aprovado |
| Bloqueio de edição do partido depois do início da eleição | Aprovado |
| Registro de voto e recusa de segundo voto | Aprovado |
| Encerramento irreversível e recusa de votos posteriores | Aprovado |

**Resultado: 9 testes aprovados.** Os dados criados pela execução foram removidos da base de teste ao final.

## Verificações HTTP no Apache local

| Verificação | Resultado |
|---|---|
| Formulário de login administrativo e acesso ao painel com a conta local | Aprovado |
| Acesso anônimo ao painel redirecionado para o login | Aprovado |
| Gravação administrativa com token CSRF inválido recusada | Aprovado |

Todos os arquivos PHP do projeto também passaram por `php -l`.

Esses testes validam o projeto de demonstração local; não constituem certificação ou auditoria para eleições reais.
