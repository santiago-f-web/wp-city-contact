document.addEventListener("DOMContentLoaded", () => {
    const tabla = document.getElementById("contact-table-body");
    const mensaje = document.getElementById("management-message");
    const form = document.getElementById("add-contact-form");

    // ✅ Función para cargar todos los contactos
    function cargarTabla() {
        tabla.innerHTML = `<tr><td colspan="3">Cargando...</td></tr>`;

        fetch(cityContactAjax.ajax_url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ action: "get_all_city_contacts" })
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    tabla.innerHTML = `<tr><td colspan="3">Error al cargar datos.</td></tr>`;
                    return;
                }

                tabla.innerHTML = "";
                data.data.forEach(({ ciudad, numero }) => {
                    const fila = document.createElement("tr");

                    fila.innerHTML = `
                        <td>${ciudad}</td>
                        <td><input type="text" value="${numero}" data-old="${numero}" data-ciudad="${ciudad}" /></td>
                        <td>
                            <button class="button editar">Guardar</button>
                            <button class="button delete">Eliminar</button>
                        </td>
                    `;

                    tabla.appendChild(fila);
                });
            });
    }

    cargarTabla();

    // ✅ Agregar nuevo contacto
    form.addEventListener("submit", e => {
        e.preventDefault();

        const ciudad = form.ciudad.value.trim();
        const contacto = form.contacto.value.trim();

        if (!ciudad || !contacto) return;

        const body = new URLSearchParams({
            action: "add_city_contact",
            ciudad,
            contacto
        });

        fetch(cityContactAjax.ajax_url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body
        })
            .then(res => res.json())
            .then(data => {
                const desc = data?.data?.[0]?.DESCRIPCION || "Guardado";
                mensaje.textContent = desc;
                mensaje.style.display = "block";
                setTimeout(() => mensaje.style.display = "none", 2000);
                form.reset();
                cargarTabla();
            });
    });

    // ✅ Delegación de eventos para editar y eliminar
    tabla.addEventListener("click", e => {
        const tr = e.target.closest("tr");
        const input = tr?.querySelector("input");

        if (!input) return;

        const ciudad = input.dataset.ciudad;
        const old = input.dataset.old;
        const nuevo = input.value.trim();

        if (e.target.classList.contains("editar")) {
            if (nuevo !== old) {
                const body = new URLSearchParams({
                    action: "edit_city_contact",
                    ciudad,
                    old,
                    new: nuevo
                });

                fetch(cityContactAjax.ajax_url, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body
                }).then(() => cargarTabla());
            }
        }

        if (e.target.classList.contains("delete")) {
            if (!confirm("¿Eliminar este número?")) return;

            const body = new URLSearchParams({
                action: "delete_city_contact",
                ciudad,
                contacto: nuevo
            });

            fetch(cityContactAjax.ajax_url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body
            }).then(() => cargarTabla());
        }
    });
});
