document.addEventListener("DOMContentLoaded", function () {
    const select = document.getElementById("city");
    const phoneList = document.getElementById("phone-list");
    const saveBtn = document.getElementById("save-selection");

    // ✅ 1. Cargar ciudades dinámicamente desde REST API
    fetch('/wp-json/ccm/v1/ciudades')
        .then(res => res.json())
        .then(json => {

            if (json.resultado && Array.isArray(json.data)) {
                select.innerHTML = '';
                const defaultOption = document.createElement('option');
                defaultOption.textContent = 'Seleccione una ciudad';
                defaultOption.disabled = true;
                defaultOption.selected = true;
                select.appendChild(defaultOption);

                json.data.forEach(ciudad => {
                    const opt = document.createElement('option');
                    opt.value = ciudad;
                    opt.textContent = ciudad;
                        // ✅ Aquí preseleccionás si coincide con la guardada
                    if (ciudad === cityContactAjax.ciudad_actual) {
                         opt.selected = true;
                    }
                    select.appendChild(opt);
                });
            } else {
                select.innerHTML = '<option>Error al cargar ciudades</option>';
            }
        })
        .catch(err => {
            console.error('Error al cargar ciudades:', err);
            select.innerHTML = '<option>Error de conexión</option>';
        });

    // ✅ 2. Buscar teléfonos cuando hacen clic en "Buscar"
    document.getElementById("search-contact").addEventListener("click", function () {
        const city = select.value;
        phoneList.innerHTML = "<li>Cargando...</li>";

        fetch(cityContactAjax.ajax_url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=get_city_contacts&city=${encodeURIComponent(city)}`
        })
            .then(response => response.json())
            .then(data => {
                phoneList.innerHTML = "";
                if (data.success) {
                    data.data.phones.forEach(phone => {
                        const li = document.createElement("li");
                        li.textContent = phone;
                        phoneList.appendChild(li);
                    });
                    saveBtn.style.display = "block";
                } else {
                    phoneList.innerHTML = `<li>Error: ${data.data.message}</li>`;
                    saveBtn.style.display = "none";
                }
            })
            .catch(error => {
                console.error("Error:", error);
                phoneList.innerHTML = "<li>Error de red</li>";
                saveBtn.style.display = "none";
            });
    });
    
    saveBtn.addEventListener('click', () => {
    const city = document.getElementById("city").value;

    if (!city) {
        alert("Selecciona una ciudad primero");
        return;
    }

    // 🔥 Consultar API para obtener todos los teléfonos
    fetch(cityContactAjax.ajax_url, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            action: "get_city_contacts",
            city: city
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success || !Array.isArray(data.data.phones)) {
            alert("❌ No se encontraron teléfonos.");
            return;
        }

        // ✅ Enviar a WordPress vía AJAX para guardar en DB
        return fetch(cityContactAjax.ajax_url, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                action: "ccm_guardar_telefonos",
                ciudad: city,
                telefonos: JSON.stringify(data.data.phones)
            })
        });
    })
    .then(res => res?.json())
    .then(data => {
        if (data?.success) {
            alert("📦 Teléfonos guardados correctamente.");
        } else {
            alert("❌ Error al guardar: " + (data?.data?.message || 'Desconocido'));
        }
    })
    .catch(err => {
        console.error("❌ Error AJAX:", err);
        alert("Error en la conexión");
    });
});

    
});
