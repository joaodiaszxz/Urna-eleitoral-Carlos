# Checklist de validação manual

- [ ] Importar schema.sql sem erros.
- [ ] Importar seed.sql e confirmar registros fictícios.
- [ ] Gerar hash real com `password_hash` e atualizar os eleitores de teste.
- [ ] Login válido abre votação.
- [ ] Identificador inexistente é recusado.
- [ ] Senha incorreta é recusada.
- [ ] Eleitor bloqueado é recusado.
- [ ] Número de candidato válido exibe nome/partido.
- [ ] Número inexistente exibe mensagem.
- [ ] Voto válido é persistido e encerra sessão do eleitor.
- [ ] Voto branco é persistido.
- [ ] Voto nulo é persistido.
- [ ] Segundo voto é bloqueado.
- [ ] Eleição encerrada não aceita novos votos (implementar/validar a transição de status).
- [ ] Totais da apuração conferem com consulta direta ao banco.
- [ ] Testar layout em celular e desktop.
- [ ] Registrar evidências e defeitos reais.
