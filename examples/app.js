/**
 * Clear a form.
 *
 * @param form object to clear.
 */
function cleanForm(form) {
    Array.from(form.elements).forEach(element => {
        if (element.type === 'select-one' || element.type === 'select-multiple') {
            element.selectedIndex = -1
        } else if (element.type === 'checkbox' || element.type === 'radio') {
            element.checked = element.defaultChecked
        } if (element.type === 'hidden' || element.type === 'password' || element.type === 'text' || element.type === 'textarea') {
            element.value = ''
        }
    })
}

function formData(form) {
    let data = {}

    Array.from(form.elements).forEach(element => {
        if (element.type === 'select-one' || element.type === 'select-multiple') {
            data[element.name] = element.options[element.selectedIndex].value
        }
        if (element.type === 'hidden' || element.type === 'password' || element.type === 'text' || element.type === 'textarea') {
            data[element.name] = element.value
        }
        if (element.type === 'checkbox' || element.type === 'radio') {
            data[element.name] = element.checked
        }
    })

    return data
}

/**
 * Determines if a form is dirty by comparing the current value of each element
 * with its default value.
 *
 * @param form The form to be checked.
 * @param {String} validationRule Control to be excluded from the validation.
 *
 * @return {Boolean} <code>true</code> if the form is dirty, <code>false</code>
 *                   otherwise.
 */
function formDirty(form, validationRule = '') {
    let dirty = true

    Array.from(form.elements).forEach(element => {
        const type = element.type
        const rule = !(validationRule === '' || validationRule === 'undefined' || validationRule === 'null')
        let current
        const required = element.required === true
        console.log(required)
        if ((type === 'checkbox' || type === 'radio') && element.required) {
            // checkbox or radio validation
            if (element.required && element.checked !== element.defaultChecked) {
                current = true
            } else {
                current = element.checked !== element.defaultChecked
            }
            if (!current && rule) {
                element.classList.add(validationRule)
            } else if (current && rule) {
                element.classList.remove(validationRule)
            }

            dirty = dirty && current
        }
        if (type === 'hidden' || type === 'password' || type === 'text' || type === 'textarea') {
            // inputs [type: hidden, password, text] and textarea
            if (element.required && element.value !== '') {
                current = true
            } else {
                current = element.value !== '';
            }
            if (!current && rule) {
                element.classList.add(validationRule)
            } else if (current && rule) {
                element.classList.remove(validationRule)
            }

            dirty = dirty && current
        }
        if (type === 'select-one' || type === 'select-multiple') {
            // select [select-one, select-multiple]
            if (element.required && element.selectedIndex > 0) {
                current = true
            } else {
                current = element.selectedIndex > 0;
            }
            if (!current && rule) {
                element.classList.add(validationRule)
            } else if (current && rule) {
                element.classList.remove(validationRule)
            }

            dirty = dirty && current
        }
    })

    return dirty;
}

/**
 * Whether if controls in form object specified will be enable.
 *
 * @param form Object to enable or disable.
 * @param value Value to be assigned.
 */
function formEnable(form, value) {
    Array.from(form.elements).forEach(element => element.disabled = !value)
}

const doAsyncAction = async(url, method = 'GET', headers = {}, data = null) => {
    let response
    if (method === 'GET') {
        return await fetch(url, {
            method: method,
            headers: headers
        })
            .then(response => response.json())
            .catch(error => console.log(error))
            .then(response => {
                return response
            })
    } else {
        response = await fetch(url, {
            method: method,
            headers: headers,
            body: JSON.stringify(data)
        })
    }

    if (response.ok) {
        const data = await response.json(); // Get JSON value from the response body

        return Promise.resolve(data);
    } else {
        return Promise.reject('*** PHP file not found.');
    }
}