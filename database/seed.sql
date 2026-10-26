USE simulador_urna;
INSERT INTO usuarios (nome, email, senha_hash, perfil)
SELECT 'Administrador de demonstração', 'admin@demo.local', '$2y$10$iUmYoFe/3c46GHyVHo0JQ.OIAEuzpGEDOleVDY9HP39U3quPZg/12', 'ADMIN'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'admin@demo.local');

INSERT INTO eleicoes (nome, descricao, status, data_inicio)
VALUES ('Eleição Escolar Fictícia 2026', 'Demonstração educacional com dados inventados.', 'EM_ANDAMENTO', NOW());

INSERT INTO partidos (nome, sigla, numero) VALUES
('Partido Horizonte', 'PHZ', 21),
('Partido Renovar', 'PRN', 32);

INSERT INTO candidatos (nome, numero, cargo, partido_id, eleicao_id) VALUES
('Marina Exemplo', 10, 'Representante escolar', 1, 1),
('Rafael Demonstração', 20, 'Representante escolar', 2, 1);

-- Senha de demonstração: demo123
-- Hash bcrypt gerado para esta senha; altere antes de qualquer uso fora de aula.
INSERT INTO eleitores (identificador, nome, senha_hash, status, votou, eleicao_id) VALUES
('ELEITOR001', 'Alex Exemplo', '$2y$12$.J.X/w7lpKbDcsndUh3a9OJE9WPThi2Pu40WPFZ7UYoS6boxLl8vK', 'ATIVO', 0, 1),
('ELEITOR002', 'Bia Demonstração', '$2y$12$.J.X/w7lpKbDcsndUh3a9OJE9WPThi2Pu40WPFZ7UYoS6boxLl8vK', 'ATIVO', 0, 1),
('ELEITOR003', 'Caio Teste', '$2y$12$.J.X/w7lpKbDcsndUh3a9OJE9WPThi2Pu40WPFZ7UYoS6boxLl8vK', 'BLOQUEADO', 0, 1);
