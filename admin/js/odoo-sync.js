
async function postData(url = '', data = {}) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            redirect: 'follow',
            referrerPolicy: 'no-referrer',
            body: JSON.stringify(data)
        });
        return response.json();
    } catch (err) {
        console.log(err);
    }
}


if (document.getElementById('syn-contacts-loading')) {
    const message = document.getElementById('syn-contacts-loading');
    const userCreation = document.getElementById('user-creation');

    const buttonSync = document.getElementById('btn-sync-contact-new');
    const buttonCU = document.getElementById('c-users');
    const quantity = document.getElementById('numero');

    if(buttonCU) {
        buttonCU.addEventListener('click', () => {
            createUsers(quantity, userCreation, message);
        });
    }
   

    buttonSync.addEventListener('click', () => {
        const roles = document.querySelectorAll('input[type=checkbox]:checked');

        syncUsers(roles, buttonSync, message);
    });
}

const syncUsers = async (roles, button, message) => {
    var usersProcessed = 0;
    var role = [];
    for (var i = 0; i < roles.length; i++) {
        role.push(roles[i].value)
    }

    if (role.length <= 0) {
        alert('Debe seleccionar al menos un perfil para sincronizar.');
        return;
    }

    var date = new Date();
    var hour = date.getHours();
    var min = date.getMinutes();

    button.style.display = 'none';
    message.style.display = 'block';
    message.innerHTML = `Inicio de sincro: ${hour}:${min}<span id="user_id_show"></span>`;

    const dataUsers = await fetch(varOdoo.getURL);
    let users = await dataUsers.json();
 
    if (users.length <= 0) {
        message.innerHTML = 'Nada para sincronizar.';
        return;
    }

    for (const user of users) {
        await syncTheUsers(user.ID);
        usersProcessed++;
        if (usersProcessed === users.length) {
            message.innerHTML = `Sincronización finalizada correctamente: ${hour}:${min}.`;
            button.style.display = 'block';
        }
    }

}

async function syncTheUsers(ID) {
    try {
        const syncUser = await postData(varOdoo.syncURL, { ID });
        if (syncUser) {
            document.getElementById('user_id_show').innerHTML = ` Sincronizando el usuario ID: ${ID}`;
        }
    } catch (err) {
        console.log('err en el catch', err);
    }
}

const createUsers = async (quantity, div, message) => {
    //validation
    if (quantity.value === '' || null === quantity.value) {
        alert('Debe especificar la cantidad de usuarios');
    }

    //hide form
    div.style.display = 'none';
    //show message
    message.style.display = 'block';
    message.innerHTML = 'Creando Usuarios, un momento por favor. Segundos transcurridos: <span id="count">1</span>';

    const show_seconds = setInterval(() => {
        var seconds = document.getElementById('count').innerHTML;
        seconds++;
        document.getElementById('count').innerHTML = seconds;
    }, 1000);


    const createUser = await postData(varOdoo.usrURL, { quantity: quantity.value })
        .then(res => {
            console.log(res);
            //clear interval
            clearInterval(show_seconds);

            if (!res.success) {

                if (res.data) {
                    message.innerHTML = res.data; //messages
                }


                div.style.display = 'block';
                setTimeout(() => { //hide message again
                    message.style.display = 'none';
                    message.innerHTML = '';
                }, 3000);
                return;
            }

            quantity.value = ''; //clean field value
            div.style.display = 'block';
            message.innerHTML = 'Usuarios creados'; //show message

            //hide message again
            setTimeout(() => {
                message.style.display = 'none';
                message.innerHTML = '';
            }, 3000);

        })
        .catch(err => {
            console.log('error', err);
        });
    return createUser;
}
