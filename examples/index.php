<?php

require_once '../vendor/autoload.php';

use ialopezg\Libraries\Collection;

class ItemCollection extends Collection { }

session_start();

if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    $action = isset($_REQUEST['a']) ? $_REQUEST['a'] : 'add';

    switch ($action) {
        case 'add':
            $request = json_decode(trim(file_get_contents("php://input")), true);
            $default_connection = $request['default_connection'] ? $request['name'] : '';
            unset($request['default_connection']);

            $_SESSION['databases']->set("databases.connections.{$request['name']}", $request);
            $_SESSION['databases']->set("databases.default_connection",
                (empty($default_connection) ? array_keys($_SESSION['databases']->get('databases.connections'))[0] : $default_connection));
            $name = strtoupper($request['name']);

            $result['status'] = 'success';
            $result['message'] = "Connection name {$request['name']} successfully added.";
            break;
        case 'default':
            $request = json_decode(trim(file_get_contents("php://input")), true);

            if ($_SESSION['databases']->has("databases.connections.{$request['name']}")) {
                $_SESSION['databases']->set('databases.default_connection', $request['name']);

                $status_code = 200;
                $result['status'] = 'success';
                $result['message'] = 'Connection name ' . strtoupper($request['name']) . ' successfully saved.';
            } else {
                $status_code = 403;

                $result['status'] = 'error';
                $result['message'] = 'Connection name ' . strtoupper($request['name']) . ' does not exists.';
            }
            break;
        case 'delete':
            $request = json_decode(trim(file_get_contents("php://input")), true);
            $connection_name = "databases.connections.{$request['name']}";
            $name = strtoupper($request['name']);

            if ($_SESSION['databases']->has($connection_name)) {
                $status_code = 200;
                $_SESSION['databases']->remove($connection_name);

                $result['status'] = 'success';
                $result['message'] = "Connection name ${name} successfully deleted.";
            } else {
                $status_code = 404;
                $result['status'] = 'error';
                $result['message'] = "Connection name ${name} does not exists.";
            }
            break;
        case 'load':
        default:
            $result['status'] = 'success';
            if ($_SESSION['databases']->has('databases.connections')) {
                if (count($_SESSION['databases']->get('databases.connections')) === 1) {
                    $_SESSION['databases']->set('databases.default_connection', array_keys($_SESSION['databases']->get('databases.connections'))[0]);
                }
            }
            $result['databases'] = $_SESSION['databases']->get('databases');
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($result);

    exit();
}

if (!isset($_SESSION['databases'])) {
    $_SESSION['databases'] = new ItemCollection([]);
}
$count = $_SESSION['databases']->has('databases.connections')
    ? count($_SESSION['databases']->get('databases.connections')) : 0;
$default_connection = $_SESSION['databases']->get('databases.default_connection')
    ? $_SESSION['databases']->get('databases.default_connection')
    : (($count > 1 && !$_SESSION['databases']->get('databases.default_connection'))
        ? array_keys($_SESSION['databases']->get('databases.connections'))[0]
        : 'Not connections founds');
$_SESSION['databases']->set('databases.default_connection', $default_connection);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Connection Manager</title>
    <link rel="stylesheet" href="app.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Collections Samples</h1>
        </header>
        <main style="margin: 2px;">
            <div style="display: flex; justify-content: space-between; flex-flow: row-reverse;">
                <span id="connection-count"><strong>Connections Count:</strong> <?= $count ?></span>
                <span id="default-connection"><strong>Default Connection:</strong> <?= $_SESSION['databases']->get('databases.default_connection', 'N/A'); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; flex-flow: row">
                <div class="left" style="width: 100%;">
                    <div style="margin: auto;" class="connections"></div>
                </div>
                <div class="right" style="text-align: center; width: 100%;">
                    <form name="add-form">
                        <fieldset style="text-align: left; background-color: #f1f1f1;">
                            <legend>Add new connection</legend>
                            <input type="text" name="name" placeholder="Connection name" style="width: 94%; margin: 10px auto; padding: 5px;">
                            <input type="text" name="driver" placeholder="Database driver" style="width: 94%; margin: 10px auto; padding: 5px;">
                            <input type="text" name="hostname" placeholder="Database hostname" style="width: 94%; margin: 10px auto; padding: 5px;">
                            <input type="text" name="username" placeholder="Database username" style="width: 94%; margin: 10px auto; padding: 5px;">
                            <input type="password" name="password" placeholder="Database user password" style="width: 94%; margin: 10px auto; padding: 5px;">
                            <input type="text" name="database" placeholder="Database name" style="width: 94%; margin: 10px auto; padding: 5px;">
                            <input type="text" name="charset" placeholder="Database charset" style="width: 94%; margin: 10px auto; padding: 5px;">
                            <input type="checkbox" name="default_connection" value="1"> Default connection
                        </fieldset>
                        <button class="btn-add" style="width: 100%; padding: 10px; margin: 10px auto;">Add connection</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function (e) {
            const container = document.querySelector('div.connections')

            loadItems(container)

            document.querySelector('form[name="add-form"]').addEventListener('submit', async function (e) {
                e.preventDefault()

                formEnable(e.target, false)

                if (formDirty(e.target, 'invalid')) {
                    const data = formData(e.target)
                    await doAsyncAction('index.php?a=add', 'POST', {
                        'Content-Type': 'application/json',  // sent request
                        'Accept': 'application/json',   // expected data sent back
                        'X-Requested-With': 'FetchAPIRequest'
                    }, data)
                        .then(data => {
                            if (data.status === 'success') {
                                alert(`${data.status.toUpperCase()}: ${data.message}`)
                            } else {
                                alert(`${data.status.toUpperCase()}: #${data.code} ${data.message}`)
                            }
                        })
                        .catch(error => console.log(error))

                    await loadItems(container)
                } else {
                    alert('Please, fill all required fields.')
                }
                cleanForm(e.target)

                formEnable(e.target, true)
            })
        })

        async function loadItems(container) {
            container.innerHTML = ''

            await doAsyncAction('index.php?a=load', 'GET', {
                'Content-Type': 'application/json',  // sent request
                'Accept': 'application/json',   // expected data sent back
                'X-Requested-With': 'FetchAPIRequest'
            })
                .then(data => {
                    const connections = data.databases.connections
                    const count = Object.keys(connections).length
                    document.querySelector('span#connection-count').innerHTML = `<strong>Connections Count:</strong> ${count}`
                    const default_connection = data.databases.default_connection
                    document.querySelector('span#default-connection').innerHTML = `<strong>Default Connection:</strong> ${default_connection}`

                    Object.keys(connections).forEach(function (name) {
                        const connection = connections[name]

                        const connectionContainer = document.createElement('div')
                        connectionContainer.className = 'connection'
                        connectionContainer.id = name
                        // header
                        const header = document.createElement('h3')
                        header.innerHTML = `Connection Name: ${name}`
                        connectionContainer.append(header)

                        // table
                        const table = document.createElement('table');
                        table.style.width = '300px'
                        table.createTHead()
                        // row
                        let row = table.tHead.insertRow()
                        row.style.textAlign = 'left'
                        table.tHead.append(row)
                        // header name cell
                        let cell = document.createElement('th')
                        cell.style.backgroundColor = 'lightgray'
                        cell.innerText = 'Name'
                        row.append(cell)
                        // header value cell
                        cell = document.createElement('th')
                        cell.innerText = 'Value'
                        row.append(cell)
                        // body
                        table.createTBody()
                        Object.keys(connection).forEach(key => {
                            // row body
                            row = table.tBodies[0].insertRow()
                            // name cell
                            cell = document.createElement('td')
                            cell.style.backgroundColor = 'lightgray'
                            cell.innerText = key
                            row.append(cell)
                            // value cell
                            cell = document.createElement('td')
                            cell.innerText = connection[key]
                            row.append(cell)
                        })
                        // button row
                        row = table.tBodies[0].insertRow()
                        cell = document.createElement('td')
                        cell.colSpan = 2
                        cell.style.textAlign = 'center'
                        // delete button
                        const deleteButton = document.createElement('button')
                        deleteButton.setAttribute('data-id', name)
                        deleteButton.innerText = 'delete'
                        deleteButton.style.width = '100%'
                        deleteButton.addEventListener('click', function (e) {
                            if (connections.hasOwnProperty(e.target.getAttribute('data-id'))) {
                                doAsyncAction('index.php?a=delete', 'POST', {
                                    'Content-Type': 'application/json',  // sent request
                                    'Accept': 'application/json',   // expected data sent back
                                    'X-Requested-With': 'FetchAPIRequest'
                                }, { name: e.target.getAttribute('data-id') })
                                    .then(data => {
                                        if (data.status === 'success') {
                                            loadItems(container)
                                        }
                                    }).catch(error => console.log('Error: ', error))
                            }
                        })
                        cell.append(deleteButton)

                        // edit button
                        const editButton = document.createElement('button')
                        editButton.setAttribute('data-id', name)
                        editButton.innerText = 'edit'
                        editButton.style.width = '100%'
                        editButton.addEventListener('click', function (e, key) {
                            if (connections.hasOwnProperty(e.target.getAttribute('data-id'))) {
                                document.querySelector('input[name="name"]').value = name
                                document.querySelector('input[name="name"]').disabled = true
                                document.querySelector('input[name="driver"]').value = connection.driver
                                document.querySelector('input[name="hostname"]').value = connection.hostname
                                document.querySelector('input[name="username"]').value = connection.username
                                document.querySelector('input[name="password"]').value = connection.password
                                document.querySelector('input[name="database"]').value = connection.database
                                document.querySelector('input[name="charset"]').value = connection.charset
                                document.querySelector('input[name="default_connection"]').checked = name === data.databases.default_connection
                            }
                        })
                        cell.append(editButton)

                        // default button
                        if (default_connection !== name) {
                            const defaultButton = document.createElement('button')
                            defaultButton.setAttribute('data-id', name)
                            defaultButton.innerText = 'set default'
                            defaultButton.style.width = '100%'
                            defaultButton.addEventListener('click', function (e) {
                                if (connections.hasOwnProperty(e.target.getAttribute('data-id'))) {
                                    doAsyncAction('index.php?a=default', 'POST', {
                                        'Content-Type': 'application/json',  // sent request
                                        'Accept': 'application/json',   // expected data sent back
                                        'X-Requested-With': 'FetchAPIRequest'
                                    }, { name: e.target.getAttribute('data-id') })
                                        .then(data => {
                                            if (data.status === 'success') {
                                                loadItems(container)
                                            }
                                        }).catch(error => console.log('Error: ', error))
                                }
                            })
                            cell.append(defaultButton)
                        }
                        row.append(cell)

                        connectionContainer.append(table)

                        container.append(connectionContainer)
                    })
                })
                .catch(error => console.log(error))
        }
    </script>
    <script src="app.js"></script>
</body>
</html>
