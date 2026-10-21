<?php
// Contenido de las páginas informativas del pie (info.php?p=...).
// Ryujin es un proyecto académico: los textos son orientativos y no corresponden a una empresa real.

return [
    'sobre-ryujin' => [
        'titulo' => 'Sobre Ryujin',
        'contenido' => <<<HTML
<p>Ryujin es una tienda online especializada en <strong>Trading Card Games</strong> (Pokémon, Magic: The Gathering, Digimon),
figuras Funko Pop y merchandising de anime.</p>
<p>Este sitio se desarrolló como <strong>Trabajo de Fin de Grado</strong> de Javier Galán Cortés. Es un proyecto de
demostración: no se venden productos reales ni se realizan cobros.</p>
<h3>Qué incluye</h3>
<ul>
    <li>Catálogo con buscador, filtros por categoría y ordenación.</li>
    <li>Carrito persistente asociado a la cuenta del usuario.</li>
    <li>Proceso de compra con direcciones de envío y facturación y pago simulado.</li>
    <li>Área de cliente con historial y seguimiento de pedidos.</li>
    <li>Panel de administración de productos, pedidos y usuarios.</li>
</ul>
HTML,
    ],
    'aviso-legal' => [
        'titulo' => 'Aviso Legal',
        'contenido' => <<<HTML
<p>En cumplimiento de la Ley 34/2002, de Servicios de la Sociedad de la Información y de Comercio Electrónico (LSSI-CE),
se informa de que este sitio web es un <strong>proyecto académico sin actividad comercial</strong>.</p>
<h3>Titularidad</h3>
<p>Responsable: Javier Galán Cortés (proyecto de fin de grado). Contacto: info@ryujin.com.</p>
<h3>Propiedad intelectual</h3>
<p>Las marcas Pokémon, Magic: The Gathering, Digimon y Funko, así como las imágenes de sus productos, pertenecen a sus
respectivos propietarios y se muestran únicamente con fines educativos.</p>
<h3>Responsabilidad</h3>
<p>El contenido se ofrece con carácter demostrativo. Ningún pedido realizado en esta web genera obligación de compra o venta.</p>
HTML,
    ],
    'privacidad' => [
        'titulo' => 'Política de privacidad',
        'contenido' => <<<HTML
<p>De acuerdo con el Reglamento General de Protección de Datos (RGPD), te informamos de cómo se tratan los datos en esta web.</p>
<h3>Datos que se recogen</h3>
<ul>
    <li><strong>Cuenta:</strong> nombre de usuario, correo electrónico y contraseña (almacenada cifrada con bcrypt).</li>
    <li><strong>Direcciones:</strong> las que introduzcas para el envío y la facturación de pedidos.</li>
    <li><strong>Métodos de pago:</strong> solo la marca, los 4 últimos dígitos y la caducidad de la tarjeta, o el correo de PayPal.
        Nunca se guarda el número completo ni el CVV.</li>
</ul>
<h3>Finalidad</h3>
<p>Los datos se usan exclusivamente para gestionar la cuenta y los pedidos de demostración. No se ceden a terceros.</p>
<h3>Tus derechos</h3>
<p>Puedes modificar tus datos desde tu perfil o solicitar su eliminación escribiendo a info@ryujin.com.</p>
HTML,
    ],
    'cookies' => [
        'titulo' => 'Política de cookies',
        'contenido' => <<<HTML
<p>Esta web utiliza únicamente <strong>cookies técnicas</strong>, imprescindibles para su funcionamiento:</p>
<ul>
    <li><code>PHPSESSID</code>: mantiene tu sesión iniciada y el contenido del carrito. Se elimina al cerrar el navegador.</li>
</ul>
<p>Además se usa el almacenamiento local del navegador para recordar la última sección abierta en tu perfil.</p>
<p>No se utilizan cookies de análisis, publicidad ni de terceros.</p>
HTML,
    ],
    'formas-de-pago' => [
        'titulo' => 'Formas de pago',
        'contenido' => <<<HTML
<p>Los pagos de esta tienda son <strong>simulados</strong>: no se realiza ningún cargo real.</p>
<h3>Tarjeta de crédito o débito</h3>
<p>Se aceptan Visa, Mastercard y American Express. Puedes guardar tus tarjetas en <em>Perfil &rarr; Métodos de pago</em>.</p>
<p>Tarjetas de prueba:</p>
<ul>
    <li><code>4242 4242 4242 4242</code>: pago aceptado.</li>
    <li><code>4000 0000 0000 0002</code>: pago rechazado por el banco.</li>
</ul>
<p>Cualquier fecha de caducidad futura y cualquier CVV de 3 cifras son válidos.</p>
<h3>PayPal</h3>
<p>Indica el correo de tu cuenta de PayPal y el pago se confirmará automáticamente.</p>
HTML,
    ],
    'devoluciones' => [
        'titulo' => 'Garantías y Devoluciones',
        'contenido' => <<<HTML
<h3>Derecho de desistimiento</h3>
<p>Dispones de <strong>14 días naturales</strong> desde la recepción del pedido para devolverlo sin necesidad de justificación.</p>
<p>Por su naturaleza, los productos sellados (sobres, cajas de sobres, Elite Trainer Box…) solo se admiten si
<strong>conservan el precinto original</strong>.</p>
<h3>Cancelaciones</h3>
<p>Puedes cancelar un pedido desde tu perfil mientras no haya sido enviado. El importe se reembolsa por el mismo método de pago.</p>
<h3>Garantía</h3>
<p>Todos los productos cuentan con la garantía legal de 3 años frente a defectos de fabricación.</p>
HTML,
    ],
    'gastos-de-envio' => [
        'titulo' => 'Gastos de envío',
        'contenido' => <<<HTML
<p>Los envíos a la <strong>península</strong> son <strong>gratuitos</strong> en todos los pedidos.</p>
<p>Para Baleares, Canarias, Ceuta y Melilla consulta las condiciones escribiendo a info@ryujin.com.</p>
HTML,
    ],
    'plazos-de-entrega' => [
        'titulo' => 'Plazos de entrega',
        'contenido' => <<<HTML
<ul>
    <li><strong>Preparación:</strong> 24-48 horas laborables desde la confirmación del pago.</li>
    <li><strong>Envío peninsular:</strong> 2-4 días laborables.</li>
    <li><strong>Preventas:</strong> se envían en la fecha de lanzamiento oficial del producto.</li>
</ul>
<p>Puedes seguir el estado de tus pedidos en <em>Perfil &rarr; Pedidos</em>.</p>
HTML,
    ],
];
