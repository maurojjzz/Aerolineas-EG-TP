-- Usuarios CEO de prueba (contraseña: 12345678)
--   ceo2@ceo2.ceo2  -> aerolínea 1 (aeroflux)
--   ceo3@ceo3.ceo3  -> aerolínea 3 (Adela)
-- Habilitados y verificados (activo=1, emailVerificado=1, fecha_hora_autorizacion cargada).
-- Se puede ejecutar más de una vez: si el usuario ya existe, lo deja actualizado.
USE aerolinea;

INSERT INTO usuario
  (nombre, apellido, tipoDocumento, nroDocumento, contrasena, email, telefono,
   fechaNacimiento, rol, activo, emailVerificado, fecha_hora_autorizacion,
   tokenVerificacion, idAerolinea)
VALUES
  ('CEO2', 'Prueba', 'DNI', '20000002', '$2y$10$da06YJCRjwQyrJmzIg9hgO7HlUQOwFh00I7ChJMWQ2K4dejkc3Ryy',
   'ceo2@ceo2.ceo2', '1100000002', '1990-01-01', 'ceo', 1, 1, NOW(), NULL, 1),
  ('CEO3', 'Prueba', 'DNI', '20000003', '$2y$10$da06YJCRjwQyrJmzIg9hgO7HlUQOwFh00I7ChJMWQ2K4dejkc3Ryy',
   'ceo3@ceo3.ceo3', '1100000003', '1990-01-01', 'ceo', 1, 1, NOW(), NULL, 3)
ON DUPLICATE KEY UPDATE
  contrasena = VALUES(contrasena),
  rol = 'ceo',
  activo = 1,
  emailVerificado = 1,
  fecha_hora_autorizacion = NOW(),
  idAerolinea = VALUES(idAerolinea);