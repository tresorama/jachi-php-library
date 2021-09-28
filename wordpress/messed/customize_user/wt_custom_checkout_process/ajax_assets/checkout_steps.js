function CHECKOUT_STEPS_SCRIPT() {
  /* =================================================== 
        EARLY ABORT
  =================================================== */
  debugger;

  // we have USER - front end validation scripts  ?  validation before call the server
  if (!USER_VALIDATION) return null;
  // we have USER - AJAX calls  ?
  if (!USER_AJAX) return null;
  // we have USER - backend error translation ? trranslate messages received from the server
  if (!USER_SERVER_MESSAGE) return null;

  // we have CHECKOUT - front end validation scripts  ?  validation before call the server
  if (!CHECKOUT_VALIDATION) return null;
  // we have CHECKOUT - AJAX calls for CHECKOUT ?
  if (!checkout_step_params) return null;

  // we have AJAX CLIENT RUNNER ?
  if (!WT_AJAX_CLIENT_CONTROLLER) return null;

  /* =================================================== 
        JQUERY
  =================================================== */

  const $ = jQuery;

  /* =================================================== 
        USER 
  =================================================== */

  let USER = null; // this is the real time user object that contain user data

  const ABSTRACT_USER = function (user_data) {
    return user_data ? { ...user_data } : null;
  };
  const get_user_data_from_dom = function () {
    const dom = document.querySelector("#user-data-container");
    const maybeJSON = dom.getAttribute("data-chekout-step");
    const parsed = maybeJSON ? JSON.parse(maybeJSON) : {};
    const { user = false } = parsed;
    return user;
  };
  const get_user_data_from_ajax_response = function (response) {
    const { user } = response;
    return user;
  };
  const update_user_object = function (user) {
    USER = ABSTRACT_USER(user);
  };
  const BillingSameAsShipping = function () {
    const {
      billing_first_name,
      billing_last_name,
      billing_address_1,
      billing_city,
      billing_country,
      billing_postcode,
      billing_state,
    } = USER.billing;
    const {
      shipping_first_name,
      shipping_last_name,
      shipping_address_1,
      shipping_city,
      shipping_country,
      shipping_postcode,
      shipping_state,
    } = USER.shipping;
    switch (false) {
      case billing_first_name === shipping_first_name:
      case billing_last_name === shipping_last_name:
      case billing_country === shipping_country:
      case billing_state === shipping_state:
      case billing_city === shipping_city:
      case billing_postcode === shipping_postcode:
      case billing_address_1 === shipping_address_1:
        return false;
      default:
        return true;
    }
  };
  const user_has_saved_billing_address = function () {
    for (const key in USER.billing) {
      if (USER.billing.hasOwnProperty(key)) {
        switch (key) {
          case "billing_company":
            continue;
        }
        const value = USER.billing[key];
        if (!value) {
          return false;
        }
      }
    }
    return true;
  };

  /* =================================================== 
        REFRESH FRAGMENTS
  =================================================== */

  const replace_fragments = function (fragments) {
    for (const selector in fragments) {
      if (fragments.hasOwnProperty(selector)) {
        const html = fragments[selector];
        const maybeEls = [...document.querySelectorAll(selector)];
        maybeEls.forEach((el) => (el.outerHTML = html));
      }
    }

    const replacedTotals = (function () {
      for (const selector in fragments) {
        if (fragments.hasOwnProperty(selector)) {
          if (selector.indexOf("order-total") > -1) {
            return true;
          }
        }
      }
      return false;
    })();

    if (replacedTotals) {
      $(document.body).trigger("updated_cart_totals");
      //$(document.body).trigger("updated_checkout");
    }
  };

  /* =================================================== 
        CHECKOUT AJAX
  =================================================== */

  const ABSTRACT_AJAX_CALL = function (key, method) {
    const endpoint = endpoints[key];
    const { _prefix } = endpoints;
    const url = _prefix.replace("%%endpoint%%", endpoint);

    return function ({ dataToStringify, onSuccess, onError }) {
      const { ajaxAdditionalParams = {} } = this;
      const req = {
        method: method,
        dataToStringify: { ...dataToStringify, [key]: true, ...ajaxAdditionalParams },
        endpoint: url,
        onSuccess: onSuccess,
        onError: onError,
      };
      WT_AJAX_CLIENT_CONTROLLER(req);
    };
  };
  const endpoints = {
    _prefix: checkout_step_params.wc_ajax_url.toString(),
    shipping_info: checkout_step_params.endpoints.shipping_info_update,
    billing_info: checkout_step_params.endpoints.billing_info_update,
  };
  const CHECKOUT_AJAX = {
    shipping_info: ABSTRACT_AJAX_CALL("shipping_info", "post"),
    billing_info: ABSTRACT_AJAX_CALL("billing_info", "post"),
  };

  /* =================================================== 
        STEPS CREATOR
  =================================================== */

  const getMyValidation = function (key) {
    if (USER_VALIDATION[key]) {
      return USER_VALIDATION[key];
    }
    if (CHECKOUT_VALIDATION[key]) {
      return CHECKOUT_VALIDATION[key];
    }
    return null;
  };
  const getMyAjax = function (key) {
    if (USER_AJAX[key]) {
      return USER_AJAX[key];
    }
    if (CHECKOUT_AJAX[key]) {
      return CHECKOUT_AJAX[key];
    }
    return null;
  };

  const ABSTRACT_FORM_CONTROLLER = function (key) {
    return {
      formHide: function () {
        const { form } = this.form_dom;
        if (form) {
          form.style.display = "none";
        }
        this.formHideErrors();
      },
      formShow: function () {
        const { form } = this.form_dom;
        if (form) {
          form.style.display = "block";
        }
      },
      formShowErrors: function (messages) {
        if (!messages || messages.length === 0) return null;
        const { boxErrors } = this.form_dom;
        if (!boxErrors) return null;

        // inject list of errors
        const list = document.createElement("ul");
        messages.forEach((m) => {
          const li = document.createElement("li");
          li.innerHTML = m.message;
          list.appendChild(li);
        });
        boxErrors.innerHTML = null;
        boxErrors.appendChild(list);
        boxErrors.style.display = "";

        // scroll to
        const b = boxErrors.getBoundingClientRect();
        window.scrollTo(b.x, b.y - 40);
      },
      formHideErrors: function () {
        const { boxErrors } = this.form_dom;
        if (!boxErrors) return null;
        boxErrors.innerHTML = null;
        boxErrors.style.display = "none";
      },
      validate: getMyValidation(key),
      doAjax: getMyAjax(key),
      ajaxAdditionalParams: { checkout_step: true },
      translateServerErrors: USER_SERVER_MESSAGE.translate,
    };
  };
  const ABSTRACT_STEP_MAYBE_EDIT = function () {
    return {
      maybe_edit_dom: null,
      maybe_edit_selectors: {
        box: ".maybe-edit",
        trigger: ".trigger",
        summary: ".summary",
      },
      maybeEditInit: function () {
        /* binding */
        if (this.onMaybeEditTrigger) {
          this.onMaybeEditTrigger = this.onMaybeEditTrigger.bind(this);
        }

        // get DOM elements */
        const { form } = this.form_dom;
        const { maybe_edit_selectors } = this;
        const box = form.parentElement.querySelector(maybe_edit_selectors.box);
        const summary = box.querySelector(maybe_edit_selectors.summary);
        const trigger = box.querySelector(maybe_edit_selectors.trigger);
        this.maybe_edit_dom = { box, summary, trigger };

        /* add listeners */
        if (trigger) {
          if (this.onMaybeEditTrigger) {
            trigger.addEventListener("click", this.onMaybeEditTrigger);
          }
        }
      },
      maybeEditShow: function (summaryText) {
        const { box } = this.maybe_edit_dom || {};
        if (!box) return;
        box.style.display = "block";
        if (summaryText) {
          this.maybeEditUpdateText(summaryText);
        }
      },
      maybeEditHide: function () {
        const { box } = this.maybe_edit_dom || {};
        if (!box) return;
        box.style.display = "none";
      },
      maybeEditUpdateText: function (summaryText) {
        const { summary } = this.maybe_edit_dom || {};
        if (!summary) return;
        summary.innerHTML = summaryText;
      },
    };
  };
  const ABSTRACT_STEP_TITLE = function () {
    return {
      step_title_dom: null,
      step_title_selector: ".step-title",
      stepTitleInit: function () {
        const { form } = this.form_dom;
        const { step_title_selector: sel } = this;
        const parent = form.parentElement;
        const title = parent.querySelector(sel);
        this.step_title_dom = title;
      },
      stepTitleShow: function () {
        const { step_title_dom } = this;
        if (!step_title_dom) return;
        step_title_dom.style.display = "block";
      },
      stepTitleHide: function () {
        const { step_title_dom } = this;
        if (!step_title_dom) return;
        step_title_dom.style.display = "none";
      },
    };
  };

  const ABSTRACT_STEP = function (key, override) {
    return {
      key: key,
      ...override,
    };
  };

  /* =================================================== 
      Select - Options Helper
  =================================================== */

  const SELECT_HELPER = function (select) {
    return {
      select: select,
      isSelect: String(select.tagName).toLowerCase() === "select",
      isInput: String(select.tagName).toLowerCase() === "input",
      isSelect2: select.className.indexOf("select2") > -1,
      getElAndIndexOfOptionByValue: function (options, value) {
        const el = [...options].filter((o) => o.value === value)[0];
        return {
          el: el ? el : null,
          i: el ? options.indexOf(el) : null,
        };
      },
      getValue: function () {
        const { select, isInput } = this;
        if (isInput) {
          return select.value ? select.value : null;
        }
        const selected = select.options[select.selectedIndex];
        const value = selected ? (selected.value ? selected.value : null) : null;
        return value;
      },
      setValue: function (value) {
        const actualValue = this.getValue();
        if (actualValue === value) return;

        const { select, isInput } = this;

        if (isInput) {
          select.value = value;
          return;
        }

        const options = [...select.options];
        const { el: toDisable, i: toDisableIndex } = this.getElAndIndexOfOptionByValue(
          options,
          actualValue
        );
        if (toDisable) {
          select.selectedIndex = null;
          toDisable.removeAttribute("selected");
        }
        const { el: toActive, i: toActiveIndex } = this.getElAndIndexOfOptionByValue(
          options,
          value
        );
        if (toActive) {
          select.selectedIndex = toActiveIndex;
          toActive.setAttribute("selected", "selected");
        }

        if (this.isSelect2) {
          const select2 = select.parentElement.querySelector(".select2");
          if (!select2) return;
          const span = select2.querySelector(
            ".selection .select2-selection .select2-selection__rendered"
          );
          if (!span) return;

          // in case you want to reset the selct..or the value passed has no option..
          const textToAdd = !toActive ? options[0].innerHTML : toActive.innerHTML;
          span.setAttribute("title", textToAdd);
          span.innerHTML = textToAdd;
        }
      },
      makeEmpty: function () {
        this.setValue(null);
      },
    };
  };

  /* =================================================== 
        REAL STEPS
  =================================================== */

  const maybe_exists = new ABSTRACT_STEP("maybe_exists", {
    ...ABSTRACT_FORM_CONTROLLER("maybe_exists"),
    ...{
      form_dom: {
        form: null,
        boxErrors: null,
        inputEmail: null,
        submit: null,
      },
      formInit: function () {
        /* binding */
        this.formOnSubmit = this.formOnSubmit.bind(this);
        this.onSuccess = this.onSuccess.bind(this);
        this.onError = this.onError.bind(this);

        /* get DOM elements */
        const form = document.querySelector("#cs-maybe-exists");
        const inputEmail = form.querySelector("#cs-maybe-exists-email");
        const submit = form.querySelector("#cs-maybe-exists-submit");
        const boxErrors = form.parentElement.querySelector("#cs-maybe-exists-errors");
        this.form_dom = { form, inputEmail, submit, boxErrors };

        /* add listeners */
        submit.addEventListener("click", this.formOnSubmit);
      },
      formOnSubmit: function (e) {
        const email = String(this.form_dom.inputEmail.value).trim();

        // validate
        const { error, messages } = this.validate({
          email: email,
        });
        if (error) {
          this.formShowErrors(messages);
          return;
        }

        // call ajax
        this.doAjax({
          dataToStringify: { email: email },
          onSuccess: this.onSuccess,
          onError: this.onError,
        });
      },
      formClearFields: function () {
        this.form_dom.inputEmail.value = null;
      },
      onSuccess: function (res) {
        const { data } = res;
        const { exists, email } = data;

        this.hide();

        if (exists) {
          login.show(email);
        } else {
          create_user.show(email);
        }
      },
      onError: function (res) {},
    },
    ...ABSTRACT_STEP_MAYBE_EDIT(),
    ...{
      init: function () {
        this.formInit();
        this.maybeEditInit();
      },
      show: function () {
        this.formShow();
        this.formClearFields();
        this.maybeEditHide();
      },
      hide: function () {
        this.formHide();
      },
      onMaybeEditTrigger(e) {
        COORDINATOR.hideAllFormsAfterMe(this.key);
        this.show();
      },
    },
  });

  const login = new ABSTRACT_STEP("login", {
    ...ABSTRACT_FORM_CONTROLLER("login"),
    ...{
      form_dom: {
        form: null,
        boxErrors: null,
        spanEmail: null,
        inputPassword: null,
        submit: null,
      },
      formInit: function () {
        /* binding */
        this.formOnSubmit = this.formOnSubmit.bind(this);
        this.onSuccess = this.onSuccess.bind(this);
        this.onError = this.onError.bind(this);

        /* get DOM element */
        this.form_dom.boxErrors = document.querySelector("#cs-login-errors");

        this.form_dom.form = document.querySelector("#cs-login");
        this.form_dom.spanEmail = document.querySelector("#cs-login-email-span");
        this.form_dom.inputEmailHidden = document.querySelector("#cs-login-email");
        this.form_dom.inputPassword = document.querySelector("#cs-login-password");
        this.form_dom.submit = document.querySelector("#cs-login-submit");

        // LISTENER
        const { submit } = this.form_dom;
        submit.addEventListener("click", this.formOnSubmit);
      },
      formUpdateDOM: function (email) {
        const { spanEmail, inputEmailHidden } = this.form_dom;

        // save for later received data
        this.received.email = email;

        // INJECT RECEIVED DATA
        spanEmail.innerHTML = email;
        inputEmailHidden.value = email;
      },
      formOnSubmit: function () {
        const password = String(this.form_dom.inputPassword.value).trim();
        const { email } = this.received;

        const data = {
          email: email,
          password: password,
        };

        const { error, messages } = this.validate(data);
        if (error) {
          this.formShowErrors(messages);
          return;
        }

        this.doAjax({
          dataToStringify: { ...data },
          onSuccess: this.onSuccess,
          onError: this.onError,
        });
      },
      formClearFields: function () {
        this.form_dom.spanEmail.innerHTML = null;
        this.form_dom.inputEmailHidden.value = null;
        this.form_dom.inputPassword.value = null;
      },
      onSuccess: function (res) {
        const { data } = res;
        const { success, fragments } = data;

        if (fragments) {
          replace_fragments(fragments);
        }

        if (success) {
          // UPDATE USER OBJECT
          update_user_object(get_user_data_from_ajax_response(data));
          // HIDE ME
          this.hide();
          //SHOW SHIPPING INFO TAB
          shipping_info.show();
        } else {
          const messages = this.translateServerErrors(data.messages);
          this.formShowErrors(messages);
        }
      },
      onError: function (res) {},
    },
    ...ABSTRACT_STEP_MAYBE_EDIT(),
    ...ABSTRACT_STEP_TITLE(),
    ...{
      received: {
        email: null,
      },
      init: function () {
        this.formInit();
        this.maybeEditInit();
        this.stepTitleInit();
      },
      show: function (email) {
        // save for later received data..
        this.received.email = email;
        this.formClearFields();
        this.formUpdateDOM(email);
        this.formShow();
        this.stepTitleHide();
      },
      hide: function () {
        const { email } = this.received;
        this.formHide();
        this.maybeEditShow("Stai comporabdo come " + email);
        this.stepTitleShow();
      },
    },
  });

  const create_user = new ABSTRACT_STEP("create_user", {
    ...ABSTRACT_FORM_CONTROLLER("create_user"),
    ...{
      form_dom: {
        form: null,
        boxErrors: null,
        inputSex: null,
        inputFirstName: null,
        inputLastName: null,
        inputEmail: null,
        inputEmailConf: null,
        inputPassword: null,
        inputPasswordConf: null,
        inputBirthDate: null,
        submit: null,
      },
      formInit: function () {
        /* binding */
        this.formOnSubmit = this.formOnSubmit.bind(this);
        this.onSuccess = this.onSuccess.bind(this);
        this.onError = this.onError.bind(this);

        /* get DOM elements */
        this.form_dom.boxErrors = document.querySelector("#nu-errors");

        this.form_dom.form = document.querySelector("#nu");
        this.form_dom.inputSexRadios = document.querySelectorAll('[name*="nu-sex"]');
        this.form_dom.inputSex = document.querySelector("#nu-sex-receiver");
        this.form_dom.inputFirstName = document.querySelector("#nu-first-name");
        this.form_dom.inputLastName = document.querySelector("#nu-last-name");
        this.form_dom.inputEmail = document.querySelector("#nu-email");
        this.form_dom.inputEmailConf = document.querySelector("#nu-email-conf");
        this.form_dom.inputPassword = document.querySelector("#nu-password");
        this.form_dom.inputPasswordConf = document.querySelector("#nu-password-conf");
        this.form_dom.inputBirthDate = document.querySelector("#nu-birth");
        this.form_dom.submit = document.querySelector("#nu-submit");

        /* add listeners */
        const { submit, inputSexRadios, inputSex } = this.form_dom;
        submit.addEventListener("click", this.formOnSubmit);

        [...inputSexRadios].forEach((radio) => {
          radio.addEventListener("change", function () {
            inputSex.value = radio.value;
          });
        });
      },
      formUpdateDOM: function (email) {
        const { inputEmail } = this.form_dom;
        inputEmail.value = email;
      },
      formOnSubmit: function () {
        const {
          inputSex,
          inputFirstName,
          inputLastName,
          inputEmail,
          inputEmailConf,
          inputPassword,
          inputPasswordConf,
          inputBirthDate,
        } = this.form_dom;
        const { value: sex } = inputSex;
        const { value: first_name } = inputFirstName;
        const { value: last_name } = inputLastName;
        const { value: email } = inputEmail;
        const { value: emailConf } = inputEmailConf;
        const { value: password } = inputPassword;
        const { value: passwordConf } = inputPasswordConf;
        const { value: birth } = inputBirthDate;

        const data = {
          email: email || undefined,
          emailConf: emailConf || undefined,
          password: password || undefined,
          passwordConf: passwordConf || undefined,
          first_name: first_name || undefined,
          last_name: last_name || undefined,
          birth: birth || undefined,
          sex: sex || undefined,
        };

        const { error, messages } = this.validate(data);
        if (error) {
          this.formShowErrors(messages);
          return;
        }

        this.doAjax({
          dataToStringify: { ...data },
          onSuccess: this.onSuccess,
          onError: this.onError,
        });
      },
      formClearFields: function () {
        this.form_dom.inputSex.value = null;
        this.form_dom.inputFirstName.value = null;
        this.form_dom.inputLastName.value = null;
        this.form_dom.inputEmail.value = null;
        this.form_dom.inputEmailConf.value = null;
        this.form_dom.inputPassword.value = null;
        this.form_dom.inputPasswordConf.value = null;
        this.form_dom.inputBirthDate.value = null;

        const { inputSexRadios } = this.form_dom;
        [...inputSexRadios].forEach((radio) => {
          radio.checked = false;
        });
      },
      onSuccess: function (res) {
        const { data } = res;
        const { success } = data;

        if (success) {
          // UPDATE USER OBJECT
          update_user_object(get_user_data_from_ajax_response(data));
          //HIDE ME
          this.hide();
          //SHOW SHIPPING INFO TAB
          shipping_info.show();
        }
      },
      onError: function (res) {},
    },
    ...ABSTRACT_STEP_MAYBE_EDIT(),
    ...ABSTRACT_STEP_TITLE(),
    ...{
      received: {
        email: null,
      },
      init: function () {
        this.formInit();
        this.maybeEditInit();
        this.stepTitleInit();
      },
      show: function (email) {
        this.formClearFields();
        this.formUpdateDOM(email);
        this.formShow();
        this.stepTitleHide();
        // save for later received data..
        this.received.email = email;
      },
      hide: function () {
        const { email } = this.received;
        this.formHide();
        this.maybeEditShow("Stai comporabdo come " + email);
        this.stepTitleShow();
      },
    },
  });

  const shipping_info = new ABSTRACT_STEP("shipping_info", {
    ...ABSTRACT_FORM_CONTROLLER("shipping_info"),
    ...{
      form_dom: {
        form: null,
        boxErrors: null,
        inputName: null,
        inputSurname: null,
        inputAddress: null,
        inputCO: null,
        inputCap: null,
        inputCity: null,
        inputProvincia: null,
        inputTel: null,
        submit: null,
      },
      formInit: function () {
        /* binding */
        this.formOnSubmit = this.formOnSubmit.bind(this);
        this.onSuccess = this.onSuccess.bind(this);
        this.onError = this.onError.bind(this);

        /* get DOM elements */
        this.form_dom.form = document.querySelector("#cs-ship");
        this.form_dom.boxErrors = document.querySelector("#cs-ship-errors");
        this.form_dom.inputFirstName = document.querySelector("#cs-ship-first-name");
        this.form_dom.inputLastName = document.querySelector("#cs-ship-last-name");
        this.form_dom.selectCountry = document.querySelector("#calc_shipping_country");
        this.form_dom.selectState = document.querySelector("#calc_shipping_state");
        this.form_dom.inputCity = document.querySelector("#calc_shipping_city");
        this.form_dom.inputPostcode = document.querySelector("#calc_shipping_postcode");
        this.form_dom.inputAddress = document.querySelector("#cs-ship-address");
        this.form_dom.inputCO = document.querySelector("#cs-ship-co");
        this.form_dom.inputTel = document.querySelector("#cs-ship-phone");
        this.form_dom.submit = document.querySelector("#cs-ship-submit");

        // LISTENERS
        const { submit } = this.form_dom;
        submit.addEventListener("click", this.formOnSubmit);
      },
      formUpdateDOM: function (user) {
        const {
          inputFirstName,
          inputLastName,
          selectCountry,
          selectState,
          inputCity,
          inputPostcode,
          inputAddress,
          inputCO,
          inputTel,
        } = this.form_dom;

        const first_name = user.shipping.shipping_first_name
          ? user.shipping.shipping_first_name
          : user.main.first_name;
        const last_name = user.shipping.shipping_last_name
          ? user.shipping.shipping_last_name
          : user.main.last_name;

        // INJECT RECEIVED DATA
        inputFirstName.value = first_name || null;
        inputLastName.value = last_name || null;
        new SELECT_HELPER(selectCountry).setValue(user.shipping.shipping_country || null);
        new SELECT_HELPER(selectState).setValue(user.shipping.shipping_state || null);
        inputCity.value = user.shipping.shipping_city || null;
        inputPostcode.value = user.shipping.shipping_postcode || null;
        inputAddress.value = user.shipping.shipping_address_1 || null;
        inputCO.value = user.shipping.shipping_co || null;
        inputTel.value = user.billing.billing_phone || null;
      },
      formClearFields: function () {
        const {
          inputFirstName,
          inputLastName,
          selectCountry,
          selectState,
          inputCity,
          inputPostcode,
          inputAddress,
          inputCO,
          inputTel,
        } = this.form_dom;

        inputFirstName.value = null;
        inputLastName.value = null;

        new SELECT_HELPER(selectCountry).makeEmpty();
        new SELECT_HELPER(selectState).makeEmpty();

        inputCity.value = null;
        inputPostcode.value = null;
        inputAddress.value = null;
        inputCO.value = null;
        inputTel.value = null;
      },
      formOnSubmit: function () {
        const {
          inputFirstName,
          inputLastName,
          selectCountry,
          selectState,
          inputCity,
          inputPostcode,
          inputAddress,
          inputCO,
          inputTel,
        } = this.form_dom;

        const { value: first_name } = inputFirstName;
        const { value: last_name } = inputLastName;
        const country = new SELECT_HELPER(selectCountry).getValue();
        const state = new SELECT_HELPER(selectState).getValue();
        const { value: city } = inputCity;
        const { value: postcode } = inputPostcode;
        const { value: address } = inputAddress;
        const { value: co } = inputCO;
        const { value: tel } = inputTel;

        const data = {
          first_name: first_name || undefined,
          last_name: last_name || undefined,
          country: country || undefined,
          state: state || undefined,
          city: city || undefined,
          postcode: postcode || undefined,
          address: address || undefined,
          co: co || undefined,
          tel: tel || undefined,
        };

        const { error, messages } = this.validate(data);
        if (error) {
          this.formShowErrors(messages);
          return;
        }

        this.doAjax({
          dataToStringify: { ...data },
          onSuccess: this.onSuccess,
          onError: this.onError,
        });
      },
      onSuccess: function (res) {
        const { data } = res;
        const { success, fragments = false } = data;

        if (fragments) {
          replace_fragments(fragments);
        }

        if (success) {
          // UPDATE USER OBJECT
          update_user_object(get_user_data_from_ajax_response(data));
          //HIDE ME
          this.formClearFields();
          this.formUpdateDOM(USER);
          this.hide();
          //SHOW PAYMENT TAB
          billing_info.show();
          //payment.show();
        }
      },
      onError: function (res) {},
    },
    ...ABSTRACT_STEP_MAYBE_EDIT(),
    ...ABSTRACT_STEP_TITLE(),
    ...{
      init: function () {
        this.formInit();
        this.maybeEditInit();
      },
      show: function () {
        this.formClearFields();
        this.formUpdateDOM(USER);
        this.formShow();
      },
      hide: function () {
        this.formHide();
      },
    },
  });

  const billing_info = new ABSTRACT_STEP("billing_info", {
    ...ABSTRACT_FORM_CONTROLLER("billing_info"),
    ...{
      form_dom: {
        form: null,
        radioBillingSameAsShipping: null,
        billing: {
          block: null,
          inputBillingFirstName: null,
          inputBillingLastName: null,
          inputBillingCompany: null,
          inputBillingCountry: null,
          inputBillingAddress1: null,
          inputBillingAddress2: null,
          inputBillingCity: null,
          inputBillingState: null,
          inputBillingPostcode: null,
          inputBillingPhone: null,
          inputBillingEmail: null,
        },
        submit: null,
      },
      formInit: function () {
        /*bindindg */
        this.formOnSubmit = this.formOnSubmit.bind(this);
        this.onSuccess = this.onSuccess.bind(this);
        this.onError = this.onError.bind(this);
        this.radioHandler = this.radioHandler.bind(this);

        /* get DOM elements */
        const f = document.querySelector("#cs-billing");
        this.form_dom.form = f;
        this.form_dom.radioBillingSameAsShipping = {
          block: f.querySelector(".maybe-change-billing-address"),
          radios: [
            f.querySelector('[name="billing-same-as-shipping"][id="same-data"]'),
            f.querySelector('[name="billing-same-as-shipping"][id="change-data"]'),
          ],
        };
        this.form_dom.billing = {
          block: f.querySelector(".billing-address-form"),
          inputBillingFirstName: f.querySelector('[name="billing_first_name"]'),
          inputBillingLastName: f.querySelector('[name="billing_last_name"]'),
          inputBillingCompany: f.querySelector('[name="billing_company"]'),
          inputBillingCountry: f.querySelector('[name="billing_country"]'),
          inputBillingAddress1: f.querySelector('[name="billing_address_1"]'),
          inputBillingAddress2: f.querySelector('[name="billing_address_2"]'),
          inputBillingCity: f.querySelector('[name="billing_city"]'),
          inputBillingState: f.querySelector('[name="billing_state"]'),
          inputBillingPostcode: f.querySelector('[name="billing_postcode"]'),
          inputBillingPhone: f.querySelector('[name="billing_phone"]'),
          inputBillingEmail: f.querySelector('[name="billing_email"]'),
        };
        this.form_dom.submit = f.querySelector("#cs-billing-submit");

        const { radios = [] } = this.form_dom.radioBillingSameAsShipping;
        radios.forEach((radio) => {
          radio.addEventListener("click", this.radioHandler);
        });

        const { submit } = this.form_dom;
        submit.addEventListener("click", this.formOnSubmit);
      },
      radioHandler: function (e) {
        const radio = e.currentTarget;
        if (radio.checked) {
          const id = radio.id;
          if (id === "same-data") {
            this.hideEditBillingForm();
          } else {
            this.showEditBillingForm();
          }
        }
      },
      showRadios: function () {
        const { radioBillingSameAsShipping } = this.form_dom;
        const { block } = radioBillingSameAsShipping;
        if (block) {
          block.style.display = "block";
        }
      },
      hideRadios: function () {
        const { radioBillingSameAsShipping } = this.form_dom;
        const { block } = radioBillingSameAsShipping;
        if (block) {
          block.style.display = "none";
        }
      },
      showEditBillingForm: function () {
        const { billing } = this.form_dom;
        const { block } = billing;
        if (block) {
          block.style.display = "";
        }
      },
      hideEditBillingForm: function () {
        const { billing } = this.form_dom;
        const { block } = billing;
        if (block) {
          block.style.display = "none";
        }
        return;
      },
      formUpdateDOM: function (user) {
        const { radioBillingSameAsShipping, billing } = this.form_dom;
        const {
          inputBillingFirstName,
          inputBillingLastName,
          inputBillingCompany,
          inputBillingCountry,
          inputBillingAddress1,
          inputBillingAddress2,
          inputBillingCity,
          inputBillingState,
          inputBillingPostcode,
          inputBillingPhone,
          inputBillingEmail,
        } = billing;

        // INJECT RECEIVED DATA
        const has_billing_address = user_has_saved_billing_address();
        const billingIsLikeShipping = BillingSameAsShipping();
        if (has_billing_address) {
          this.hideRadios();
          this.showEditBillingForm();
        } else {
          this.showRadios();
          if (billingIsLikeShipping) {
            radioBillingSameAsShipping.radios[0].click();
          } else {
            radioBillingSameAsShipping.radios[1].click();
          }
        }

        inputBillingFirstName.value = user.billing.billing_first_name || null;
        inputBillingLastName.value = user.billing.billing_last_name || null;
        inputBillingCompany.value = user.billing.billing_company || null;
        inputBillingCountry.value = user.billing.billing_country || null;
        inputBillingAddress1.value = user.billing.billing_address_1 || null;
        inputBillingAddress2.value = user.billing.billing_address_2 || null;
        inputBillingCity.value = user.billing.billing_city || null;
        inputBillingState.value = user.billing.billing_state || null;
        inputBillingPostcode.value = user.billing.billing_postcode || null;
        inputBillingPhone.value = user.billing.billing_phone || null;
        inputBillingEmail.value = user.billing.billing_email || null;
      },
      formOnSubmit: function () {
        const { billing } = this.form_dom;
        const {
          inputBillingFirstName,
          inputBillingLastName,
          inputBillingCompany,
          inputBillingCountry,
          inputBillingAddress1,
          inputBillingAddress2,
          inputBillingCity,
          inputBillingState,
          inputBillingPostcode,
          inputBillingPhone,
          inputBillingEmail,
        } = billing;

        const { value: billing_first_name } = inputBillingFirstName;
        const { value: billing_last_name } = inputBillingLastName;
        const { value: billing_company } = inputBillingCompany;
        const billing_country = new SELECT_HELPER(inputBillingCountry).getValue();
        const billing_state = new SELECT_HELPER(inputBillingState).getValue();
        const { value: billing_city } = inputBillingCity;
        const { value: billing_postcode } = inputBillingPostcode;
        const { value: billing_address_1 } = inputBillingAddress1;
        const { value: billing_phone } = inputBillingPhone;
        const { value: billing_email } = inputBillingEmail;

        const data = {
          billing_first_name: billing_first_name || undefined,
          billing_last_name: billing_last_name || undefined,
          billing_company: billing_company || undefined,
          billing_address_1: billing_address_1 || undefined,
          billing_city: billing_city || undefined,
          billing_state: billing_state || undefined,
          billing_postcode: billing_postcode || undefined,
          billing_country: billing_country || undefined,
          billing_email: billing_email || undefined,
          billing_phone: billing_phone || undefined,
        };

        const { error, messages } = this.validate(data);
        if (error) {
          this.formShowErrors(messages);
          return;
        }

        this.doAjax({
          dataToStringify: { ...data },
          onSuccess: this.onSuccess,
          onError: this.onError,
        });
      },
      onSuccess: function (res) {
        const { data } = res;
        const { success, fragments = false } = data;

        if (fragments) {
          replace_fragments(fragments);
        }

        if (success) {
          // UPDATE USER OBJECT
          update_user_object(get_user_data_from_ajax_response(data));
          //HIDE ME
          //this.formClearFields();
          this.formUpdateDOM(USER);
          this.hide();
          //SHOW PAYMENT TAB
          payment.show();
        }
      },
      onError: function (res) {},
    },
    ...{
      init: function () {
        this.formInit();
      },
      show: function () {
        this.formUpdateDOM(USER);
        this.formShow();
      },
      hide: function () {
        this.formHide();
      },
    },
  });
  const payment = new ABSTRACT_STEP("payment", {
    ...ABSTRACT_FORM_CONTROLLER("payment"),
    ...{
      form_dom: {
        form: null,
        fakeForm: {
          inputBillingFirstName: null,
          inputBillingLastName: null,
          inputBillingCompany: null,
          inputBillingCountry: null,
          inputBillingAddress1: null,
          inputBillingAddress2: null,
          inputBillingCity: null,
          inputBillingState: null,
          inputBillingPostcode: null,
          inputBillingPhone: null,
          inputBillingEmail: null,
          inputShippingFirstName: null,
          inputShippingLastName: null,
          inputShippingCompany: null,
          inputShippingCountry: null,
          inputShippingAddress1: null,
          inputShippingAddress2: null,
          inputShippingCity: null,
          inputShippingState: null,
          inputShippingPostcode: null,
        },
      },
      formInit: function () {
        /* get DOM elements */
        this.form_dom.form = document.querySelector("#cs-payment");

        const f = document.querySelector(".fake-woocommerce-checkout-form"); // FAKE FORM
        let fakeForm = null;
        if (f) {
          fakeForm = {
            billing: {
              block: f.querySelector(".woocommerce-address-fields"),
              inputBillingFirstName: f.querySelector('[name="billing_first_name"]'),
              inputBillingLastName: f.querySelector('[name="billing_last_name"]'),
              inputBillingCompany: f.querySelector('[name="billing_company"]'),
              inputBillingCountry: f.querySelector('[name="billing_country"]'),
              inputBillingAddress1: f.querySelector('[name="billing_address_1"]'),
              inputBillingAddress2: f.querySelector('[name="billing_address_2"]'),
              inputBillingCity: f.querySelector('[name="billing_city"]'),
              inputBillingState: f.querySelector('[name="billing_state"]'),
              inputBillingPostcode: f.querySelector('[name="billing_postcode"]'),
              inputBillingPhone: f.querySelector('[name="billing_phone"]'),
              inputBillingEmail: f.querySelector('[name="billing_email"]'),
            },
            shipping: {
              inputShippingFirstName: f.querySelector(
                'input[name="shipping_first_name"]'
              ),
              inputShippingLastName: f.querySelector('input[name="shipping_last_name"]'),
              inputShippingCompany: f.querySelector('input[name="shipping_company"]'),
              inputShippingCountry: f.querySelector('input[name="shipping_country"]'),
              inputShippingAddress1: f.querySelector('input[name="shipping_address_1"]'),
              inputShippingAddress2: f.querySelector('input[name="shipping_address_2"]'),
              inputShippingCity: f.querySelector('input[name="shipping_city"]'),
              inputShippingState: f.querySelector('input[name="shipping_state"]'),
              inputShippingPostcode: f.querySelector('input[name="shipping_postcode"]'),
            },
          };
        }
        this.form_dom.fakeForm = fakeForm;
      },
      formUpdateDOM: function (user) {
        const { fakeForm = null } = this.form_dom;
        if (!fakeForm) {
          return;
        }
        const { billing, shipping } = this.form_dom.fakeForm;
        const {
          inputBillingFirstName,
          inputBillingLastName,
          inputBillingCompany,
          inputBillingCountry,
          inputBillingAddress1,
          inputBillingAddress2,
          inputBillingCity,
          inputBillingState,
          inputBillingPostcode,
          inputBillingPhone,
          inputBillingEmail,
        } = billing;
        const {
          inputShippingFirstName,
          inputShippingLastName,
          inputShippingCompany,
          inputShippingCountry,
          inputShippingAddress1,
          inputShippingAddress2,
          inputShippingCity,
          inputShippingState,
          inputShippingPostcode,
        } = shipping;

        // INJECT RECEIVED DATA
        inputBillingFirstName.value = user.billing.billing_first_name || null;
        inputBillingLastName.value = user.billing.billing_last_name || null;
        inputBillingCompany.value = user.billing.billing_company || null;
        inputBillingCountry.value = user.billing.billing_country || null;
        inputBillingAddress1.value = user.billing.billing_address_1 || null;
        inputBillingAddress2.value = user.billing.billing_address_2 || null;
        inputBillingCity.value = user.billing.billing_city || null;
        inputBillingState.value = user.billing.billing_state || null;
        inputBillingPostcode.value = user.billing.billing_postcode || null;
        inputBillingPhone.value = user.billing.billing_phone || null;
        inputBillingEmail.value = user.billing.billing_email || null;

        inputShippingFirstName.value = user.shipping.shipping_first_name || null;
        inputShippingLastName.value = user.shipping.shipping_last_name || null;
        inputShippingCompany.value = user.shipping.shipping_company || null;
        inputShippingCountry.value = user.shipping.shipping_country || null;
        inputShippingAddress1.value = user.shipping.shipping_address_1 || null;
        inputShippingAddress2.value = user.shipping.shipping_address_2 || null;
        inputShippingCity.value = user.shipping.shipping_city || null;
        inputShippingState.value = user.shipping.shipping_state || null;
        inputShippingPostcode.value = user.shipping.shipping_postcode || null;
      },
    },
    ...{
      init: function () {
        this.formInit();
      },
      show: function () {
        this.formUpdateDOM(USER);
        this.formShow();
      },
      hide: function () {
        this.formHide();
      },
    },
  });

  /* =================================================== 
        RUN
  =================================================== */

  //init all steps
  maybe_exists.init();
  login.init();
  create_user.init();
  shipping_info.init();
  billing_info.init();
  payment.init();

  // initialize USER with data from DOM , if exists
  update_user_object(get_user_data_from_dom());

  // run coordinator of the page
  const COORDINATOR = {
    steps: [maybe_exists, login, create_user, shipping_info, billing_info, payment],
    steps_order: [
      "maybe_exists",
      "login",
      "create_user",
      "shipping_info",
      "billing_info",
      "payment",
    ],
    hideAllForms: function () {
      const { steps } = this;
      steps.forEach((step) => step.hide());
    },
    hideAllFormsAfterMe: function (key) {
      const { steps, steps_order } = this;
      const pos = steps_order.indexOf(key);
      if (pos !== -1) {
        const nextSteps = steps.filter((step, i) => i > pos);
        nextSteps.forEach((step) => step.hide());
      }
    },
    first_run: function () {
      this.hideAllForms();
      if (USER) {
        maybe_exists.maybeEditShow("Stai comporabdo come " + USER.main.user_email);
        shipping_info.show();
      } else {
        maybe_exists.show();
      }
    },
  };

  COORDINATOR.first_run();
}
