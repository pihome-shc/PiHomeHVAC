/**
Copyright 2021 Carlos de Alfonso (https://github.com/dealfonso)

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

(function(exports, document) {

    /**
     * Removes those values in the array that are empty (i.e. its string value is "")
     * @returns the array with the values that are empty removed
     */
        Array.prototype._trim = function() {
        return this.filter(function(e) {
            return `${e}`.trim() !== "";
        });
    }

    /**
     * This function is a proxy to Element.append, but it returns the object, to enable to chain actions
     * @param  {...any} args The objects to append to the element
     * @returns the element
     */
    Element.prototype._append = function(...args) {
        this.append(...args);
        return this;
    }

    /**
     * This function creates a tag object using a notation like the one used for query selectors (e.g. div#myid.myclass.myclass2)
     *   if ommited the tag name, it will be considered as a div (e.g. #myid.myclass1.myclass2 or .myclass1.myclass2)
     * @param {*} tag tag to create in the form '<tag>#<id>.<class1>.<class2>'
     * @param {*} props other properties to set to the element (e.g. attributes) ** if is a text, it will be interpreted as the text param
     * @param {*} text text to set to the element (if prop is set to a string, this param will be ignored)
     * @returns the objet
     */
    function createTag(tag, props = {}, text = null) {
        let parts_id = tag.split('#');

        let id = null;
        if (parts_id.length == 1) {
            tag = parts_id[0];
        } else {
            parts_id[1] = parts_id[1].split('.')
            id = parts_id[1][0];
            tag = [ parts_id[0], ...parts_id[1].slice(1) ].join('.');
        }

        let parts = tag.split('.');
        tag = parts[0];
        if (tag === "") {
            tag = "div";
        }

        if (typeof(props) === "string") {
            text = props;
            props = {};
        }

        if (text !== null) {
            props.textContent = text;
        }

        if (id !== null) {
            props.id = id;
        }

        props.className = [ props.className, ...parts.slice(1)]._trim().join(" ");

        let el = document.createElement(tag);
        for (let prop in props) {
            if (el[prop] !== undefined) {
                el[prop] = props[prop];
            } else {
                el.setAttribute(prop, props[prop]);
            }
        }
        return el;
    }

    function _default_create_dialog() {
        return createTag(".modal.fade", { tabindex : "-1", role : "dialog", "aria-hidden" : "true", "data-keyboard": "true"  })._append(
            createTag(".modal-dialog.modal-dialog-centered.modal-sm", { role : "document" })._append(
                createTag(".modal-content")._append(
                    createTag(".modal-header.bg-red.text-white")._append(
                        createTag(".modal-title")._append(
                            createTag("h5", "Please confirm action")
                        ),  
                        createTag("button.close.btn-close", { type: "button", "data-bs-dismiss": "modal", "aria-label": "Close" }
                    )),
                    createTag(".modal-body")._append(
                        createTag("p.message.text-center", "Please confirm this action"),
                    ),
                    createTag(".modal-footer")._append(
                        createTag("button.btn.btn-primary.confirm", { type: "button" }, "Confirm"),
                        createTag("button.btn.btn-secondary.cancel", { type: "button" }, "Cancel"),
                    )
                )
            )
        )
    }

    function _default_create_dialog_verify() {
        return createTag(".modal.fade", { tabindex : "-1", role : "dialog", "aria-hidden" : "true", "data-keyboard": "true"  })._append(
            createTag(".modal-dialog.modal-dialog-centered", { role : "document" })._append(
                createTag(".modal-content")._append(
                    createTag(".modal-header")._append(
                        createTag(".modal-title")._append(
                            createTag("h5", "Verification failed")
                        ),  
                        createTag("button.close.btn-close", { type: "button", "data-bs-dismiss": "modal", "aria-label": "Close" }
                    )),
                    createTag(".modal-body")._append(
                        createTag("p.message.text-center", "The action cannot be carried out because the verification failed"),
                    ),
                    createTag(".modal-footer")._append(
                        createTag("button.btn.btn-primary.accept", { type: "button", "data-bs-dismiss": "modal" }, "Accept"),
                    )
                )
            )
        )
    }

    defaults = {
        confirm: "Please confirm this action",
        texttarget: "p.message",
        titletarget: ".modal-title h5",
        titletxt: "The action requires confirmation",
        confirmbtn: "button.confirm",
        cancelbtn: "button.cancel",
        dialog: null,
        canceltxt: null,
        confirmtxt: null,
        dialogfnc: _default_create_dialog
    };

    // TODO: use different data-attributes for title target, title txt, text target and dialog to avoid collisions

    let defaults_verification = {
        errormsg: "The action cannot be carried out because the verification failed",
        verify: () => true,
        errormsgtarget: "p.message",
        titletarget: ".modal-title h5",
        errortitletxt: "Verification failed",
        acceptbtn: "button.accept",
        accepttxt: null,
        errordialog: null,
        dialogfnc: _default_create_dialog_verify
    };

    function mergeobjects(o1, o2) {
        let result = {};
        for (let key in o1) {
            result[key] = o1[key];
            if (o2[key] !== undefined) {
                result[key] = o2[key];
            }
        }
        return result;
    }

    function addverification(els, options = {}) {
        if (!Array.isArray(els)) {
            els = [ els ];
        }

        els.forEach(function(el) {
            if (el === null) {
                return;
            }

            // Check wether verification happens before confirming or not
            let verification_first = (el._confirmations === undefined);

            // Prepare the backup for the legacy onclick method, which will be backed up from the backed up during confirmation (to delay the execution until the verification is confirmed)
            el._back_back_onclick = null;
            if (!verification_first) {
                if (el._back_onclick !== undefined) {
                    el._back_back_onclick = el._back_onclick;
                    el._back_onclick = null;
                }
            }

            // We add the event listener to the element, but when the event is going down (to intercept the button)
            el.addEventListener("click", function(e) {
                if (verification_first) {
                    // It is a script-generated event and we are not responding to it, because it is sent by confirmation
                    if (!e.isTrusted) {
                        return;
                    }
                } else {
                }

                // Grab the current settings (they are got here to enable changing global configuration)
                let settings = mergeobjects(defaults_verification, options);

                let result = false;
                try {
                    // We execute the verification function
                    let f = settings.verify;
                    if (typeof f !== "function") {
                        f = new Function(f);
                    }
                    result = f();
                } catch (e) {
                    console.error("Invalid code for verification function: \n" + e);
                }

                if (result !== true) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    // The user can provide a dialogo selector to show, via data-dialog attribute. If it is not found, or it is 
                    //  not provided, we'll create a new one.
                    let dialog = document.querySelector(settings.errordialog);
                    let dialog_created = false;
                    if (dialog === null) {
                        dialog = settings.dialogfnc();
                        dialog_created = true;
                    }

                    // Whether to use bootstrap or not
                    let use_bs = true;
                    if (window.bootstrap === undefined) {
                        use_bs = false;
                    } else {
                        if ((dialog === null) || (bootstrap.Modal === undefined)) {
                            use_bs = false;
                        } 
                    }

                    if (use_bs) {
                        // Find the confirmation and cancellation buttons
                        let acceptbtn = dialog.querySelector(settings.acceptbtn);
                        if (settings.accepttxt !== null) {
                            if (acceptbtn !== null) {
                                acceptbtn.textContent = settings.accepttxt;
                            }
                        }

                        // If there is a text target and a text, put it there
                        if ((settings.errormsg !== null) && (settings.errormsgtarget !== null)) {
                            let errormsgtarget = dialog.querySelector(settings.errormsgtarget);
                            if (errormsgtarget !== null) {
                                errormsgtarget.textContent = settings.errormsg;
                            }
                        }

                        // If there is a title target and a title text, put it there
                        if ((settings.errortitletxt !== null) && (settings.titletarget !== null)) {
                            let titletarget = dialog.querySelector(settings.titletarget);
                            if (titletarget !== null) {
                                titletarget.textContent = settings.errortitletxt;
                            }
                        }

                        // If the dialog was created by this function, we'll add it to the body to be able to use it (we'll dispose it later)
                        if (dialog_created) {
                            document.body.appendChild(dialog);
                        }

                        // Otherwise, let's use boostrap's modal dialogs
                        let modal = new bootstrap.Modal(dialog);

                        // Now we create a promise to make sure that the dialog is deleted from the body when it is hidden
                        new Promise(function(resolve, reject){

                            // Handlers for the events (although easy they are separated because we want to be able to remove the handlers)
                            function dialog_hidden(e) {
                                // Remove the handlers, just in case that the dialog is provided by the user
                                if (acceptbtn !== null) {
                                    acceptbtn.removeEventListener('click', accept_fnc);
                                }
                                dialog.removeEventListener('hidden.bs.modal', dialog_hidden);

                                // If the dialog was created by this function, we'll dispose it
                                if (dialog_created) {
                                    dialog.remove();
                                } 
                                resolve();
                            }
                            function accept_fnc(e) {
                                modal.hide();
                            }

                            // We'll add the event at the end of the user's event handlers
                            dialog.addEventListener('hidden.bs.modal', dialog_hidden);

                            // If there is an accept button, it will close the dialog when it is clicked (separated to be able to be removed from user provided dialog)
                            if (acceptbtn !== null) {
                                acceptbtn.addEventListener('click', accept_fnc);
                            }

                            // Now show the dialog
                            modal.show();  
                        })
                    } else {
                        alert(settings.errormsg);
                    }
                } else {
                    if (typeof(this._back_back_onclick) === 'function') {
                        if (!this._back_back_onclick()) {
                            e.preventDefault();
                        }
                    }
                }
            }, true);
        });
    }

    function addconfirmation(els, options = {}) {
        // Make sure that it is an array of elements
        if (typeof els !== "array") {
            els = [els];
        }

        // Deal with each element
        els.forEach(function(el) {
            if (el === null) {
                return;
            }

            // Prepare the list of different settings for each confirmation
            if (el._confirmations === undefined) {
                el._confirmations = [];
            }

            // Add the new settings (we'll prepend it to the list)
            el._confirmations.unshift(options);

            // If we have backed the onclick element, we already have prepared the element for confirmation, so a new confirmation means simply adding the settings to the _confirmations list
            if (el._back_onclick === undefined) {
                el._back_onclick = null;
                el._confirmation_no = 0;
            } else {
                return;
            }

            // First we'll remove the onclick method, if exists and back it
            if ((el.onclick !== undefined) && (el.onclick !== null)) {
                el._back_onclick = el.onclick;
                el.onclick = null;
            }

            // Now we'll prepend the click event to the element
            el.addEventListener('click', function(e) {

                // If we have confirmed anything, just execute the onclick
                if (this._confirmation_no >= this._confirmations.length) {
                    this._confirmation_no = 0;

                    // TODO: check if onclick works together with the onclick event; I think it does not, and it should be called
                    //    el.click() (if exists), then _back_onclick (if exists) and if both work, not prevent default (then the code
                    //    below should be removed)

                    // If there was a previous onclick event, we'll execute it
                    if (typeof(this._back_onclick) === 'function') {
                        if (!this._back_onclick()) {
                            e.preventDefault();
                        }
                    }
                    return;
                }

                // Grab the current settings
                let settings = mergeobjects(defaults, this._confirmations[this._confirmation_no]);

                // Has confirmations pending
                e.preventDefault();

                // Prevent from executing the other handlers
                e.stopImmediatePropagation();

                // The user can provide a dialogo selector to show, via data-dialog attribute. If it is not found, or it is 
                //  not provided, we'll create a new one.
                let dialog = document.querySelector(settings.dialog);
                let dialog_created = false;
                if (dialog === null) {
                    dialog = settings.dialogfnc();
                    dialog_created = true;
                }

                // Whether to use bootstrap or not
                let use_bs = true;
                if (window.bootstrap === undefined) {
                    use_bs = false;
                } else {
                    if ((dialog === null) || (bootstrap.Modal === undefined)) {
                        use_bs = false;
                    } 
                }

                // Find the confirmation and cancellation buttons
                let confirmbtn = dialog.querySelector(settings.confirmbtn);
                let cancelbtn = dialog.querySelector(settings.cancelbtn);
                if (settings.confirmtxt !== null) {
                    if (confirmbtn !== null) {
                        confirmbtn.textContent = settings.confirmtxt;
                    }
                }
                if (settings.canceltxt !== null) {
                    if (cancelbtn !== null) {
                        cancelbtn.textContent = settings.canceltxt;
                    }
                }

                // If there is a text target and a text, put it there
                if ((settings.confirm !== null) && (settings.texttarget !== null)) {
                    let texttarget = dialog.querySelector(settings.texttarget);
                    if (texttarget !== null) {
                        texttarget.textContent = settings.confirm;
                    }
                }

                // If there is a title target and a title text, put it there
                if ((settings.titletxt !== null) && (settings.titletarget !== null)) {
                    let titletarget = dialog.querySelector(settings.titletarget);
                    if (titletarget !== null) {
                        titletarget.textContent = settings.titletxt;
                    }
                }

                // If the dialog was created by this function, we'll add it to the body to be able to use it (we'll dispose it later)
                if (dialog_created) {
                    document.body.appendChild(dialog);
                }

                // If no bootstrap support, we'll fallback to the legacy "confirm" function
                if (! use_bs) {
                    if (window.confirm(settings.confirm) === true) {
                        // If the user clicked on the confirmation button, we'll execute the onclick event
                        /*
                        el._confirmation_no++;
                        el.dispatchEvent(new Event(e.type, e));
                        */
                        // Continue with the action, by simulating the common click action
                        el._confirmation_no++;

                        if (el._confirmation_no >= el._confirmations.length) {
                            if (el.click !== undefined) {
                                el.click();
                            } else {
                                el.dispatchEvent(new Event(e.type, e));
                            }
                        } else {
                            el.dispatchEvent(new Event(e.type, e));
                        }
                    } else {
                        el._confirmation_no = 0;
                    }
                } else {

                    // Otherwise, let's use boostrap's modal dialogs
                    let modal = new bootstrap.Modal(dialog);

                    // Now we create a promise that will be resolved when the dialog is closed or any of the buttons is clicked
                    new Promise(function(resolve, reject){
                        let confirmed = false;

                        // Handlers for the events (although easy they are separated because we want to be able to remove the handlers)
                        function dialog_hidden(e) {
                            // Remove the handlers, just in case that the dialog is provided by the user
                            if (confirmbtn !== null) {
                                confirmbtn.removeEventListener('click', confirm_fnc);
                            }
                            if (cancelbtn !== null) {
                                cancelbtn.removeEventListener('click', cancel_fnc);
                            }
                                
                            dialog.removeEventListener('hidden.bs.modal', dialog_hidden);

                            // If the dialog was created by this function, we'll dispose it
                            if (dialog_created) {
                                dialog.remove();
                            } 

                            if (confirmed) {
                                resolve();
                            } else {
                                reject();
                            }
                        }
                        function confirm_fnc(e){
                            confirmed = true;
                            modal.hide();
                        }
                        function cancel_fnc(e) {
                            modal.hide();
                        }

                        // We'll resolver or reject the promise after the dialog is closed, so that it offers a better user experience
                        dialog.addEventListener('hidden.bs.modal', dialog_hidden, true);

                        // If the user clicks on either confirm or cancel button, the dialog is closed to proceed with the promise
                        if (confirmbtn !== null) {
                            confirmbtn.addEventListener('click', confirm_fnc, true);
                        }
                        if (cancelbtn !== null) {
                            cancelbtn.addEventListener('click', cancel_fnc, true);
                        }

                        // Now show the dialog
                        modal.show();
                    }).then(function() {
                        // Continue with the action, by simulating the common click action
                        el._confirmation_no++;

                        // If it is the last confirmation, we'll execute the legacy click event (if exists; otherwise we'll dispatch an event to fire jquery events (click method already fires them))
                        if (el._confirmation_no >= el._confirmations.length) {
                            // TODO: check whether onclick (i.e. el.click) and other event handlers work together (I think that el.dispatchEvent(...) should be also called if not returned false)
                            if (el.click !== undefined) {
                                el.click();
                            } else {
                                el.dispatchEvent(new Event(e.type, e));
                            }
                        } else {
                            el.dispatchEvent(new Event(e.type, e));
                        }
                    }).catch(function() {
                        el._confirmation_no = 0;
                        // User clicked cancel (handled to avoid errors in the console)
                    });
                }
            }, true);
        });
    }

    function init(document) {

        function initialize(el, values = {}) {
            let data = el.dataset;
            let options = {};
            for (let key in defaults) {
                if (data[key] !== undefined) {
                    options[key] = data[key];
                }
                if (values[key] !== undefined) {
                    options[key] = values[key];
                }
            }
            addconfirmation(el, mergeobjects(options, values));
        }

        function initialize_verification(el, values = {}) {
            let data = el.dataset;
            let options = {};
            for (let key in defaults_verification) {
                if (data[key] !== undefined) {
                    options[key] = data[key];
                }
                if (values[key] !== undefined) {
                    options[key] = values[key];
                }
            }
            addverification(el, mergeobjects(options, values));
        }

        if (window.jQuery !== undefined) {
            $.fn.confirmButton = function(options = {}) {
                return this.each(function() {
                    initialize(this, options);
                });
            }
            $.fn.verifyButton = function(options = {}) {
                return this.each(function() {
                    initialize_verification(this, options);
                });
            }
        }
        document.querySelectorAll("[data-verify]").forEach(function(el) {
            initialize_verification(el)
        });
        document.querySelectorAll("[data-confirm]").forEach(function(el) {
            initialize(el)
        });
        document.querySelectorAll("[data-verify-after-confirm]").forEach(function(el) {
            let data = el.dataset;
            let options = {};
            if (data['verify-after-confirm'] !== undefined) {
                options["verify"] = data["verify-after-confirm"];
            }
            initialize_verification(el, options)
        });
    }

    function config(settings) {
        defaults = mergeobjects(defaults, settings);
    }

    function config_verification(settings) {
        defaults_verification = mergeobjects(defaults_verification, settings);
    }

    exports.confirmation = {
        addConfirmation: addconfirmation,
        config: config
    };

    exports.verification = {
        addVerification: addconfirmation,
        config: config_verification
    };

    if (document.addEventListener !== undefined) {
        document.addEventListener('DOMContentLoaded', function(e) {
            init(document);
        });
    }
})(window, document);
