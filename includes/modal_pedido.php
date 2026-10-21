<!-- Modal Detalles Pedido (se rellena desde js/script.js con php/get_pedido_detalles.php) -->
<div id="modal-detalles-pedido" class="modal">
    <div class="modal-content">
        <span class="cerrar-modal" id="cerrar-modal-detalles">&times;</span>
        <h3>Detalles del Pedido #<span id="detalle-id-pedido"></span></h3>
        <div id="detalle-contenido">
            <div class="detalle-seccion">
                <h4>Productos</h4>
                <ul id="detalle-lista-productos" class="lista-productos-detalle"></ul>
            </div>
            <div class="detalle-grid-direcciones">
                <div class="detalle-seccion">
                    <h4>Dirección de Envío</h4>
                    <p id="detalle-direccion-envio"></p>
                </div>
                <div class="detalle-seccion">
                    <h4>Dirección de Facturación</h4>
                    <p id="detalle-direccion-facturacion"></p>
                </div>
            </div>
            <div class="detalle-seccion">
                <h4>Resumen</h4>
                <p><strong>Fecha:</strong> <span id="detalle-fecha"></span></p>
                <p><strong>Estado:</strong> <span id="detalle-estado"></span></p>
                <p><strong>Pago:</strong> <span id="detalle-pago"></span></p>
                <p><strong>Total:</strong> <span id="detalle-total"></span> €</p>
            </div>
        </div>
    </div>
</div>
