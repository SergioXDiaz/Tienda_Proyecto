-- Volcado de datos para la tabla `destinos`
--

INSERT INTO `destinos` (`id`, `nombre`, `descripcion`, `precio`, `clima`, `tipo`, `imagen`) VALUES
(1, 'París, Francia', 'La ciudad del amor, ideal para visitar museos y monumentos históricos.', 750.00, 'templado', 'cultura', NULL),
(2, 'Reikiavik, Islandia', 'Tierra de glaciares, auroras boreales y volcanes activos.', 1200.00, 'frio', 'aventura', NULL),
(3, 'Roma, Italia', 'Un museo al aire libre con la mejor gastronomía del mundo.', 600.00, 'templado', 'cultura', NULL),
(4, 'Zermatt, Suiza', 'Esquí de lujo a los pies del imponente monte Cervino.', 1400.00, 'frio', 'aventura', NULL),
(5, 'Santorini, Grecia', 'Vistas espectaculares al mar Egeo con sus famosas casas blancas.', 950.00, 'calido', 'relax', NULL),
(6, 'Barcelona, España', 'La ciudad de Gaudí, con playa, la Sagrada Familia y una cultura vibrante.', 600.00, 'templado', 'cultura', NULL),
(7, 'Moscú, Rusia', 'La Plaza Roja y el Kremlin bajo un manto de nieve espectacular.', 950.00, 'frio', 'cultura', NULL),
(8, 'Nueva York, EEUU', 'La ciudad que nunca duerme. Rascacielos y cultura urbana.', 1300.00, 'templado', 'cultura', NULL),
(9, 'Cancún, México', 'Playas de arena blanca y ruinas mayas impresionantes.', 850.00, 'calido', 'relax', NULL),
(10, 'Patagonia, Argentina', 'Naturaleza salvaje, glaciares y senderismo de alto nivel.', 1600.00, 'frio', 'aventura', NULL),
(11, 'Río de Janeiro, Brasil', 'Carnaval, samba y las playas más famosas del mundo.', 900.00, 'calido', 'relax', NULL),
(12, 'Cusco, Perú', 'La puerta de entrada a Machu Picchu y el imperio Inca.', 1100.00, 'templado', 'cultura', NULL),
(13, 'Tokio, Japón', 'Una mezcla perfecta entre templos antiguos y luces de neón.', 1500.00, 'templado', 'cultura', NULL),
(14, 'Bali, Indonesia', 'Espiritualidad, arrozales y playas tropicales para desconectar.', 1050.00, 'calido', 'relax', NULL),
(15, 'Dubái, Emiratos Árabes', 'Lujo extremo, desierto y arquitectura futurista.', 1800.00, 'calido', 'aventura', NULL),
(16, 'Sídney, Australia', 'Iconos modernos frente al mar y una vibrante vida nocturna.', 2000.00, 'templado', 'relax', NULL),
(17, 'Bora Bora, Polinesia', 'El paraíso definitivo de cabañas sobre el agua y relax absoluto.', 2500.00, 'calido', 'relax', NULL),
(18, 'Himalaya, Nepal', 'El techo del mundo, solo para los amantes del riesgo y la montaña.', 1700.00, 'frio', 'aventura', NULL),
(19, 'El Cairo, Egipto', 'Las pirámides milenarias y los misterios del río Nilo.', 800.00, 'calido', 'cultura', NULL),
(20, 'Ciudad del Cabo, Sudáfrica', 'Donde se encuentran dos océanos, entre montañas y viñedos.', 1200.00, 'templado', 'aventura', NULL),
(21, 'Masái Mara, Kenia', 'Safari inolvidable para ver a los cinco grandes de la selva.', 2200.00, 'calido', 'aventura', NULL);

-- --------------------------------------------------------

-- Volcado de datos para la tabla `resenas`
--

INSERT INTO `resenas` (`id`, `usuario_id`, `id_destino`, `comentario`, `puntuacion`, `fecha`) VALUES
(1, 3, 2, 'Increíble viaje a Reikiavik. Las auroras boreales son algo que hay que ver una vez en la vida.', 5, '2026-01-17 12:33:11'),
(2, 4, 6, 'Barcelona es preciosa, pero en verano hay demasiada gente en la Sagrada Familia.', 4, '2026-01-17 12:33:11'),
(3, 5, 15, 'Probé el viaje sorpresa y me tocó Dubái. ¡Un lujo por la mitad de precio!', 5, '2026-01-17 12:33:11'),
(4, 6, 4, 'El viaje a los Alpes estuvo bien, pero el hotel era un poco ruidoso. El servicio de la web excelente.', 3, '2026-01-17 12:33:11'),
(5, 3, 10, 'No me gustó la organización en la selva Amazónica, mucha espera en los traslados.', 2, '2026-01-17 12:33:11');

-- --------------------------------------------------------

-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `id_usuario`, `id_destino`, `fecha_viaje`, `fecha_reserva`, `tipo_tarifa`) VALUES
(2, 2, 2, '2026-01-21', '2026-01-17 18:02:15', 'normal'),
(3, 4, 11, '2026-02-28', '2026-01-18 18:06:32', 'normal');

-- --------------------------------------------------------

-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `ultimo_intento_sorpresa`) VALUES
(1, 'Administrador', 'admin@viajes.com', '0192023a7bbd73250516f069df18b500', 'admin', NULL),
(2, 'Usuario de Prueba', 'user@test.com', '6ad14ba9986e3615423dfca256d04e3f', 'usuario', '2026-01-17 19:14:03'),
(3, 'Carlos Ruiz', 'carlosruiz@correo.com', 'dc599a9972fde3045dab59dbd1ae170b', 'usuario', '2026-01-18 19:02:47'),
(4, 'Laura Gómez', 'lauragomez@correo.com', '680e89809965ec41e64dc7e447f175ab', 'usuario', '2026-01-18 19:05:48'),
(5, 'Diego Torres', 'diegotorres@correo.com', '078c007bd92ddec308ae2f5115c1775d', 'usuario', NULL),
(6, 'Elena Belmonte', 'elenabelmonte@correo.com', 'fadf17141f3f9c3389d10d09db99f757', 'usuario', NULL);

-- --------------------------------------------------------

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `destinos`
--
ALTER TABLE `destinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `resenas`
--
ALTER TABLE `resenas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `viajes_sorpresa`
--
ALTER TABLE `viajes_sorpresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD CONSTRAINT `resenas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id`);

--
-- Filtros para la tabla `viajes_sorpresa`
--
ALTER TABLE `viajes_sorpresa`
  ADD CONSTRAINT `viajes_sorpresa_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `viajes_sorpresa_ibfk_2` FOREIGN KEY (`destino_id`) REFERENCES `destinos` (`id`);
COMMIT;