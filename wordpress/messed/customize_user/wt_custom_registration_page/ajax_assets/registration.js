function REGISTRATION_SCRIPT() {
  debugger;
  /* =================================================== 
        EARLY ABORT
  =================================================== */
  // we have validation scripts ?
  if (!USER_VALIDATION) return null;
  // we have AJAX calls ?
  if (!USER_AJAX) return null;
  // we have backend error translation ? trranslate messages received from the server
  if (!USER_SERVER_MESSAGE) return null;

  /* =================================================== 
        STEPS
  =================================================== */
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
      validate: USER_VALIDATION[key],
      doAjax: USER_AJAX[key],
      translateServerErrors: USER_SERVER_MESSAGE.translate,
    };
  };

  const ABSTRACT_STEP = function (key, override) {
    return {
      key: key,
      ...override,
    };
  };

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
        const { success, user } = data;

        if (success) {
          //SHOW SHIPPING INFO TAB
          console.log("REGISTERED and LOGGED");
        }
      },
      onError: function (res) {},
    },
    ...{
      init: function () {
        this.formInit();
        this.show();
      },
      show: function () {
        this.formClearFields();
        this.formShow();
      },
      hide: function () {
        this.formHide();
      },
    },
  });

  create_user.init();
}
