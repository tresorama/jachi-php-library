const USER_VALIDATION = (function () {
  /* =================================================== 
        EARLY ABORT
  =================================================== */
  // we have joi validation script ?
  if (!joi) return null;
  if (!ABSTRACT_VALIDATION) return null;

  /* =================================================== 
        VALIDATION SETUP
  =================================================== */
  const Joi = joi;

  const schemas = {
    maybe_exists: Joi.object({
      email: Joi.string()
        .email({ tlds: { allow: false } })
        .required(),
    }),
    login: Joi.object({
      // EMAIL
      email: Joi.string()
        .required()
        .email({ tlds: { allow: false } }),
      //PASSWORD
      password: Joi.string().required().min(6),
    }),
    create_user: Joi.object({
      // EMAIL
      email: Joi.string()
        .required()
        .email({ tlds: { allow: false } }),
      // EMAIL CONFIMATION
      emailConf: Joi.any().required().valid(Joi.ref("email")),
      //PASSWORD
      password: Joi.string().required().min(6),
      // PASSWORD CONFIRMATION
      passwordConf: Joi.any().required().valid(Joi.ref("password")),
      // FIRST NAME
      first_name: Joi.string().required(),
      // LAST NAME
      last_name: Joi.string().required(),
      // BIRTH DATE
      birth: Joi.any().required(),
      // SEX
      sex: Joi.any().required().invalid(""),
    }),
  };

  const ERROR_MESSAGE_MAP = {
    label: {
      email: { it: "Email", eng: "Email" },
      emailConf: { it: "Email di Conferma", eng: "Confirmation Email" },
      password: { it: "Password", eng: "Password" },
      passwordConf: { it: "Password di Conferma", eng: "Confirmation Password" },
      first_name: { it: "Nome", eng: "First Name" },
      last_name: { it: "Cognome", eng: "Last Name" },
      sex: { it: "Sesso", eng: "Gender" },
      birth: { it: "Data di Nascita", eng: "Birth Date" },
    },
    message: {
      required: { it: "{{nome}} è un campo richiesto.", en: "{{nome}} it's required." },
      email: {
        it: "{{value}} non è una mail valida.",
        en: "{{value}} is not a valid email.",
      },
      empty: { it: "{{nome}} non può essere vuoto.", en: "{{nome}} can not be empty." },
      only: {
        it: "{{nome}} e {{ref}} devono essere uguali.",
        en: "{{nome}} and {{ref}} must match.",
      },
      min: {
        it: "{{nome}} deve avere almeno {{min}} caratteri.",
        en: "{{nome}} length must be at least {{min}} characters.",
      },
      max: {
        it: "{{nome}} deve avere massimo {{max}} caratteri.",
        en: "{{nome}} length must be max {{max}} characters.",
      },
    },
  };

  const maybe_exists = ABSTRACT_VALIDATION(schemas["maybe_exists"], ERROR_MESSAGE_MAP);
  const login = ABSTRACT_VALIDATION(schemas["login"], ERROR_MESSAGE_MAP);
  const create_user = ABSTRACT_VALIDATION(schemas["create_user"], ERROR_MESSAGE_MAP);

  /* =================================================== 
      EXPORT
=================================================== */
  return {
    maybe_exists,
    login,
    create_user,
  };
})();
