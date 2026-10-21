<?php
// Pie común: drawer del carrito, footer y scripts. Cierra <body> y <html>.
?>
    <!-- Drawer Carrito -->
    <div id="drawer-carrito" class="drawer">
        <div class="drawer-header">
            <h3>Tu Carrito</h3>
            <span id="cerrar-drawer-carrito" class="cerrar">&times;</span>
        </div>
        <div class="drawer-body" id="contenido-carrito">
            <?php include __DIR__ . '/../php/muestra_carrito.php'; ?>
        </div>
    </div>

    <footer>
        <div class="logo-footer"><img src="assets/Logo.png" alt="logo" /></div>
        <div class="info-footer">
            <h4><u>INFORMACIÓN</u></h4>
            <a href="info.php?p=sobre-ryujin">
                <p>Sobre Ryujin</p>
            </a>
            <a href="info.php?p=aviso-legal">
                <p>Aviso Legal</p>
            </a>
            <a href="info.php?p=privacidad">
                <p>Política de privacidad</p>
            </a>
            <a href="info.php?p=cookies">
                <p>Política de cookies</p>
            </a>
        </div>
        <div class="condiciones-footer">
            <h4><u>CONDICIONES</u></h4>
            <a href="info.php?p=formas-de-pago">
                <p>Formas de pago</p>
            </a>
            <a href="info.php?p=devoluciones">
                <p>Garantias y Devoluciones</p>
            </a>
            <a href="info.php?p=gastos-de-envio">
                <p>Gastos de envío</p>
            </a>
            <a href="info.php?p=plazos-de-entrega">
                <p>Plazos de entrega</p>
            </a>
        </div>
        <div class="contacto-footer">
            <h4><u>CONTACTO</u></h4>
            <a href="mailto:info@ryujin.com">
                <p><i class="fas fa-envelope"></i>info@ryujin.com</p>
            </a>
        </div>
        <div class="copy-footer">
            <p>Copyright © 2025 Ryujin. Diseñado por Javier Galán Cortés</p>
        </div>
    </footer>
    <script src="js/script.js"></script>
</body>

</html>
