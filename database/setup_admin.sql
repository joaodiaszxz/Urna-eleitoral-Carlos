USE simulador_urna;

INSERT INTO usuarios (nome, email, senha_hash, perfil)
SELECT 'Administrador de demonstração', 'admin@demo.local', '$2y$10$iUmYoFe/3c46GHyVHo0JQ.OIAEuzpGEDOleVDY9HP39U3quPZg/12', 'ADMIN'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'admin@demo.local');

-- Credenciais apenas para o ambiente local de demonstração:
-- admin@demo.local / UrnaDemo#2026
-- Troque a senha e remova esta conta antes de publicar o projeto.
