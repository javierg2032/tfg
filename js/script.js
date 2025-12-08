document.addEventListener("DOMContentLoaded", () => {
    // Lógica para el modal de detalles de pedido en perfil.php
    const modalDetalles = document.getElementById('modal-detalles-pedido');
    if (modalDetalles) {
        const spanCerrar = document.getElementById('cerrar-modal-detalles');
        const detalleId = document.getElementById('detalle-id-pedido');
        const listaProductos = document.getElementById('detalle-lista-productos');
        const divEnvio = document.getElementById('detalle-direccion-envio');
        const divFacturacion = document.getElementById('detalle-direccion-facturacion');
        const spanEstado = document.getElementById('detalle-estado');
        const spanTotal = document.getElementById('detalle-total');

        // Cerrar modal
        spanCerrar.onclick = function() {
            modalDetalles.style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == modalDetalles) {
                modalDetalles.style.display = "none";
            }
        }

        // Click en botones "Ver detalles"
        document.querySelectorAll('.btn-ver-detalles').forEach(btn => {
            btn.addEventListener('click', async function() {
                const idPedido = this.getAttribute('data-id');
                // Mostrar modal (cargando...)
                modalDetalles.style.display = "block";
                detalleId.textContent = idPedido;
                listaProductos.innerHTML = '<li>Cargando...</li>';
                divEnvio.textContent = '...';
                divFacturacion.textContent = '...';
                spanEstado.textContent = '...';
                spanTotal.textContent = '...';

                try {
                    const response = await fetch(`php/get_pedido_detalles.php?id=${idPedido}`);
                    const data = await response.json();

                    if (data.error) {
                        alert(data.error);
                        modalDetalles.style.display = "none";
                        return;
                    }

                    // Rellenar datos
                    spanEstado.textContent = data.estado;
                    spanTotal.textContent = data.total;

                    // Direcciones
                    const formatDir = (d) => {
                        if (!d) return 'No disponible (Misma que envío)';
                        return `<strong>${d.nombre}</strong><br>${d.direccion}<br>${d.ciudad}<br>${d.estado}`;
                    };

                    divEnvio.innerHTML = formatDir(data.envio);
                    divFacturacion.innerHTML = formatDir(data.facturacion);

                    // Productos
                    listaProductos.innerHTML = '';
                    data.productos.forEach(prod => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                            <img src="/tfg${prod.imagen}" alt="${prod.nombre}">
                            <div class="producto-info">
                                <div><strong>${prod.nombre}</strong></div>
                                <div>${prod.cantidad} x ${prod.precio_unitario} €</div>
                            </div>
                            <div><strong>${(prod.cantidad * prod.precio_unitario).toFixed(2)} €</strong></div>
                        `;
                        listaProductos.appendChild(li);
                    });

                } catch (error) {
                    console.error('Error:', error);
                    listaProductos.innerHTML = '<li>Error al cargar los detalles.</li>';
                }
            });
        });
    }

    // --- CÓDIGO EXISTENTE ---
  console.log("Script cargado");

  /* === Popup Usuario === */
  const userIcon = document.getElementById("userIcon");
  const loginPopup = document.getElementById("loginPopup");

  if (userIcon && loginPopup) {
    userIcon.addEventListener("click", (e) => {
      e.stopPropagation();
      loginPopup.style.display =
        loginPopup.style.display === "block" ? "none" : "block";
    });

    loginPopup.addEventListener("click", (e) => e.stopPropagation());

    window.addEventListener("click", () => {
      loginPopup.style.display = "none";
    });
  }

  /* === Alternar entre Login y Registro === */
  const loginForm = document.getElementById("formulario-login");
  const registerForm = document.getElementById("formulario-registro");
  const linkRegistro = document.getElementById("link-registro");
  const linkLogin = document.getElementById("link-login");
  const muestraRegistro = document.getElementById("muestra-registro");
  const muestraLogin = document.getElementById("muestra-login");

  if (
    loginForm &&
    registerForm &&
    linkRegistro &&
    linkLogin &&
    muestraRegistro &&
    muestraLogin
  ) {
    muestraRegistro.addEventListener("click", (e) => {
      e.preventDefault();
      loginForm.style.display = "none";
      linkRegistro.style.display = "none";
      registerForm.style.display = "block";
      linkLogin.style.display = "block";
    });

    muestraLogin.addEventListener("click", (e) => {
      e.preventDefault();
      loginForm.style.display = "block";
      linkRegistro.style.display = "block";
      registerForm.style.display = "none";
      linkLogin.style.display = "none";
    });
  }

  /* Menu usuario si está logueado */
  const userMenu = document.getElementById("userMenu");

  if (userMenu) {
    // Mostrar u ocultar menú al hacer clic en el icono
    userIcon.addEventListener("click", (e) => {
      e.stopPropagation();
      userMenu.style.display =
        userMenu.style.display === "block" ? "none" : "block";
    });

    // Cerrar el menú al hacer clic fuera
    window.addEventListener("click", (e) => {
      if (!e.target.closest(".icono-usuario")) {
        userMenu.style.display = "none";
      }
    });
  }

  /* === Carrusel === */
  const diapositivas = document.querySelectorAll(".diapositiva");
  if (diapositivas.length) {
    let diapositivaActual = 0;
    const diapositivasTotales = diapositivas.length;
    let intervalo;

    function muestraDiapositiva(index) {
      diapositivas.forEach((d, i) => {
        d.classList.remove("diapositiva-activa");
        if (i === index) d.classList.add("diapositiva-activa");
      });
    }

    document.querySelector(".siguiente")?.addEventListener("click", () => {
      diapositivaActual = (diapositivaActual + 1) % diapositivasTotales;
      muestraDiapositiva(diapositivaActual);
      reiniciaIntervalo();
    });

    document.querySelector(".previo")?.addEventListener("click", () => {
      diapositivaActual =
        (diapositivaActual - 1 + diapositivasTotales) % diapositivasTotales;
      muestraDiapositiva(diapositivaActual);
      reiniciaIntervalo();
    });

    function reiniciaIntervalo() {
      clearInterval(intervalo);
      intervalo = setInterval(() => {
        diapositivaActual = (diapositivaActual + 1) % diapositivasTotales;
        muestraDiapositiva(diapositivaActual);
      }, 6000);
    }

    muestraDiapositiva(diapositivaActual);
    reiniciaIntervalo();
  }

  /* === Menú lateral del perfil === */
  const menuItemsPerfil = document.querySelectorAll(".menu-lateral li");
  const seccionesPerfil = document.querySelectorAll(
    ".contenido-perfil .seccion"
  );

  if (menuItemsPerfil.length && seccionesPerfil.length) {
    // Función para activar sección
    function activarSeccion(id) {
        menuItemsPerfil.forEach((i) => {
            if(i.getAttribute("data-seccion") === id) i.classList.add("activo");
            else i.classList.remove("activo");
        });
        seccionesPerfil.forEach((sec) => {
            if(sec.id === id) sec.classList.add("activo");
            else sec.classList.remove("activo");
        });
        localStorage.setItem('perfil_seccion_activa', id);
    }

    // Cargar sección guardada
    const seccionGuardada = localStorage.getItem('perfil_seccion_activa');
    if (seccionGuardada) {
        const itemExist = document.querySelector(`.menu-lateral li[data-seccion="${seccionGuardada}"]`);
        if (itemExist) {
            activarSeccion(seccionGuardada);
        }
    }

    menuItemsPerfil.forEach((item) => {
      item.addEventListener("click", () => {
        const seccionID = item.getAttribute("data-seccion");
        activarSeccion(seccionID);
      });
    });
  }

  /* === Menú later del admin === */
  const menuItemsAdmin = document.querySelectorAll(".menu-lateral li");
  const seccionesAdmin = document.querySelectorAll(".contenido-admin .seccion");

  if (menuItemsAdmin.length && seccionesAdmin.length) {
    menuItemsAdmin.forEach((item) => {
      item.addEventListener("click", () => {
        const seccionID = item.getAttribute("data-seccion");
        menuItemsAdmin.forEach((i) => i.classList.remove("activo"));
        item.classList.add("activo");
        seccionesAdmin.forEach((sec) => sec.classList.remove("activo"));
        document.getElementById(seccionID)?.classList.add("activo");
      });
    });
  }

  /* === Drawer de edición de producto === */
  const drawer = document.getElementById("drawer-editar-producto");
  const cerrarDrawer = document.getElementById("cerrar-drawer");

  if (drawer && cerrarDrawer) {
    cerrarDrawer.addEventListener("click", () => {
      drawer.classList.remove("abierto");
    });
  }

  // Botones de editar producto
  document.querySelectorAll(".btn-edit").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();

      // Rellenar formulario con datos de la fila (data-*)
      document.getElementById("edit-id").value = btn.dataset.id;
      document.getElementById("edit-nombre").value = btn.dataset.nombre;
      document.getElementById("edit-precio").value = btn.dataset.precio;
      document.getElementById("edit-descripcion").value =
        btn.dataset.descripcion;
      document.getElementById("edit-stock").value = btn.dataset.stock;
      document.getElementById("edit-imagen-actual").value = btn.dataset.imagen;

      drawer.classList.add("abierto");
    });
  });

  /* === Drawer para crear nuevo producto === */
  const drawerNuevo = document.getElementById("drawer-nuevo");
  const btnNuevoProducto = document.getElementById("nuevo-producto");
  const cerrarNuevo = document.getElementById("cerrar-nuevo");

  if (drawerNuevo && btnNuevoProducto && cerrarNuevo) {
    // Abrir drawer
    btnNuevoProducto.addEventListener("click", (e) => {
      e.preventDefault();
      drawerNuevo.classList.add("abierto");
    });

    // Cerrar drawer
    cerrarNuevo.addEventListener("click", () => {
      drawerNuevo.classList.remove("abierto");
    });
  }

  // Eliminación de productos ahora mediante formularios HTML (no-AJAX).

  // Manejo de cantidades (funciona por cada bloque .cantidad en la página)
  document.querySelectorAll(".cantidad").forEach((cantidadDiv) => {
    const input = cantidadDiv.querySelector("input[type='number']");
    const btnMas = cantidadDiv.querySelector(".mas");
    const btnMenos = cantidadDiv.querySelector(".menos");

    const stockDisponible = parseInt(cantidadDiv.dataset.stock) || Infinity;
    const maxPorPedido = 5;
    const limite = Math.min(stockDisponible, maxPorPedido);

    function mostrarTooltip(mensaje) {
      let tooltip = cantidadDiv.querySelector(".tooltip-cantidad");
      if (tooltip) tooltip.remove();
      tooltip = document.createElement("div");
      tooltip.className = "tooltip-cantidad";
      tooltip.textContent = mensaje;
      cantidadDiv.appendChild(tooltip);
      setTimeout(() => tooltip.remove(), 2000);
    }

    btnMas?.addEventListener("click", () => {
      let valor = parseInt(input.value) || 0;
      if (valor < limite) {
        input.value = valor + 1;
        input.dispatchEvent(new Event("change"));
      } else {
        mostrarTooltip(`Máximo ${limite} unidades`);
      }
    });

    btnMenos?.addEventListener("click", () => {
      const min = parseInt(input.min) || 1;
      let valor = parseInt(input.value) || 0;
      if (valor > min) {
        input.value = valor - 1;
        input.dispatchEvent(new Event("change"));
      }
    });
  });

  /* === Drawer Carrito === */
  /* === Drawer Carrito === */
  const iconoCarrito = document.querySelector(".icono-carrito");
  const drawerCarrito = document.getElementById("drawer-carrito");

  if (iconoCarrito && drawerCarrito) {
    iconoCarrito.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      drawerCarrito.classList.add("abierto");
    });

    // Cerrar drawer
    const cerrarDrawerCarrito = document.getElementById("cerrar-drawer-carrito");
    if (cerrarDrawerCarrito) {
      cerrarDrawerCarrito.addEventListener("click", () => {
        drawerCarrito.classList.remove("abierto");
      });
    }

    drawerCarrito.addEventListener("click", (e) => e.stopPropagation());

    window.addEventListener("click", (e) => {
      if (
        !e.target.closest("#drawer-carrito") &&
        !e.target.closest(".icono-carrito")
      ) {
        drawerCarrito.classList.remove("abierto");
      }
    });

  } else if (iconoCarrito) {
    // Fallback si no hay drawer (otras páginas)
    iconoCarrito.addEventListener("click", () => {
      window.location.href = "carrito.php";
    });
  }

  // Comprobar si hay que abrir el carrito automáticamente (tras redirección PHP)
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('open_cart') === 'true' && drawerCarrito) {
      drawerCarrito.classList.add('abierto');
      // Limpiar la URL para que no se vuelva a abrir al recargar
      const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search.replace(/[?&]open_cart=true/, '');
      window.history.replaceState({path: newUrl}, '', newUrl);
  }
});

/* === Drawer para añadir/editar direcciones en perfil === */
document.addEventListener("DOMContentLoaded", () => {
  const drawerDir = document.getElementById("drawer-direccion");
  const btnNuevoDir = document.getElementById("btn-nueva-direccion");
  const cerrarDrawerDir = document.getElementById("cerrar-drawer-direccion");
  const tituloDrawer = document.getElementById("drawer-direccion-titulo");
  const formDir = document.getElementById("form-direccion");

  // Cancel button inside drawer
  const btnCancelarDir = document.getElementById("btn-cancelar-dir");
  if (btnCancelarDir)
    btnCancelarDir.addEventListener("click", () =>
      drawerDir.classList.remove("abierto")
    );

  // Botón para nueva dirección
  if (btnNuevoDir) {
      btnNuevoDir.addEventListener("click", () => {
          document.getElementById("dir-action").value = "add";
          document.getElementById("dir-id-ud").value = "";
          document.getElementById("dir-id").value = "";
          // Limpiar campos
          document.getElementById("dir-nombre").value = "";
          document.getElementById("dir-apellido").value = "";
          document.getElementById("dir-calle").value = "";
          document.getElementById("dir-ciudad").value = "";
          document.getElementById("dir-codigo_postal").value = "";
          document.getElementById("dir-provincia").value = "";
          document.getElementById("dir-pais").value = "";
          
          const factChk = document.getElementById("dir-facturacion");
          if(factChk) factChk.checked = false;

          document.getElementById("drawer-direccion-titulo").textContent = "Nueva dirección";
          drawerDir.classList.add("abierto");
      });
  }

  // Cerrar drawer al hacer click fuera
  window.addEventListener("click", (e) => {
    if (
      !e.target.closest("#drawer-direccion") &&
      !e.target.closest("#btn-nueva-direccion") &&
      !e.target.closest(".btn-edit-dir")
    ) {
      drawerDir?.classList.remove("abierto");
    }
  });

  // Evitar que el drawer se cierre al hacer click dentro
  drawerDir?.addEventListener("click", (e) => e.stopPropagation());

  // Listeners para botones de editar
  document.querySelectorAll(".btn-edit-dir").forEach(editBtn => {
      editBtn.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation(); // Evitar que cierre el drawer inmediato si hubiera conflicto
        
        document.getElementById("dir-action").value = "edit";
        document.getElementById("dir-id-ud").value =
          editBtn.dataset.id_usuario_direccion || "";
        document.getElementById("dir-id").value =
          editBtn.dataset.id_direccion || "";
        document.getElementById("dir-nombre").value =
          editBtn.dataset.nombre || "";
        document.getElementById("dir-apellido").value =
          editBtn.dataset.apellido || "";
        document.getElementById("dir-calle").value =
          editBtn.dataset.calle || "";
        document.getElementById("dir-ciudad").value =
          editBtn.dataset.ciudad || "";
        document.getElementById("dir-codigo_postal").value =
          editBtn.dataset.codigo_postal || "";
        document.getElementById("dir-provincia").value =
          editBtn.dataset.provincia || "";
        document.getElementById("dir-pais").value = editBtn.dataset.pais || "";
        
        const factChk = document.getElementById("dir-facturacion");
        if (factChk) factChk.checked = editBtn.dataset.facturacion === "1";
        
        const tipoSel = document.getElementById("dir-id-tipo");
        if (tipoSel && editBtn.dataset.id_tipo)
          tipoSel.value = editBtn.dataset.id_tipo;
          
        document.getElementById("drawer-direccion-titulo").textContent =
          "Editar dirección";
        drawerDir.classList.add("abierto");
      });
  });
});
