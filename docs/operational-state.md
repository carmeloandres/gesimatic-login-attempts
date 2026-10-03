# Estado operativo de los intentos de acceso

La configuración del limitador se comparte mediante `OptionManager`. El estado
operativo también es global en WordPress Multisite y se almacena en una única
tabla construida con `$wpdb->base_prefix`.

Por tanto, todos los sitios de una red comparten el contador y el bloqueo de cada
IP:

- un fallo de autenticación en cualquier sitio incrementa el mismo contador;
- un bloqueo generado desde un sitio impide el acceso desde esa IP a todos los
  sitios de la red;
- un login correcto en cualquier sitio elimina el registro global de la IP y
  reinicia sus intentos y bloqueo para toda la red.

En una instalación normal, el comportamiento es equivalente, limitado a ese
único sitio.

## Consideración sobre direcciones IP compartidas

Una IP pública puede representar a muchos usuarios, por ejemplo en oficinas,
universidades, VPN o redes con CGNAT. La actividad de una sola persona puede
bloquear esa IP en todos los sitios de la red hasta que expire el bloqueo o un
administrador lo elimine. Este efecto es deliberado y aumenta la protección
global, pero debe tenerse en cuenta al configurar los límites y periodos.
