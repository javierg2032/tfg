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

document.querySelectorAll(".btn-delete").forEach(btn => {
  btn.addEventListener("click", async (e) => {
    e.preventDefault(); // Muy importante
    const id = btn.dataset.id;

    if (!confirm("¿Eliminar este producto?")) return;

    try {
      const formData = new FormData();
      formData.append("id_producto", id);

      const response = await fetch("php/elimina_producto.php", {
        method: "POST",
        body: formData
      });
      const result = await response.json();

      if (result.status === "success") {
        alert(result.message);
        // Opcional: eliminar fila de la tabla
        btn.closest("tr").remove();
      } else {
        alert("Error: " + result.message);
      }
    } catch (err) {
      console.error(err);
      alert("Error al eliminar producto");
    }
  });
});

});
