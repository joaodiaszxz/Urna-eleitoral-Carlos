# Plano e matriz de testes de back-end

Execute somente com banco local de demonstração. Registre evidência real (captura de tela/log) depois de executar cada caso; não marque como aprovado antes de testar.

| ID | Cenário | Entrada | Resultado esperado | Resultado observado |
|---|---|---|---|---|
| CT01 | Eleitor válido | Identificador e senha corretos | Sessão criada; votação liberada | A preencher |
| CT02 | Eleitor inexistente | Identificador não cadastrado | Acesso negado | A preencher |
| CT03 | Senha inválida | Senha incorreta | Acesso negado | A preencher |
| CT04 | Eleitor bloqueado | Conta BLOQUEADO | Acesso negado | A preencher |
| CT05 | Eleitor já votou | `votou = 1` | Novo acesso à votação negado | A preencher |
| CT06 | Candidato válido | Número existente | Candidato e partido exibidos | A preencher |
| CT07 | Número inválido | Número inexistente | Mensagem de não encontrado | A preencher |
| CT08 | Voto branco | Ação BRANCO | Confirmação e registro BRANCO | A preencher |
| CT09 | Confirmar voto | Candidato selecionado | Voto persistido e eleitor marcado | A preencher |
| CT10 | Segundo voto | Repetir confirmação | Bloqueado por regra e restrição única | A preencher |
| CT11 | Eleição encerrada | Tentar votar | Operação bloqueada | A preencher |
| CT12 | Apuração | Votos fictícios no banco | Totais e percentuais coerentes | A preencher |
| CT13 | Administrador autorizado | E-mail e senha válidos de perfil ADMIN | Painel administrativo liberado | A preencher |
| CT14 | Perfil sem permissão administrativa | Usuário OPERADOR ou eleitor | Painel administrativo negado | A preencher |
| CT15 | CRUD administrativo | Salvar/editar eleição, partido e candidato | Dados persistidos; entradas inválidas recusadas | A preencher |
| CT16 | Encerrar eleição | Administrador encerra eleição | Status ENCERRADA; novos votos recusados; reabertura bloqueada | A preencher |
| CT17 | CSRF administrativo | Enviar formulário sem token válido | Requisição recusada | A preencher |

## Registro de defeitos
Para cada falha, anote: ID, data, passos para reproduzir, resultado esperado, resultado encontrado, gravidade, correção aplicada e novo teste de regressão.
