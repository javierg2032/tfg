document.addEventListener("DOMContentLoaded", () => {
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
  const seccionesPerfil = document.querySelectorAll(".contenido-perfil .seccion");

  if (menuItemsPerfil.length && seccionesPerfil.length) {
    menuItemsPerfil.forEach((item) => {
      item.addEventListener("click", () => {
        const seccionID = item.getAttribute("data-seccion");
        menuItemsPerfil.forEach((i) => i.classList.remove("activo"));
        item.classList.add("activo");
        seccionesPerfil.forEach((sec) => sec.classList.remove("activo"));
        document.getElementById(seccionID)?.classList.add("activo");
      });
    });
  }

 /* === Menú superior del admin === */
  const menuItemsAdmin = document.querySelectorAll(".menu-superior li");
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

  /* === Mostrar/ocultar formulario nuevo producto === */
  const btnNuevoProducto = document.getElementById("nuevo-producto");
  if (btnNuevoProducto) {
    btnNuevoProducto.addEventListener("click", () => {
      const form = document.getElementById("form-nuevo-producto");
      form.style.display = form.style.display === "none" ? "block" : "none";
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
  document.querySelectorAll(".btn-edit").forEach(btn => {
    btn.addEventListener("click", e => {
      e.preventDefault();

      // Rellenar formulario con datos de la fila (data-*)
      document.getElementById("edit-id").value = btn.dataset.id;
      document.getElementById("edit-nombre").value = btn.dataset.nombre;
      document.getElementById("edit-precio").value = btn.dataset.precio;
      document.getElementById("edit-descripcion").value = btn.dataset.descripcion;
      document.getElementById("edit-stock").value = btn.dataset.stock;
      document.getElementById("edit-imagen-actual").value = btn.dataset.imagen;

      drawer.classList.add("abierto");
    });
  });
});
