# Roteiro de pitch (3–5 minutos)

**1. Problema e objetivo (30 s)**  
O projeto é um simulador de urna eletrônica exclusivamente educacional, criado para demonstrar autenticação, votação e apuração com dados fictícios.

**2. Solução (40 s)**  
O eleitor usa uma credencial fictícia cadastrada no MySQL. O sistema verifica sua situação e permite consultar um candidato, corrigir a escolha antes de confirmar ou registrar voto branco/nulo.

**3. Tecnologias e arquitetura (50 s)**  
O back-end usa PHP, acesso ao MySQL por PDO e consultas parametrizadas. O projeto separa Controllers, Models e Views seguindo o padrão MVC. HTML, CSS e formulários formam a interface responsiva.

**4. Segurança (40 s)**  
As credenciais são verificadas com `password_verify`, as consultas são parametrizadas e a sessão é regenerada após o login. A confirmação do voto usa transação e há uma restrição única no banco para impedir dois votos do mesmo eleitor na eleição.

**5. Demonstração (60–90 s)**  
Mostrar tela inicial, autenticação com eleitor fictício, consulta de candidato, confirmação de voto, tentativa de segundo voto e página de apuração. Mostrar também casos de teste e o quadro Trello/GitHub.

**6. Aprendizados e limitações (30 s)**  
Explicar que é uma demonstração escolar, não um sistema eleitoral real. Apontar o que foi testado, o que ainda falta implementar e como as evidências foram registradas.
